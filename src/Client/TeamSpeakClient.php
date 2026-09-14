<?php

declare(strict_types=1);

namespace TS3ServerBot\Client;

use TS3ServerBot\Config\ClientConfig;
use TS3ServerBot\Config\ServerFamily;
use TS3ServerBot\Config\Transport;
use TS3ServerBot\Connection\ConnectionState;
use TS3ServerBot\Domain\Instance;
use TS3ServerBot\Domain\QueryNode;
use TS3ServerBot\Domain\VirtualServer;
use TS3ServerBot\Exception\InvalidParameterException;
use TS3ServerBot\Exception\InvalidStateException;
use TS3ServerBot\Exception\UnsupportedOperationException;
use TS3ServerBot\Protocol\CommandCatalog;
use TS3ServerBot\Protocol\QueryCommand;
use TS3ServerBot\Protocol\QueryResponse;
use TS3ServerBot\Transport\CurlHttpClient;
use TS3ServerBot\Transport\HttpClientInterface;
use TS3ServerBot\Transport\PhpSecLibSshSession;
use TS3ServerBot\Transport\SshQueryTransport;
use TS3ServerBot\Transport\SshSessionInterface;
use TS3ServerBot\Transport\TransportInterface;
use TS3ServerBot\Transport\WebQueryTransport;

/**
 * Developer-facing entry point for TS3ServerBot Query.
 *
 * Connects to TeamSpeak 3.13.8 or TeamSpeak 6.0.0-beta12.1 using SSH ServerQuery or WebQuery HTTP.
 * Raw/telnet Query is not available. Command catalogs match those two server versions.
 * Unknown 6.0.0-beta12.1 command names are sent to the server instead of being invented here.
 *
 * @package TS3ServerBot
 */
final class TeamSpeakClient
{
    private ConnectionState $state = ConnectionState::Disconnected;

    private ?int $selectedServerId;

    private ?int $selectedServerPort;

    private readonly CommandCatalog $catalog;

    /**
     * @param ClientConfig $config Transport and credentials. Secrets are never logged.
     * @param TransportInterface $transport SSH or WebQuery implementation.
     * @param CommandCatalog|null $catalog Command list for the configured server family.
     */
    public function __construct(
        private readonly ClientConfig $config,
        private readonly TransportInterface $transport,
        ?CommandCatalog $catalog = null,
    ) {
        $this->catalog = $catalog ?? CommandCatalog::forFamily($config->serverFamily);
        $this->selectedServerId = $config->virtualServerId;
        $this->selectedServerPort = $config->virtualServerPort;
    }

    /**
     * Creates a WebQuery client. Requires an API key. Uses HTTP header `x-api-key`, never a URL query string.
     *
     * @throws \TS3ServerBot\Exception\InvalidParameterException
     */
    public static function webQuery(ClientConfig $config, ?HttpClientInterface $http = null): self
    {
        if ($config->transport !== Transport::WebQuery) {
            throw new InvalidParameterException('webQuery() requires Transport::WebQuery.');
        }

        return new self($config, new WebQueryTransport($config, $http ?? new CurlHttpClient()));
    }

    /**
     * Creates an SSH ServerQuery client. Requires username and password for the SSH handshake.
     * That handshake is Query authentication; callers do not need a second `login()` unless they
     * want to switch Query identity.
     *
     * @throws \TS3ServerBot\Exception\InvalidParameterException
     */
    public static function ssh(ClientConfig $config, ?SshSessionInterface $session = null): self
    {
        if ($config->transport !== Transport::Ssh) {
            throw new InvalidParameterException('ssh() requires Transport::Ssh.');
        }

        return new self($config, new SshQueryTransport($config, $session ?? new PhpSecLibSshSession()));
    }

    /**
     * Opens the configured transport.
     *
     * WebQuery marks the client ready (API key is sent on each request). SSH authenticates the
     * encrypted session with the configured Query username and password, waits for the ServerQuery
     * banner, and is then already logged in. A second Query `login` command is not required.
     *
     * When SSH config includes `virtualServerId` or `virtualServerPort`, `use` is sent automatically.
     *
     * @throws \TS3ServerBot\Exception\ConnectionException
     * @throws \TS3ServerBot\Exception\AuthenticationException
     * @throws \TS3ServerBot\Exception\TimeoutException
     */
    public function connect(): void
    {
        $this->transport->connect();
        $this->state = ConnectionState::Authenticated;

        if ($this->config->transport === Transport::Ssh
            && ($this->config->virtualServerId !== null || $this->config->virtualServerPort !== null)
        ) {
            $this->useServer(
                $this->config->virtualServerId,
                $this->config->virtualServerPort,
                false,
                $this->config->nickname
            );
        }
    }

    /**
     * Closes the transport. SSH sends `quit` when possible.
     */
    public function disconnect(): void
    {
        $this->transport->disconnect();
        $this->state = ConnectionState::Disconnected;
    }

    /**
     * Whether the client is connected (SSH session open or WebQuery marked ready).
     */
    public function isConnected(): bool
    {
        return $this->state !== ConnectionState::Disconnected;
    }

    /**
     * Current connection lifecycle state.
     */
    public function getState(): ConnectionState
    {
        return $this->state;
    }

    /**
     * Returns the active transport: SSH or WebQuery.
     */
    public function getTransport(): Transport
    {
        return $this->config->transport;
    }

    /**
     * Authenticates with ServerQuery `login`. SSH only. WebQuery uses the API key instead.
     *
     * SSH already authenticates during `connect()` using the same username and password. Call this
     * only to switch Query identity. TeamSpeak deselects the virtual server after `login`; call
     * `useServer()` again afterward.
     *
     * Permission: `b_serverquery_login`. TeamSpeak 3.13.8.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function login(?string $username = null, ?string $password = null): QueryResponse
    {
        $response = $this->execute('login', [
            'client_login_name' => $username ?? (string) $this->config->username,
            'client_login_password' => $password ?? (string) $this->config->password,
        ]);
        $this->state = ConnectionState::Authenticated;

        return $response;
    }

    /**
     * Deselects the virtual server and logs out. SSH only.
     *
     * Command: `logout`. Permission: `b_serverquery_login`. TeamSpeak 3.13.8.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function logout(): QueryResponse
    {
        $response = $this->execute('logout');
        $this->selectedServerId = null;
        $this->selectedServerPort = null;
        if ($this->state !== ConnectionState::Disconnected) {
            $this->state = ConnectionState::Connected;
        }

        return $response;
    }

    /**
     * Selects a virtual server. SSH uses `use`. WebQuery stores the id/port for later HTTP paths.
     *
     * Command: `use` (SSH). Permission: `b_virtualserver_select`. TeamSpeak 3.13.8.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function useServer(?int $id = null, ?int $port = null, bool $virtual = false, ?string $nickname = null): VirtualServer
    {
        $id ??= $this->config->virtualServerId;
        $port ??= $this->config->virtualServerPort;
        $nickname ??= $this->config->nickname;

        if ($this->config->transport === Transport::Ssh) {
            $parameters = [];
            $options = [];
            if ($id !== null) {
                $parameters['sid'] = $id;
            }
            if ($port !== null) {
                $parameters['port'] = $port;
            }
            if ($nickname !== null) {
                $parameters['client_nickname'] = $nickname;
            }
            if ($virtual) {
                $options[] = 'virtual';
            }
            $this->execute('use', $parameters, $options);
        }

        $this->selectedServerId = $id;
        $this->selectedServerPort = $port;

        return new VirtualServer($this, $id, $port);
    }

    /**
     * Returns a virtual-server helper using the current selection or the given id.
     */
    public function server(?int $id = null, ?int $port = null): VirtualServer
    {
        return new VirtualServer(
            $this,
            $id ?? $this->selectedServerId ?? $this->config->virtualServerId,
            $port ?? $this->selectedServerPort ?? $this->config->virtualServerPort,
        );
    }

    /**
     * Instance-level helper (`version`, `serverlist`, `hostinfo`, ...).
     */
    public function instance(): Instance
    {
        return new Instance($this);
    }

    /**
     * Displays information about the current Query connection.
     *
     * Command: `whoami`. TeamSpeak 3.13.8.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function whoami(): QueryNode
    {
        return $this->execute('whoami')->requireFirst();
    }

    /**
     * Displays version, platform, and build number.
     *
     * Command: `version`. TeamSpeak 3.13.8.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function version(): QueryNode
    {
        return $this->instance()->getVersion();
    }

    /**
     * Creates a WebQuery API key. Typically run over SSH because a key is required to use WebQuery.
     *
     * Command: `apikeyadd`. TeamSpeak 3.13.8. Scope must be manage, write, or read.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function createApiKey(string $scope, int $lifetime = 14, ?int $clientDatabaseId = null): QueryNode
    {
        $parameters = ['scope' => $scope, 'lifetime' => $lifetime];
        if ($clientDatabaseId !== null) {
            $parameters['cldbid'] = $clientDatabaseId;
        }

        $response = $this->execute('apikeyadd', $parameters);
        foreach (['id', 'apikey'] as $key) {
            $row = $response->firstHaving($key);
            if ($row !== null) {
                return $row;
            }
        }

        return $response->requireFirst();
    }

    /**
     * Notification lines collected from the SSH session. Field lists are not documented in 3.13.8.
     *
     * @return list<string>
     */
    public function drainNotifications(): array
    {
        if ($this->transport instanceof SshQueryTransport) {
            return $this->transport->drainNotifications();
        }

        return [];
    }

    /**
     * Executes a Query command.
     *
     * On TeamSpeak 3 this is not a raw Query string API: the name must exist in the 3.13.8 catalog,
     * and WebQuery-incompatible commands are rejected. On TeamSpeak 6 the 6.0.0-beta12.1 catalog is used
     * the same way; names unknown to that catalog are still sent so a newer beta can answer or return an error.
     *
     * @param array<string, scalar|list<scalar>> $parameters
     * @param list<string> $options
     *
     * @throws \TS3ServerBot\Exception\InvalidStateException
     * @throws \TS3ServerBot\Exception\InvalidParameterException
     * @throws \TS3ServerBot\Exception\UnsupportedOperationException
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function execute(string $command, array $parameters = [], array $options = []): QueryResponse
    {
        if ($this->state === ConnectionState::Disconnected) {
            throw new InvalidStateException('Connect before executing TeamSpeak operations.');
        }

        $definition = $this->catalog->find($command);

        if ($definition === null) {
            if ($this->config->serverFamily === ServerFamily::TeamSpeak3) {
                throw new UnsupportedOperationException(
                    'Command "' . $command . '" is not in the TeamSpeak 3.13.8 catalog.'
                );
            }
        } elseif ($this->config->transport === Transport::WebQuery && !$definition->webQuery) {
            throw new UnsupportedOperationException(
                'Command "' . $command . '" is not available on WebQuery. Use the SSH transport.'
            );
        }

        $this->assertOptions($command, $options);

        $query = new QueryCommand(
            $command,
            $parameters,
            $options,
            $this->selectedServerId ?? $this->config->virtualServerId,
            $this->selectedServerPort ?? $this->config->virtualServerPort,
        );

        $response = $this->transport->execute($query);
        $response->throwIfError();

        return $response;
    }

    /**
     * @param list<string> $options
     *
     * @throws InvalidParameterException
     * @throws UnsupportedOperationException
     */
    public function assertOptions(string $command, array $options): void
    {
        $definition = $this->catalog->find($command);
        if ($definition === null) {
            if ($this->config->serverFamily === ServerFamily::TeamSpeak3) {
                $this->catalog->get($command);
            }

            return;
        }

        $label = $this->config->serverFamily === ServerFamily::TeamSpeak6
            ? 'TeamSpeak 6.0.0-beta12.1'
            : 'TeamSpeak 3.13.8';

        foreach ($options as $option) {
            $option = ltrim($option, '-');
            if (!in_array($option, $definition->options, true)) {
                throw new InvalidParameterException(
                    'Option "-' . $option . '" is not documented for ' . $command . ' in ' . $label . '.'
                );
            }
        }
    }

    /**
     * Returns the command catalog for the configured server family.
     */
    public function catalog(): CommandCatalog
    {
        return $this->catalog;
    }
}

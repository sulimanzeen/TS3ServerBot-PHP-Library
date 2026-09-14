<?php

declare(strict_types=1);

namespace TS3ServerBot\Domain;

use TS3ServerBot\Client\TeamSpeakClient;
use TS3ServerBot\Protocol\QueryResponse;

/**
 * Instance-level operations documented without a selected virtual server.
 *
 * @package TS3ServerBot
 */
final class Instance
{
    /**
     * @param TeamSpeakClient $client Connected framework client.
     */
    public function __construct(private readonly TeamSpeakClient $client)
    {
    }

    /**
     * Displays the servers version information including platform and build number.
     *
     * Command: `version`. Supported on SSH and WebQuery. TeamSpeak 3.13.8.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function getVersion(): QueryNode
    {
        return $this->client->execute('version')->requireFirst();
    }

    /**
     * Displays server instance connection info.
     *
     * Command: `hostinfo`. Permission: `b_serverinstance_info_view`. TeamSpeak 3.13.8.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function getHostInfo(): QueryNode
    {
        return $this->client->execute('hostinfo')->requireFirst();
    }

    /**
     * Displays the server instance configuration.
     *
     * Command: `instanceinfo`. Permission: `b_serverinstance_info_view`. TeamSpeak 3.13.8.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function getInfo(): QueryNode
    {
        return $this->client->execute('instanceinfo')->requireFirst();
    }

    /**
     * Lists virtual servers on the instance.
     *
     * Command: `serverlist`. Permission: `b_serverinstance_virtualserver_list`. TeamSpeak 3.13.8.
     *
     * @param list<string> $options Documented flags: uid, short, all, onlyoffline
     * @return list<QueryNode>
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function getServers(array $options = []): array
    {
        $this->client->assertOptions('serverlist', $options);

        return $this->client->execute('serverlist', [], $options)->items;
    }

    /**
     * Finds a virtual server database id by voice port.
     *
     * Command: `serveridgetbyport`. Permission: `b_serverinstance_virtualserver_list`. TeamSpeak 3.13.8.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function getServerIdByPort(int $port): QueryNode
    {
        return $this->client->execute('serveridgetbyport', ['virtualserver_port' => $port])->requireFirst();
    }

    /**
     * Sends a global text message to all virtual servers.
     *
     * Command: `gm`. TeamSpeak 3.13.8.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function sendGlobalMessage(string $message): QueryResponse
    {
        return $this->client->execute('gm', ['msg' => $message]);
    }

    /**
     * Lists IP addresses used by the server instance.
     *
     * Command: `bindinglist`. TeamSpeak 3.13.8.
     *
     * @return list<QueryNode>
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function getBindings(): array
    {
        return $this->client->execute('bindinglist')->items;
    }

    /**
     * Signs a message with the license private key. TeamSpeak 6. Does not work with no license or the default license.
     *
     * Command: `licensesignmessage`. Permission: `b_serverinstance_licensesign_message`.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function signLicenseMessage(string $message): QueryNode
    {
        return $this->client->execute('licensesignmessage', ['message' => $message])->requireFirst();
    }
}

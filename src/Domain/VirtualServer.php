<?php

declare(strict_types=1);

namespace TS3ServerBot\Domain;

use TS3ServerBot\Client\TeamSpeakClient;
use TS3ServerBot\Exception\InvalidParameterException;
use TS3ServerBot\Protocol\QueryResponse;

/**
 * Virtual-server operations mapped to documented ServerQuery commands.
 *
 * @package TS3ServerBot
 */
final class VirtualServer
{
    /**
     * @param TeamSpeakClient $client Connected framework client.
     * @param int|null $id Virtual server id used for WebQuery paths.
     * @param int|null $port Virtual server voice port used for `/byport/` when set.
     */
    public function __construct(
        private readonly TeamSpeakClient $client,
        private readonly ?int $id,
        private readonly ?int $port = null,
    ) {
    }

    /**
     * Virtual server id used for WebQuery paths and SSH `use`, if known.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Virtual server voice port used for `/byport/` or `use port=`, if known.
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * Displays detailed configuration of the selected virtual server.
     *
     * Command: `serverinfo`. Permission: `b_virtualserver_info_view`. TeamSpeak 3.13.8.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function getInfo(): QueryNode
    {
        return $this->client->execute('serverinfo')->requireFirst();
    }

    /**
     * Lists clients online on the virtual server.
     *
     * Command: `clientlist`. Permissions: `b_virtualserver_client_list` plus subscribe powers.
     * Documented options: uid, away, voice, times, groups, info, country, ip, icon, badges.
     * `location` is not exposed; it appears only in the changelog, not in clientlist.txt.
     *
     * @param list<string> $options
     * @return list<QueryNode>
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function getClients(array $options = []): array
    {
        $this->client->assertOptions('clientlist', $options);

        return $this->client->execute('clientlist', [], $options)->items;
    }

    /**
     * Lists channels on the virtual server.
     *
     * Command: `channellist`. Permission: `b_virtualserver_channel_list`.
     * Documented options: topic, flags, voice, limits, icon, secondsempty, banners.
     *
     * @param list<string> $options
     * @return list<QueryNode>
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function getChannels(array $options = []): array
    {
        $this->client->assertOptions('channellist', $options);

        return $this->client->execute('channellist', [], $options)->items;
    }

    /**
     * Displays details for one or more online clients.
     *
     * Command: `clientinfo`. Permission: `b_client_info_view`. TeamSpeak 3.13.8.
     *
     * @param list<int> $clientIds
     * @return list<QueryNode>
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function getClientInfo(array $clientIds): array
    {
        return $this->client->execute('clientinfo', ['clid' => $clientIds])->items;
    }

    /**
     * Displays channel properties.
     *
     * Command: `channelinfo`. TeamSpeak 3.13.8.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function getChannelInfo(int $channelId): QueryNode
    {
        return $this->client->execute('channelinfo', ['cid' => $channelId])->requireFirst();
    }

    /**
     * Kicks one or more clients from a channel (`reasonid` 4) or the server (`reasonid` 5).
     *
     * Command: `clientkick`. TeamSpeak 3.13.8. `reasonmsg` may be at most 40 characters.
     *
     * @param list<int> $clientIds
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function kick(array $clientIds, int $reasonId, ?string $message = null, bool $continueOnError = false): QueryResponse
    {
        $parameters = [
            'reasonid' => $reasonId,
            'clid' => $clientIds,
        ];
        if ($message !== null) {
            $parameters['reasonmsg'] = $message;
        }

        return $this->client->execute('clientkick', $parameters, $continueOnError ? ['continueonerror'] : []);
    }

    /**
     * Moves one or more clients into a channel.
     *
     * Command: `clientmove`. TeamSpeak 3.13.8.
     *
     * @param list<int> $clientIds
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function move(array $clientIds, int $channelId, ?string $channelPassword = null, bool $continueOnError = false): QueryResponse
    {
        $parameters = [
            'clid' => $clientIds,
            'cid' => $channelId,
        ];
        if ($channelPassword !== null) {
            $parameters['cpw'] = $channelPassword;
        }

        return $this->client->execute('clientmove', $parameters, $continueOnError ? ['continueonerror'] : []);
    }

    /**
     * Sends a poke message to a client.
     *
     * Command: `clientpoke`. TeamSpeak 3.13.8.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function poke(int $clientId, string $message): QueryResponse
    {
        return $this->client->execute('clientpoke', ['clid' => $clientId, 'msg' => $message]);
    }

    /**
     * Sends a text message. `targetmode` 1 = client, 2 = channel, 3 = server.
     *
     * Command: `sendtextmessage`. TeamSpeak 3.13.8.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function sendTextMessage(int $targetMode, string $message, int $target = 0): QueryResponse
    {
        return $this->client->execute('sendtextmessage', [
            'targetmode' => $targetMode,
            'target' => $target,
            'msg' => $message,
        ]);
    }

    /**
     * Changes virtual server properties.
     *
     * Command: `serveredit`. TeamSpeak 3.13.8.
     *
     * @param array<string, scalar> $properties
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function edit(array $properties): QueryResponse
    {
        return $this->client->execute('serveredit', $properties);
    }

    /**
     * Lists server groups.
     *
     * Command: `servergrouplist`. TeamSpeak 3.13.8.
     *
     * @return list<QueryNode>
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function getServerGroups(): array
    {
        return $this->client->execute('servergrouplist')->items;
    }

    /**
     * Adds a client to one or more server groups.
     *
     * Command: `clientaddservergroup`. TeamSpeak 3.13.8.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function addClientToServerGroup(int $clientDatabaseId, int ...$serverGroupIds): QueryResponse
    {
        return $this->client->execute('clientaddservergroup', [
            'cldbid' => $clientDatabaseId,
            'sgid' => $serverGroupIds,
        ]);
    }

    /**
     * Removes a client from a server group.
     *
     * Command: `clientdelservergroup`. TeamSpeak 3.13.8.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function removeClientFromServerGroup(int $clientDatabaseId, int $serverGroupId): QueryResponse
    {
        return $this->client->execute('clientdelservergroup', [
            'cldbid' => $clientDatabaseId,
            'sgid' => $serverGroupId,
        ]);
    }

    /**
     * Lists ban rules on the virtual server.
     *
     * Command: `banlist`. TeamSpeak 3.13.8.
     *
     * @return list<QueryNode>
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function getBans(): array
    {
        return $this->client->execute('banlist')->items;
    }

    /**
     * Bans one or more online clients.
     *
     * Command: `banclient`. TeamSpeak 3.13.8.
     *
     * @param list<int> $clientIds
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function banClient(array $clientIds, ?int $time = null, ?string $reason = null, bool $continueOnError = false): QueryResponse
    {
        $parameters = ['clid' => $clientIds];
        if ($time !== null) {
            $parameters['time'] = $time;
        }
        if ($reason !== null) {
            $parameters['banreason'] = $reason;
        }

        return $this->client->execute('banclient', $parameters, $continueOnError ? ['continueonerror'] : []);
    }

    /**
     * Registers for ServerQuery event notifications. SSH only. Notify payloads are not documented.
     *
     * Command: `servernotifyregister`. Permission: `b_virtualserver_notify_register`.
     * TeamSpeak 6 also documents event `bans` (permission `b_client_ban_list`).
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function registerNotifications(string $event, ?int $channelId = null): QueryResponse
    {
        $parameters = ['event' => $event];
        if ($channelId !== null) {
            $parameters['id'] = $channelId;
        }

        return $this->client->execute('servernotifyregister', $parameters);
    }

    /**
     * Unregisters ServerQuery event notifications. SSH only.
     *
     * Command: `servernotifyunregister`. On TeamSpeak 6, `event` and `id` can unregister a single source.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function unregisterNotifications(?string $event = null, ?int $channelId = null): QueryResponse
    {
        $parameters = [];
        if ($event !== null) {
            $parameters['event'] = $event;
        }
        if ($channelId !== null) {
            $parameters['id'] = $channelId;
        }

        return $this->client->execute('servernotifyunregister', $parameters);
    }

    /**
     * Finds matching ban rules. TeamSpeak 6. At least one of ip, name, uid, or mytsid is required.
     *
     * Command: `banfind`. Permission: `b_client_ban_list`.
     *
     * @return list<QueryNode>
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function findBans(?string $ip = null, ?string $name = null, ?string $uid = null, ?string $mytsid = null): array
    {
        $parameters = [];
        if ($ip !== null) {
            $parameters['ip'] = $ip;
        }
        if ($name !== null) {
            $parameters['name'] = $name;
        }
        if ($uid !== null) {
            $parameters['uid'] = $uid;
        }
        if ($mytsid !== null) {
            $parameters['mytsid'] = $mytsid;
        }
        if ($parameters === []) {
            throw new InvalidParameterException('banfind requires at least one of ip, name, uid, or mytsid.');
        }

        return $this->client->execute('banfind', $parameters)->items;
    }

    /**
     * Creates a JWT signed with the server identity. TeamSpeak 6. Duration is in seconds (max 1 hour).
     *
     * Command: `authenticationtoken`.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function createAuthenticationToken(int $durationSeconds): QueryNode
    {
        return $this->client->execute('authenticationtoken', ['duration' => $durationSeconds])->requireFirst();
    }

    /**
     * Returns a JWT for Matrix/Synapse chat login when the server is configured for Matrix. TeamSpeak 6.
     *
     * Command: `chatlogintoken`.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function getChatLoginToken(): QueryNode
    {
        return $this->client->execute('chatlogintoken')->requireFirst();
    }

    /**
     * Sets this server as the Matrix homebase for the caller, or for cldbid if given. TeamSpeak 6.
     *
     * Command: `homebaseset`. Permissions: `b_virtualserver_homebase_set`; `b_virtualserver_homebase_manage` with cldbid.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function setHomebase(?int $clientDatabaseId = null): QueryResponse
    {
        $parameters = [];
        if ($clientDatabaseId !== null) {
            $parameters['cldbid'] = $clientDatabaseId;
        }

        return $this->client->execute('homebaseset', $parameters);
    }

    /**
     * Unsets this server as the Matrix homebase for the caller, or for cldbid if given. TeamSpeak 6.
     *
     * Command: `homebasedel`. Permission: `b_virtualserver_homebase_manage` when using cldbid.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function deleteHomebase(?int $clientDatabaseId = null): QueryResponse
    {
        $parameters = [];
        if ($clientDatabaseId !== null) {
            $parameters['cldbid'] = $clientDatabaseId;
        }

        return $this->client->execute('homebasedel', $parameters);
    }

    /**
     * Checks whether this server is the Matrix homebase for the caller, or for cldbid if given. TeamSpeak 6.
     *
     * Command: `homebaseisset`. Permission: `b_virtualserver_homebase_manage` when using cldbid.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function isHomebaseSet(?int $clientDatabaseId = null): QueryResponse
    {
        $parameters = [];
        if ($clientDatabaseId !== null) {
            $parameters['cldbid'] = $clientDatabaseId;
        }

        return $this->client->execute('homebaseisset', $parameters);
    }

    /**
     * Lists users who set this server as their Matrix homebase. TeamSpeak 6.
     *
     * Command: `homebaselist`. Permission: `b_virtualserver_homebase_list`.
     * Optional return_code is sent back on a notify message; notify payload fields are not documented.
     *
     * @return list<QueryNode>
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function listHomebases(?string $returnCode = null): array
    {
        $parameters = [];
        if ($returnCode !== null) {
            $parameters['return_code'] = $returnCode;
        }

        return $this->client->execute('homebaselist', $parameters)->items;
    }

    /**
     * Returns a JWT for a TeamSpeak HTTP file-transfer server. TeamSpeak 6. SSH only (`ft*`).
     *
     * Command: `ftgetchannelfilehttptoken`. Pass a channel id or a special channel name
     * (`avatars`, `icons`, `chat`, `listuserfiles`). The HTTP transfer after the token is not implemented.
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function getChannelFileHttpToken(?int $channelId = null, ?string $specialChannel = null): QueryNode
    {
        $parameters = [];
        if ($channelId !== null) {
            $parameters['cid'] = $channelId;
        }
        if ($specialChannel !== null) {
            $parameters['scid'] = $specialChannel;
        }
        if ($parameters === []) {
            throw new InvalidParameterException('ftgetchannelfilehttptoken requires cid or scid.');
        }

        return $this->client->execute('ftgetchannelfilehttptoken', $parameters)->requireFirst();
    }

    /**
     * Lists client identities known by the server.
     *
     * Command: `clientdblist`. On TeamSpeak 6, `-homebaseonly` is documented.
     *
     * @param list<string> $options
     * @return list<QueryNode>
     *
     * @throws \TS3ServerBot\Exception\TeamSpeakException
     */
    public function getDatabaseClients(array $options = [], ?int $start = null, ?int $duration = null): array
    {
        $this->client->assertOptions('clientdblist', $options);
        $parameters = [];
        if ($start !== null) {
            $parameters['start'] = $start;
        }
        if ($duration !== null) {
            $parameters['duration'] = $duration;
        }

        return $this->client->execute('clientdblist', $parameters, $options)->items;
    }
}

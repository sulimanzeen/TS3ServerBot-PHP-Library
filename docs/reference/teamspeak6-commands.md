# TeamSpeak 6-only commands

These nine Query commands exist in TeamSpeak **6.0.0-beta12.1** and not in TeamSpeak **3.13.8**. Text below follows the Query help for 6.0.0-beta12.1. The library does not invent extra HTTP APIs; it only sends these Query commands over SSH or WebQuery.

Set `ServerFamily::TeamSpeak6` when you connect. See [Compatibility](../guides/compatibility.md).

Transports: **SSH** (10022) and **WebQuery HTTP** (10080), unless a command is marked SSH only. Raw Query (10011) is not supported.

## Identity and tokens

### `authenticationtoken`

Returns a JWT signed with the server identity so other services (or Query commands such as `permget` with `forauthenticationtoken`) can prove this server identified the caller.

| | |
|---|---|
| Usage | `authenticationtoken duration={duration}` |
| Parameters | `duration` — integer seconds the token is valid (max 1 hour) |
| Permissions | (none listed in the help file) |
| Transport | SSH, WebQuery |
| PHP | `$server->createAuthenticationToken(int $durationSeconds)` |

Documented example:

```text
authenticationtoken duration=3600
token=...
error id=0 msg=ok
```

### `chatlogintoken`

When the TeamSpeak server is configured to use Matrix servers for chat, this command returns a JSON Web Token that can be used to log in to the Matrix server. Currently only the Synapse Matrix server is supported.

| | |
|---|---|
| Usage | `chatlogintoken` |
| Parameters | (none) |
| Permissions | (none listed in the help file) |
| Transport | SSH, WebQuery |
| PHP | `$server->getChatLoginToken()` |

The library returns the Query result. It does not talk to Matrix/Synapse itself.

Documented example:

```text
chatlogintoken
token=...
error id=0 msg=ok
```

### `licensesignmessage`

Signs the message with the private key of your license. This can prove that you own this license. Does not work for servers that have no license or use the default license.

| | |
|---|---|
| Usage | `licensesignmessage message={message}` |
| Parameters | `message` — arbitrary data |
| Permissions | `b_serverinstance_licensesign_message` |
| Transport | SSH, WebQuery |
| PHP | `$client->instance()->signLicenseMessage(string $message)` |

Documented example:

```text
licensesignmessage message=hello
certificate_chain=... signature=...
error id=0 msg=ok
```

## Bans

### `banfind`

Displays a list of matching bans on the selected virtual server. Optional, but at least one of `ip`, `name`, `uid`, or `mytsid` must be set.

| | |
|---|---|
| Usage | `banfind [ip={ip}] [name={name}] [uid={clientUID}] [mytsid=(mytsid)]` |
| Parameters | `ip`, `name`, `uid`, `mytsid` |
| Permissions | `b_client_ban_list` |
| Transport | SSH, WebQuery |
| PHP | `$server->findBans(?string $ip, ?string $name, ?string $uid, ?string $mytsid)` |

Documented example:

```text
banfind ip=1.2.3.4
banid=7 ip=... name=... reason=spam
error id=0 msg=ok
```

## Matrix homebase

These commands manage whether this virtual server is the Matrix homebase for a client.

### `homebaseset`

Sets this server as the Matrix homebase server for the calling client, or the client specified with `cldbid` if given.

| | |
|---|---|
| Usage | `homebaseset [cldbid={clientDBID}]` |
| Parameters | `cldbid` — integer client database id |
| Permissions | `b_virtualserver_homebase_set`; `b_virtualserver_homebase_manage` when using `cldbid` |
| Transport | SSH, WebQuery |
| PHP | `$server->setHomebase(?int $clientDatabaseId)` |

### `homebasedel`

Unsets this server as the Matrix homebase server for the calling client, or the client specified with `cldbid` if given.

| | |
|---|---|
| Usage | `homebasedel [cldbid={clientDBID}]` |
| Parameters | `cldbid` — integer client database id |
| Permissions | `b_virtualserver_homebase_manage` when using `cldbid` |
| Transport | SSH, WebQuery |
| PHP | `$server->deleteHomebase(?int $clientDatabaseId)` |

### `homebaseisset`

Checks if this server is set as the Matrix homebase server for the calling client, or the client specified with `cldbid` if given.

| | |
|---|---|
| Usage | `homebaseisset [cldbid={clientDBID}]` |
| Parameters | `cldbid` — integer client database id |
| Permissions | `b_virtualserver_homebase_manage` when using `cldbid` |
| Transport | SSH, WebQuery |
| PHP | `$server->isHomebaseSet(?int $clientDatabaseId)` |

### `homebaselist`

Lists all users that have set this server as their Matrix homebase.

| | |
|---|---|
| Usage | `homebaselist [return_code={return_code}]` |
| Parameters | `return_code` — echoed on the notify message (notify **payload schema is not documented**) |
| Permissions | `b_virtualserver_homebase_list` |
| Transport | SSH, WebQuery |
| PHP | `$server->listHomebases(?string $returnCode)` |

Documented example:

```text
homebaselist return_code=abc
return_code=abc cldbid=3 since=1627290921
error id=0 msg=ok
```

## HTTP file transfer token

### `ftgetchannelfilehttptoken`

When the TeamSpeak server is configured for HTTP file transfer, this command returns a JSON Web Token to use for authentication to a HTTP TeamSpeak file-transfer server.

| | |
|---|---|
| Usage | `ftgetchannelfilehttptoken cid={channelID}\|scid={special channelID}` |
| Parameters | `cid` — channel id; **or** `scid` — `avatars`, `icons`, `chat`, or `listuserfiles` |
| Permissions | (none listed in the help file) |
| Transport | **SSH only** (WebQuery documents all `ft*` commands as unsupported) |
| PHP | `$server->getChannelFileHttpToken(?int $channelId, ?string $specialChannel)` |

The library does **not** implement the HTTP/S3 transfer after the token.

Documented example:

```text
ftgetchannelfilehttptoken cid=1
cid=1 expires_in=60 jwt=...
error id=0 msg=ok
```

## Related TeamSpeak 6 deltas on shared commands

These files also exist in 3.13.8, with extra parameters in TS6 help:

- `servernotifyregister` — event `bans` (`$server->registerNotifications('bans')`)
- `servernotifyunregister` — optional `event` / `id`
- `permget` — `forauthenticationtoken` and optional `cid`
- `clientdblist` — option `-homebaseonly` (`$server->getDatabaseClients(['homebaseonly'])`)

# TeamSpeak operations

High-level methods map to documented Query commands. See the [command matrix](commands.md) and [TeamSpeak 6-only commands](teamspeak6-commands.md).

## MVP (all transports unless noted)

| PHP | Command | Transport |
|---|---|---|
| `$client->version()` | `version` | SSH, WebQuery |
| `$client->whoami()` | `whoami` | SSH, WebQuery |
| `$server->getInfo()` | `serverinfo` | SSH, WebQuery |
| `$server->getClients()` | `clientlist` | SSH, WebQuery |
| `$server->getChannels()` | `channellist` | SSH, WebQuery |
| `$client->login()` | `login` | SSH only; optional identity switch. SSH `connect()` already authenticates. |
| `$client->useServer()` | `use` | SSH (WebQuery uses the URL path) |

## Management slice

| PHP | Command |
|---|---|
| `$server->kick()` | `clientkick` |
| `$server->move()` | `clientmove` |
| `$server->poke()` | `clientpoke` |
| `$server->sendTextMessage()` | `sendtextmessage` |
| `$server->edit()` | `serveredit` |
| `$server->getServerGroups()` | `servergrouplist` |
| `$server->addClientToServerGroup()` | `clientaddservergroup` |
| `$server->getBans()` | `banlist` |
| `$server->banClient()` | `banclient` |
| `$server->getDatabaseClients()` | `clientdblist` |

## SSH session

| PHP | Command |
|---|---|
| `$client->logout()` | `logout` |
| `$client->createApiKey()` | `apikeyadd` |
| `$server->registerNotifications()` | `servernotifyregister` |
| `$server->unregisterNotifications()` | `servernotifyunregister` |
| `$client->drainNotifications()` | unverified notify lines |

## TeamSpeak 6

Requires `ServerFamily::TeamSpeak6`. See [TeamSpeak 6-only commands](teamspeak6-commands.md) for parameters and permissions.

| PHP | Command | Transport |
|---|---|---|
| `$server->createAuthenticationToken($seconds)` | `authenticationtoken` | SSH, WebQuery |
| `$server->getChatLoginToken()` | `chatlogintoken` | SSH, WebQuery |
| `$client->instance()->signLicenseMessage($message)` | `licensesignmessage` | SSH, WebQuery |
| `$server->findBans($ip, $name, $uid, $mytsid)` | `banfind` | SSH, WebQuery |
| `$server->setHomebase($cldbid)` | `homebaseset` | SSH, WebQuery |
| `$server->deleteHomebase($cldbid)` | `homebasedel` | SSH, WebQuery |
| `$server->isHomebaseSet($cldbid)` | `homebaseisset` | SSH, WebQuery |
| `$server->listHomebases($returnCode)` | `homebaselist` | SSH, WebQuery |
| `$server->getChannelFileHttpToken($cid, $scid)` | `ftgetchannelfilehttptoken` | SSH only |

Any other documented command can be reached with `$client->execute('commandname', $parameters, $options)` if it is in the active catalog (or, on TeamSpeak 6, even if it is not). That still is not a raw Query string.

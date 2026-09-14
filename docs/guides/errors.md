# Exceptions

All exceptions extend `TS3ServerBot\Exception\TeamSpeakException`.

| Exception | When |
|---|---|
| ConnectionException | Transport cannot connect or lost the socket |
| TimeoutException | Configured timeout elapsed |
| AuthenticationException | SSH auth failed, API key rejected, or Query login failed |
| ProtocolException | Encode/parse/JSON failure |
| UnexpectedResponseException | Missing expected items |
| ServerException | TeamSpeak `error id` / status.code is not success |
| PermissionException | Looks like a permission failure (`failed_permid` or message) |
| InvalidParameterException | Bad config or undocumented option |
| InvalidStateException | Called while disconnected |
| UnsupportedOperationException | Unknown command, WebQuery-incompatible command, or refused feature |
| TransportException | HTTP/SSH plumbing error |

Handle `TeamSpeakException` for a generic catch. Inspect `ServerException::getServerErrorId()` for the numeric TeamSpeak code.

A complete error-id table is **not** in the 3.13.8 documentation.

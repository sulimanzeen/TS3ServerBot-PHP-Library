# Security practices

- Use SSH or WebQuery only. Do not enable only raw Query on the TeamSpeak server if you want to use this library.
- Prefer SSH or HTTP behind a reverse proxy with TLS.
- Store API keys and passwords in environment variables, not in git.
- Use `x-api-key` (the library default). Do not copy examples that put keys in URLs.
- Give Query accounts the minimum permissions listed for each command in the version catalog.
- Allowlist bot IPs in `query_ip_allowlist.txt`.
- Assume exception messages may include TeamSpeak `msg=` text; the framework still redacts config secrets.
- Snapshot passwords and `ftkey` values are sensitive.

See [SECURITY.md](../SECURITY.md) in the library directory.

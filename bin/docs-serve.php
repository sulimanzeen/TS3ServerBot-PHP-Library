<?php

$root = dirname(__DIR__);
$port = getenv('TS3_DOCS_PORT') ?: '8000';
$docroot = $root . DIRECTORY_SEPARATOR . 'docs-site';

fwrite(STDOUT, "TS3ServerBot Query docs: http://localhost:{$port}\nOpen docs-site/index.html instead if you do not want a server.\nStop with Ctrl+C.\n");

passthru(escapeshellarg(PHP_BINARY) . ' -S localhost:' . escapeshellarg($port) . ' -t ' . escapeshellarg($docroot), $code);
exit($code);

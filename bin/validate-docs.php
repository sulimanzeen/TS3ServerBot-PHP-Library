<?php

declare(strict_types=0);

$root = dirname(__DIR__);
$errors = [];

function fail_list(array $errors)
{
    foreach ($errors as $error) {
        fwrite(STDERR, $error . PHP_EOL);
    }
    exit($errors ? 1 : 0);
}

$src = $root . DIRECTORY_SEPARATOR . 'src';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src));
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $code = file_get_contents($file->getPathname());
    if (preg_match('/class\s+RawQuery|const DEFAULT_QUERY_PORT\s*=\s*10011/', $code)) {
        $errors[] = 'Raw Query implementation found in ' . $file->getPathname();
    }
    if (!preg_match_all('/public(?: static)? function\s+(\w+)\s*\(/', $code, $methods)) {
        continue;
    }
    foreach ($methods[1] as $index => $name) {
        if (in_array($name, ['__construct', '__debugInfo'], true)) {
            continue;
        }
        $offset = strpos($code, $methods[0][$index]);
        $before = rtrim(substr($code, 0, $offset));
        if (substr($before, -2) !== '*/') {
            $errors[] = $file->getFilename() . '::' . $name . ' is missing PHPDoc.';
        }
    }
}

$docs = $root . DIRECTORY_SEPARATOR . 'docs';
$required = [
    'index.md',
    'guides/installation.md',
    'guides/first-connection.md',
    'guides/ssh.md',
    'guides/webquery.md',
    'guides/not-supported.md',
    'guides/architecture.md',
    'guides/errors.md',
    'guides/troubleshooting.md',
    'guides/security.md',
    'guides/compatibility.md',
    'guides/examples.md',
    'reference/commands.md',
    'reference/teamspeak6-commands.md',
    'reference/configuration.md',
    'reference/operations.md',
];
foreach ($required as $relative) {
    $path = $docs . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (!is_file($path)) {
        $errors[] = 'Missing documentation file: ' . $relative;
        continue;
    }
    $text = file_get_contents($path);
    if (preg_match('/\[([^\]]+)\]\(([^)]+)\)/', $text, $m)) {
        // checked globally below
    }
}

$notSupported = file_get_contents($docs . DIRECTORY_SEPARATOR . 'guides' . DIRECTORY_SEPARATOR . 'not-supported.md');
if (stripos($notSupported, 'not supported') === false || strpos($notSupported, '10011') === false) {
    $errors[] = 'not-supported.md must state that raw Query port 10011 is not supported.';
}

$catalogJson = file_get_contents($root . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Protocol' . DIRECTORY_SEPARATOR . 'command-catalog.json');
$catalogJson = preg_replace('/^\xEF\xBB\xBF/', '', $catalogJson);
$catalog = json_decode($catalogJson, true);
if (!is_array($catalog) || count($catalog) < 139) {
    $errors[] = 'Command catalog is incomplete.';
}

$catalogTs6Json = file_get_contents($root . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Protocol' . DIRECTORY_SEPARATOR . 'command-catalog-ts6.json');
$catalogTs6Json = preg_replace('/^\xEF\xBB\xBF/', '', $catalogTs6Json);
$catalogTs6 = json_decode($catalogTs6Json, true);
if (!is_array($catalogTs6) || count($catalogTs6) < 149) {
    $errors[] = 'TeamSpeak 6 command catalog is incomplete.';
} else {
    $ts6Names = array();
    foreach ($catalogTs6 as $row) {
        if (isset($row['name'])) {
            $ts6Names[$row['name']] = $row;
        }
    }
    foreach (array('authenticationtoken', 'banfind', 'chatlogintoken', 'ftgetchannelfilehttptoken', 'homebasedel', 'homebaseisset', 'homebaselist', 'homebaseset', 'licensesignmessage') as $name) {
        if (!isset($ts6Names[$name])) {
            $errors[] = 'TeamSpeak 6 catalog is missing ' . $name . '.';
        }
    }
    if (isset($ts6Names['ftgetchannelfilehttptoken']) && !empty($ts6Names['ftgetchannelfilehttptoken']['webQuery'])) {
        $errors[] = 'ftgetchannelfilehttptoken must be SSH-only on WebQuery.';
    }
}

$ts6Page = file_get_contents($docs . DIRECTORY_SEPARATOR . 'reference' . DIRECTORY_SEPARATOR . 'teamspeak6-commands.md');
foreach (array('authenticationtoken', 'banfind', 'chatlogintoken', 'ftgetchannelfilehttptoken', 'homebaseset') as $name) {
    if (strpos($ts6Page, '`' . $name . '`') === false) {
        $errors[] = 'teamspeak6-commands.md must explain ' . $name . '.';
    }
}

if (PHP_VERSION_ID >= 80100) {
    $examples = glob($root . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . '*.php');
    foreach ($examples as $example) {
        $output = [];
        $code = 0;
        exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($example), $output, $code);
        if ($code !== 0) {
            $errors[] = 'Example failed php -l: ' . basename($example);
        }
    }
}

if (!is_file($root . DIRECTORY_SEPARATOR . 'docs-site' . DIRECTORY_SEPARATOR . 'index.html')) {
    $errors[] = 'Documentation website is missing index.html. Run php bin/docs-build.php.';
}
if (!is_file($root . DIRECTORY_SEPARATOR . 'docs-site' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'search-index.js')) {
    $errors[] = 'Documentation search index is missing. Run php bin/docs-build.php.';
}

if ($errors) {
    fwrite(STDERR, "Documentation validation failed:\n");
    fail_list($errors);
}

fwrite(STDOUT, "Documentation validation passed.\n");
exit(0);

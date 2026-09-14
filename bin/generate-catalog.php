<?php

declare(strict_types=0);

/**
 * Regenerates command-catalog-ts6.json from official TeamSpeak 6.0.0-beta12.1 Query help files.
 *
 * Those server files are not part of the Composer package. This script is for maintainers who
 * have the 6.0.0-beta12.1 Query help tree next to this package during catalog updates.
 * Compatible with PHP 7.4 so it can run on older developer machines.
 */

$library = dirname(__DIR__);
$workspace = dirname($library);
$ts6Docs = $workspace . DIRECTORY_SEPARATOR . 'teamspeak6-server-win-x64' . DIRECTORY_SEPARATOR . 'serverquerydocs';

if (!is_dir($ts6Docs)) {
    fwrite(STDERR, "TeamSpeak 6.0.0-beta12.1 Query help files not found (maintainer catalog rebuild only).\n");
    exit(1);
}

$session = array('help' => true, 'login' => true, 'logout' => true, 'quit' => true, 'use' => true);
$sshOnlyExact = array(
    'help' => true,
    'login' => true,
    'logout' => true,
    'quit' => true,
    'use' => true,
    'servernotifyregister' => true,
    'servernotifyunregister' => true,
);

$files = glob($ts6Docs . DIRECTORY_SEPARATOR . '*.txt');
sort($files);
$catalog = array();

foreach ($files as $file) {
    $name = basename($file, '.txt');
    if ($name === 'help') {
        $catalog[] = array(
            'name' => 'help',
            'usage' => 'help [command]',
            'permissions' => array(),
            'parameters' => array(),
            'options' => array(),
            'sshOnly' => true,
            'webQuery' => false,
            'category' => 'session',
            'aliasOf' => null,
            'source' => 'serverquerydocs/help.txt',
            'description' => 'Shows ServerQuery command help. SSH only.',
        );
        continue;
    }

    $text = file_get_contents($file);
    $text = preg_replace("/\r\n|\r/", "\n", $text);

    $usage = $name;
    if (preg_match('/^Usage:\s*(.*?)(?=\nPermissions:|\nDescription:)/s', $text, $match)) {
        $usage = trim(preg_replace('/\s+/', ' ', $match[1]));
    }

    $permissions = array();
    if (preg_match('/Permissions:\s*(.*?)\nDescription:/s', $text, $match)) {
        if (preg_match_all('/\b([bi]_[a-z0-9_]+)\b/', $match[1], $perms)) {
            $permissions = array_values(array_unique($perms[1]));
        }
    }

    $parameters = array();
    if (preg_match_all('/\b([a-z][a-z0-9_]*)\s*=\s*[{\(]/', $usage, $paramMatch)) {
        $parameters = $paramMatch[1];
    }

    $options = array();
    if (preg_match_all('/-([a-z][a-z0-9_]*)/', $usage, $optMatch)) {
        $options = $optMatch[1];
    }

    if (preg_match('/Parameters:\s*(.*?)(?=\nExample:)/s', $text, $paramSection)) {
        if (preg_match_all('/^  ([a-z][a-z0-9_]+)\s*:/m', $paramSection[1], $fromDocs)) {
            $parameters = array_merge($parameters, $fromDocs[1]);
        }
        if (preg_match_all('/^  -([a-z][a-z0-9_]+)/m', $paramSection[1], $fromOptDocs)) {
            $options = array_merge($options, $fromOptDocs[1]);
        }
    }

    $parameters = array_values(array_unique($parameters));
    $options = array_values(array_unique($options));

    $description = '';
    if (preg_match('/Description:\s*(.*?)(?=\nParameters:|\nExample:)/s', $text, $match)) {
        $description = trim(preg_replace('/\s+/', ' ', $match[1]));
    }

    $aliasOf = null;
    if (preg_match('/Alias for ([a-z][a-z0-9_]*)/i', $text, $match)) {
        $aliasOf = $match[1];
    }

    $sshOnly = isset($sshOnlyExact[$name]) || strpos($name, 'ft') === 0;
    $catalog[] = array(
        'name' => $name,
        'usage' => $usage,
        'permissions' => $permissions,
        'parameters' => $parameters,
        'options' => $options,
        'sshOnly' => $sshOnly,
        'webQuery' => !$sshOnly,
        'category' => isset($session[$name]) ? 'session' : 'query',
        'aliasOf' => $aliasOf,
        'source' => 'serverquerydocs/' . $name . '.txt',
        'description' => $description,
    );
}

$out = $library . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Protocol' . DIRECTORY_SEPARATOR . 'command-catalog-ts6.json';
$json = json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if ($json === false) {
    fwrite(STDERR, "Failed to encode TS6 catalog JSON.\n");
    exit(1);
}

file_put_contents($out, $json . "\n");
fwrite(STDOUT, 'Wrote ' . count($catalog) . " TeamSpeak 6 commands to {$out}\n");
exit(0);

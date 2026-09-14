<?php

declare(strict_types=0);

/**
 * Builds a static HTML docs site that opens from disk (file://).
 * Compatible with PHP 7.4+.
 */

$library = dirname(__DIR__);
$docs = $library . DIRECTORY_SEPARATOR . 'docs';
$site = $library . DIRECTORY_SEPARATOR . 'docs-site';
$src = $library . DIRECTORY_SEPARATOR . 'src';

$nav = array(
    'Guides' => array(
        'Home' => 'index',
        'Installation' => 'guides/installation',
        'First connection' => 'guides/first-connection',
        'SSH' => 'guides/ssh',
        'WebQuery' => 'guides/webquery',
        'Architecture' => 'guides/architecture',
        'Not supported' => 'guides/not-supported',
        'Errors' => 'guides/errors',
        'Troubleshooting' => 'guides/troubleshooting',
        'Security' => 'guides/security',
        'Compatibility' => 'guides/compatibility',
        'Examples' => 'guides/examples',
    ),
    'Reference' => array(
        'Configuration' => 'reference/configuration',
        'Operations' => 'reference/operations',
        'Command matrix' => 'reference/commands',
        'API reference' => 'api',
    ),
    'TeamSpeak 6' => array(
        'TS6-only commands' => 'reference/teamspeak6-commands',
    ),
);

$searchIndex = array();

function ts3_slug($text)
{
    $text = strtolower(trim(strip_tags($text)));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function ts3_depth($page)
{
    return substr_count($page, '/');
}

function ts3_prefix($page)
{
    $depth = ts3_depth($page);
    if ($depth <= 0) {
        return '';
    }

    return str_repeat('../', $depth);
}

function ts3_asset($page, $file)
{
    return ts3_prefix($page) . 'assets/' . $file;
}

function ts3_root_href($fromPage, $targetPage)
{
    $fromDir = dirname($fromPage);
    $fromParts = ($fromDir === '.' || $fromDir === '') ? array() : explode('/', $fromDir);
    $toParts = explode('/', $targetPage);
    $toFile = array_pop($toParts);
    while ($fromParts && $toParts && $fromParts[0] === $toParts[0]) {
        array_shift($fromParts);
        array_shift($toParts);
    }
    $rel = str_repeat('../', count($fromParts));
    if ($toParts) {
        $rel .= implode('/', $toParts) . '/';
    }

    return $rel . $toFile . '.html';
}

function ts3_href($fromPage, $target)
{
    $target = str_replace('\\', '/', $target);
    if (stripos($target, 'SECURITY.md') !== false) {
        return ts3_root_href($fromPage, 'security');
    }
    $target = preg_replace('/\.(md|html)$/', '', $target);

    $fromDir = dirname($fromPage);
    if ($fromDir === '.' || $fromDir === '') {
        $fromDir = '';
    }

    if (!preg_match('#^(https?:)?/#', $target)) {
        $base = $fromDir === '' ? '' : $fromDir . '/';
        $resolved = $base . $target;
    } else {
        $resolved = ltrim($target, '/');
    }

    $parts = array();
    foreach (explode('/', $resolved) as $part) {
        if ($part === '' || $part === '.') {
            continue;
        }
        if ($part === '..') {
            array_pop($parts);
            continue;
        }
        $parts[] = $part;
    }
    $targetPage = preg_replace('/\.(md|html)$/', '', implode('/', $parts));

    $fromParts = $fromDir === '' ? array() : explode('/', $fromDir);
    $toParts = $targetPage === '' ? array() : explode('/', $targetPage);
    $toFile = array_pop($toParts);
    while ($fromParts && $toParts && $fromParts[0] === $toParts[0]) {
        array_shift($fromParts);
        array_shift($toParts);
    }
    $rel = str_repeat('../', count($fromParts));
    if ($toParts) {
        $rel .= implode('/', $toParts) . '/';
    }

    return $rel . $toFile . '.html';
}

function ts3_inline($text, $fromPage)
{
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', function ($m) use ($fromPage) {
        $href = $m[2];
        if (strpos($href, 'http://') === 0 || strpos($href, 'https://') === 0) {
            return '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . $m[1] . '</a>';
        }
        $href = str_replace('\\', '/', $href);
        $built = ts3_href($fromPage, $href);

        return '<a href="' . htmlspecialchars($built, ENT_QUOTES, 'UTF-8') . '">' . $m[1] . '</a>';
    }, $text);

    return $text;
}

function ts3_render_markdown($markdown, $fromPage, &$searchIndex)
{
    $markdown = str_replace(array("\r\n", "\r"), "\n", $markdown);
    $lines = explode("\n", $markdown);
    $html = '';
    $i = 0;
    $count = count($lines);
    while ($i < $count) {
        $line = $lines[$i];
        if (preg_match('/^```(\w+)?\s*$/', $line)) {
            $code = array();
            $i++;
            while ($i < $count && !preg_match('/^```\s*$/', $lines[$i])) {
                $code[] = $lines[$i];
                $i++;
            }
            $i++;
            $html .= '<pre><code>' . htmlspecialchars(implode("\n", $code), ENT_QUOTES, 'UTF-8') . '</code></pre>';
            continue;
        }
        if (preg_match('/^\|(.+)\|\s*$/', $line) && isset($lines[$i + 1]) && preg_match('/^\|[\s:|\-]+\|\s*$/', $lines[$i + 1])) {
            $rows = array();
            while ($i < $count && preg_match('/^\|(.+)\|\s*$/', $lines[$i], $rowMatch)) {
                if (!preg_match('/^\|[\s:|\-]+\|\s*$/', $lines[$i])) {
                    $cells = array_map('trim', explode('|', trim($lines[$i], "|\n")));
                    $rows[] = $cells;
                }
                $i++;
            }
            if ($rows) {
                $html .= '<div class="table-wrap"><table>';
                $header = array_shift($rows);
                $html .= '<thead><tr>';
                foreach ($header as $cell) {
                    $html .= '<th>' . ts3_inline($cell, $fromPage) . '</th>';
                }
                $html .= '</tr></thead><tbody>';
                foreach ($rows as $row) {
                    $html .= '<tr>';
                    foreach ($row as $cell) {
                        $html .= '<td>' . ts3_inline($cell, $fromPage) . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</tbody></table></div>';
            }
            continue;
        }
        if (preg_match('/^(#{1,3}) (.+)$/', $line, $h)) {
            $level = strlen($h[1]);
            $title = $h[2];
            $id = ts3_slug(trim($title, '`'));
            $html .= '<h' . $level . ' id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '">' . ts3_inline($title, $fromPage) . '</h' . $level . '>';
            $searchIndex[] = array(
                'type' => 'guide',
                'title' => trim($title, '`'),
                'keywords' => array(trim($title, '`'), $fromPage),
                'href' => $fromPage . '.html#' . $id,
                'summary' => 'Documentation heading',
            );
            $i++;
            continue;
        }
        if (preg_match('/^\d+\. (.+)$/', $line)) {
            $html .= '<ol>';
            while ($i < $count && preg_match('/^\d+\. (.+)$/', $lines[$i], $li)) {
                $html .= '<li>' . ts3_inline($li[1], $fromPage) . '</li>';
                $i++;
            }
            $html .= '</ol>';
            continue;
        }
        if (preg_match('/^- (.+)$/', $line)) {
            $html .= '<ul>';
            while ($i < $count && preg_match('/^- (.+)$/', $lines[$i], $li)) {
                $html .= '<li>' . ts3_inline($li[1], $fromPage) . '</li>';
                $i++;
            }
            $html .= '</ul>';
            continue;
        }
        if (trim($line) === '') {
            $i++;
            continue;
        }
        $para = array($line);
        $i++;
        while ($i < $count && trim($lines[$i]) !== '' && !preg_match('/^(#|\||- |\d+\. |```)/', $lines[$i])) {
            $para[] = $lines[$i];
            $i++;
        }
        $html .= '<p>' . ts3_inline(implode("\n", $para), $fromPage) . '</p>';
    }

    return $html;
}

function ts3_load_json($path)
{
    $json = file_get_contents($path);
    $json = preg_replace('/^\xEF\xBB\xBF/', '', $json);
    $data = json_decode($json, true);

    return is_array($data) ? $data : array();
}

function ts3_chip_list($items)
{
    if (!is_array($items) || count($items) === 0) {
        return '<span class="empty">—</span>';
    }
    $html = '<span class="chips">';
    foreach ($items as $item) {
        $html .= '<code class="chip">' . htmlspecialchars((string) $item, ENT_QUOTES, 'UTF-8') . '</code>';
    }
    $html .= '</span>';

    return $html;
}

function ts3_command_matrix_html($library, &$searchIndex)
{
    $ts3 = ts3_load_json($library . '/src/Protocol/command-catalog.json');
    $ts6 = ts3_load_json($library . '/src/Protocol/command-catalog-ts6.json');
    $byName = array();
    foreach ($ts3 as $row) {
        $byName[$row['name']]['ts3'] = $row;
    }
    foreach ($ts6 as $row) {
        $byName[$row['name']]['ts6'] = $row;
    }
    ksort($byName);

    $html = '<h1 id="command-matrix">ServerQuery command matrix</h1>';
    $html .= '<p>Verified for TeamSpeak 3.13.8 and 6.0.0-beta12.1. Filter by family. WebQuery excludes <code>ft*</code>, <code>help</code>, <code>login</code>, <code>logout</code>, <code>quit</code>, notify register, and <code>use</code>.</p>';
    $html .= '<p>Nine commands exist only on TeamSpeak 6.0.0-beta12.1 — see <a href="teamspeak6-commands.html">TS6-only commands</a>.</p>';
    $html .= '<div class="filters matrix-toolbar" data-table-filter="commands">';
    $html .= '<button type="button" class="is-active" data-filter="all">All</button>';
    $html .= '<button type="button" data-filter="both">Shared</button>';
    $html .= '<button type="button" data-filter="ts3">TeamSpeak 3</button>';
    $html .= '<button type="button" data-filter="ts6">TeamSpeak 6</button>';
    $html .= '<button type="button" data-filter="ts6only">TS6 only</button>';
    $html .= '<label class="matrix-search"><span class="sr-only">Filter commands</span>';
    $html .= '<input type="search" id="command-filter" placeholder="Filter commands, params, permissions…" autocomplete="off" spellcheck="false">';
    $html .= '</label></div>';
    $html .= '<div class="table-wrap command-matrix"><table id="command-table">';
    $html .= '<thead><tr><th>Command</th><th>Family</th><th>Transport</th><th>Parameters</th><th>Options</th><th>Permissions</th></tr></thead><tbody>';

    foreach ($byName as $name => $families) {
        $row = isset($families['ts6']) ? $families['ts6'] : $families['ts3'];
        $family = 'both';
        $familyLabel = 'TS3 + TS6';
        if (isset($families['ts3']) && !isset($families['ts6'])) {
            $family = 'ts3';
            $familyLabel = 'TS3';
        } elseif (!isset($families['ts3']) && isset($families['ts6'])) {
            $family = 'ts6';
            $familyLabel = 'TS6 only';
        }
        $params = isset($row['parameters']) ? $row['parameters'] : array();
        $options = isset($row['options']) ? $row['options'] : array();
        $perms = isset($row['permissions']) ? $row['permissions'] : array();
        $paramsText = implode(', ', $params);
        $optionsText = implode(', ', $options);
        $permsText = implode(', ', $perms);
        $webYes = !empty($row['webQuery']);
        $href = ($family === 'ts6') ? 'teamspeak6-commands.html#' . $name : '#' . $name;
        $html .= '<tr id="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" data-family="' . $family . '">';
        $html .= '<th scope="row"><a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"><code>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</code></a></th>';
        $html .= '<td><span class="pill family-' . $family . '">' . htmlspecialchars($familyLabel, ENT_QUOTES, 'UTF-8') . '</span></td>';
        $html .= '<td class="cell-transport"><span class="pill ' . ($webYes ? 'yes' : 'no') . '">WebQuery</span><span class="pill yes">SSH</span></td>';
        $html .= '<td>' . ts3_chip_list($params) . '</td>';
        $html .= '<td>' . ts3_chip_list($options) . '</td>';
        $html .= '<td>' . ts3_chip_list($perms) . '</td>';
        $html .= '</tr>';

        $keywords = array($name, $paramsText, $optionsText, $permsText, $familyLabel);
        if (!empty($row['description'])) {
            $keywords[] = $row['description'];
        }
        $searchIndex[] = array(
            'type' => 'command',
            'title' => $name,
            'keywords' => $keywords,
            'href' => ($family === 'ts6') ? 'reference/teamspeak6-commands.html#' . $name : 'reference/commands.html#' . $name,
            'summary' => isset($row['usage']) ? $row['usage'] : $name,
        );
        foreach ((array) $row['parameters'] as $param) {
            $searchIndex[] = array(
                'type' => 'parameter',
                'title' => $param,
                'keywords' => array($param, $name, 'parameter', 'arg'),
                'href' => ($family === 'ts6') ? 'reference/teamspeak6-commands.html#' . $name : 'reference/commands.html#' . $name,
                'summary' => 'Query parameter on `' . $name . '`',
            );
        }
        foreach ((array) $row['options'] as $option) {
            $searchIndex[] = array(
                'type' => 'option',
                'title' => '-' . $option,
                'keywords' => array($option, '-' . $option, $name, 'option'),
                'href' => 'reference/commands.html#' . $name,
                'summary' => 'Query option on `' . $name . '`',
            );
        }
    }
    $html .= '</tbody></table></div>';

    return $html;
}

function ts3_api_html($srcDir, &$searchIndex)
{
    $html = '<h1 id="api-reference">API reference</h1><p>Generated from PHPDoc in <code>src/</code>. Query targets are TeamSpeak 3.13.8 and TeamSpeak 6.0.0-beta12.1.</p>';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcDir));
    $files = array();
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    sort($files);

    foreach ($files as $path) {
        $code = file_get_contents($path);
        if (!preg_match('/namespace\s+([^;]+);/', $code, $ns)) {
            continue;
        }
        if (!preg_match('/\n(?:final |abstract )?(?:class|interface|enum)\s+(\w+)/', $code, $cls)) {
            continue;
        }
        $fqcn = trim($ns[1]) . '\\' . $cls[1];
        $id = str_replace('\\', '-', $fqcn);
        $html .= '<h2 id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($fqcn, ENT_QUOTES, 'UTF-8') . '</h2>';
        $rel = str_replace($srcDir . DIRECTORY_SEPARATOR, 'src/', $path);
        $html .= '<p class="path"><code>' . htmlspecialchars($rel, ENT_QUOTES, 'UTF-8') . '</code></p>';

        if (preg_match_all('/\/\*\*(.*?)\*\/\s*public(?: static)? function\s+(\w+)\s*\((.*?)\)/s', $code, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $doc = trim(preg_replace('/^\s*\*\s?/m', '', $match[1]));
                $methodId = $id . '-' . $match[2];
                $html .= '<h3 id="' . htmlspecialchars($methodId, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($match[2], ENT_QUOTES, 'UTF-8') . '</h3>';
                $html .= '<pre><code>public function ' . htmlspecialchars($match[2] . '(' . $match[3] . ')', ENT_QUOTES, 'UTF-8') . '</code></pre>';
                $html .= '<pre class="doc">' . htmlspecialchars($doc, ENT_QUOTES, 'UTF-8') . '</pre>';

                $params = array();
                if (preg_match_all('/\$(\w+)/', $match[3], $p)) {
                    $params = $p[1];
                }
                $searchIndex[] = array(
                    'type' => 'method',
                    'title' => $cls[1] . '::' . $match[2],
                    'keywords' => array_merge(array($match[2], $cls[1], $fqcn, 'function', 'method'), $params),
                    'href' => 'api.html#' . $methodId,
                    'summary' => preg_split('/\n/', $doc)[0],
                );
                foreach ($params as $param) {
                    $searchIndex[] = array(
                        'type' => 'parameter',
                        'title' => '$' . $param,
                        'keywords' => array($param, '$' . $param, $match[2], 'arg', 'argument', 'parameter'),
                        'href' => 'api.html#' . $methodId,
                        'summary' => 'PHP argument on ' . $cls[1] . '::' . $match[2],
                    );
                }
            }
        }
    }

    return $html;
}

function ts3_layout($title, $content, $page, $nav)
{
    $prefix = ts3_prefix($page);
    $navHtml = '';
    foreach ($nav as $group => $items) {
        $navHtml .= '<p class="nav-group">' . htmlspecialchars($group, ENT_QUOTES, 'UTF-8') . '</p><ul>';
        foreach ($items as $label => $target) {
            $active = ($page === $target) ? ' class="active"' : '';
            $href = htmlspecialchars(ts3_root_href($page, $target), ENT_QUOTES, 'UTF-8');
            $navHtml .= '<li' . $active . '><a href="' . $href . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a></li>';
        }
        $navHtml .= '</ul>';
    }

    $searchPage = htmlspecialchars(ts3_root_href($page, 'search'), ENT_QUOTES, 'UTF-8');

    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . ' — TS3ServerBot Query</title>
    <link rel="stylesheet" href="' . htmlspecialchars(ts3_asset($page, 'style.css'), ENT_QUOTES, 'UTF-8') . '">
</head>
<body data-depth="' . ts3_depth($page) . '" data-page="' . htmlspecialchars($page, ENT_QUOTES, 'UTF-8') . '">
<header>
    <div class="brand-block">
        <button type="button" class="menu-toggle" aria-label="Open navigation">Menu</button>
        <p class="brand">TS3ServerBot Query <span>TS3ServerBot.com</span></p>
        <p class="meta">Developer docs · TeamSpeak 3.13.8 + 6.0.0-beta12.1 · SSH + WebQuery</p>
    </div>
    <form class="search-form" action="' . $searchPage . '" method="get" role="search">
        <label class="sr-only" for="docs-search">Search documentation</label>
        <input id="docs-search" class="search-input" type="search" name="q" placeholder="Search commands, methods, args…" autocomplete="off" spellcheck="false">
        <kbd>/</kbd>
        <div class="search-results" hidden></div>
    </form>
</header>
<div class="layout">
    <nav>' . $navHtml . '</nav>
    <main' . (($page === 'reference/commands') ? ' class="page-wide page-commands"' : '') . '>
        ' . $content . '
    </main>
</div>
<script src="' . htmlspecialchars(ts3_asset($page, 'search-index.js'), ENT_QUOTES, 'UTF-8') . '"></script>
<script src="' . htmlspecialchars(ts3_asset($page, 'app.js'), ENT_QUOTES, 'UTF-8') . '"></script>
</body>
</html>
';
}

function ts3_write($site, $page, $html)
{
    $path = $site . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $page) . '.html';
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($path, $html);
}

$pages = array();
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($docs));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'md') {
        $rel = substr($file->getPathname(), strlen($docs) + 1);
        $rel = str_replace('\\', '/', $rel);
        $page = preg_replace('/\.md$/', '', $rel);
        $pages[$page] = $file->getPathname();
    }
}

foreach ($pages as $page => $path) {
    $raw = file_get_contents($path);
    $title = $page;
    if (preg_match('/^# (.+)$/m', $raw, $m)) {
        $title = $m[1];
    }
    if ($page === 'reference/commands') {
        $content = ts3_command_matrix_html($library, $searchIndex);
    } else {
        $content = ts3_render_markdown($raw, $page, $searchIndex);
    }
    ts3_write($site, $page, ts3_layout($title, $content, $page, $nav));
}

$api = ts3_api_html($src, $searchIndex);
ts3_write($site, 'api', ts3_layout('API reference', $api, 'api', $nav));

$security = ts3_render_markdown(file_get_contents($library . DIRECTORY_SEPARATOR . 'SECURITY.md'), 'security', $searchIndex);
ts3_write($site, 'security', ts3_layout('Security', $security, 'security', $nav));

$searchContent = '<h1 id="search">Search</h1><p>Type a command, PHP method, argument, or parameter. This page works offline.</p><div class="search-page-results"><p class="muted">Use the search bar, or add <code>?q=</code> to this URL.</p></div>';
ts3_write($site, 'search', ts3_layout('Search', $searchContent, 'search', $nav));

$indexJs = 'window.TS3_SEARCH_INDEX = ' . json_encode(array_values($searchIndex), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ";\n";
file_put_contents($site . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'search-index.js', $indexJs);

fwrite(STDOUT, 'Wrote static docs to ' . $site . ' (' . count($searchIndex) . " search entries)\n");
exit(0);

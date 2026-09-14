<?php

declare(strict_types=1);

namespace TS3ServerBot\Tests\Documentation;

use PHPUnit\Framework\TestCase;
use TS3ServerBot\Client\TeamSpeakClient;
use TS3ServerBot\Config\ClientConfig;
use TS3ServerBot\Domain\Instance;
use TS3ServerBot\Domain\VirtualServer;

final class PublicApiDocumentationTest extends TestCase
{
    /**
     * @param class-string $class
     *
     * @dataProvider publicClasses
     */
    public function testPublicMethodsHaveDocBlocks(string $class): void
    {
        $reflection = new \ReflectionClass($class);
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getDeclaringClass()->getName() !== $class) {
                continue;
            }
            if ($method->isConstructor() || $method->isDestructor()) {
                continue;
            }

            $doc = $method->getDocComment();
            self::assertNotFalse($doc, $class . '::' . $method->getName() . ' is missing PHPDoc.');
        }
    }

    /**
     * @return list<array{0: class-string}>
     */
    public static function publicClasses(): array
    {
        return [
            [TeamSpeakClient::class],
            [ClientConfig::class],
            [VirtualServer::class],
            [Instance::class],
        ];
    }

    public function testGuidesDoNotPresentRawModeAsSupported(): void
    {
        $guide = (string) file_get_contents(dirname(__DIR__, 2) . '/docs/guides/not-supported.md');

        self::assertStringContainsString('not supported', strtolower($guide));
        self::assertStringContainsString('10011', $guide);
    }

    public function testCompatibilityDocumentsTeamSpeak6Query(): void
    {
        $guide = (string) file_get_contents(dirname(__DIR__, 2) . '/docs/guides/compatibility.md');

        self::assertStringContainsString('TeamSpeak 6', $guide);
        self::assertStringContainsString('ServerFamily::TeamSpeak6', $guide);
        self::assertStringNotContainsString('out of documentation scope', $guide);
        self::assertStringNotContainsString('teamspeak3-server', $guide);
        self::assertStringNotContainsString('teamspeak6-server', $guide);
    }

    public function testShippedDocsNameServerVersionsNotBuildFolders(): void
    {
        $root = dirname(__DIR__, 2);
        $files = [
            $root . '/README.md',
            $root . '/CONTRIBUTING.md',
            $root . '/CHANGELOG.md',
            $root . '/SECURITY.md',
            $root . '/docs/index.md',
            $root . '/docs/guides/compatibility.md',
            $root . '/docs-site/index.html',
            $root . '/docs-site/guides/compatibility.html',
        ];
        foreach (glob($root . '/docs/{guides,reference}/*.md', GLOB_BRACE) ?: [] as $guide) {
            $files[] = $guide;
        }
        foreach ($files as $file) {
            $text = (string) file_get_contents($file);
            self::assertStringNotContainsString('teamspeak3-server_win64', $text, $file);
            self::assertStringNotContainsString('teamspeak6-server-win-x64', $text, $file);
        }
        foreach ([$root . '/README.md', $root . '/docs/index.md', $root . '/docs/guides/compatibility.md'] as $file) {
            $text = (string) file_get_contents($file);
            self::assertStringContainsString('3.13.8', $text, $file);
            self::assertStringContainsString('6.0.0-beta12.1', $text, $file);
        }
    }

    public function testTeamSpeak6CommandCategoryExplainsNineCommands(): void
    {
        $guide = (string) file_get_contents(dirname(__DIR__, 2) . '/docs/reference/teamspeak6-commands.md');

        self::assertStringContainsString('authenticationtoken', $guide);
        self::assertStringContainsString('banfind', $guide);
        self::assertStringContainsString('chatlogintoken', $guide);
        self::assertStringContainsString('ftgetchannelfilehttptoken', $guide);
        self::assertStringContainsString('homebaseset', $guide);
        self::assertStringContainsString('SSH only', $guide);
        self::assertStringContainsString('createAuthenticationToken', $guide);
    }

    public function testCommandMatrixUsesFullWidthLayout(): void
    {
        $html = (string) file_get_contents(dirname(__DIR__, 2) . '/docs-site/reference/commands.html');

        self::assertStringContainsString('class="page-wide page-commands"', $html);
        self::assertStringContainsString('class="table-wrap command-matrix"', $html);
        self::assertStringContainsString('id="command-filter"', $html);
        self::assertStringContainsString('id="serverprocessstop"', $html);
        self::assertStringContainsString('class="chip"', $html);
        self::assertStringNotContainsString('<th>WebQuery</th><th>SSH</th>', $html);
    }

    public function testStaticDocsSiteHasSearchIndex(): void
    {
        $index = (string) file_get_contents(dirname(__DIR__, 2) . '/docs-site/assets/search-index.js');
        $html = (string) file_get_contents(dirname(__DIR__, 2) . '/docs-site/index.html');

        self::assertStringContainsString('TS3_SEARCH_INDEX', $index);
        self::assertStringContainsString('banfind', $index);
        self::assertStringContainsString('findBans', $index);
        self::assertStringContainsString('docs-search', $html);
        self::assertFileExists(dirname(__DIR__, 2) . '/docs-site/reference/teamspeak6-commands.html');
    }
}

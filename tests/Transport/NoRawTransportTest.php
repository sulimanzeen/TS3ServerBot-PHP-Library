<?php

declare(strict_types=1);

namespace TS3ServerBot\Tests\Transport;

use PHPUnit\Framework\TestCase;
use TS3ServerBot\Config\Transport;

final class NoRawTransportTest extends TestCase
{
    public function testTransportEnumHasOnlySshAndWebQuery(): void
    {
        $cases = array_map(static fn (Transport $transport): string => $transport->value, Transport::cases());

        self::assertSame(['ssh', 'webquery'], $cases);
        self::assertNotContains('raw', $cases);
        self::assertNotContains('telnet', $cases);
    }

    public function testSourceTreeDoesNotImplementRawQuery(): void
    {
        $root = dirname(__DIR__, 2);
        $hits = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root . '/src'));

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = (string) file_get_contents($file->getPathname());
            if (preg_match('/RawQuery|queryPort\s*=\s*10011|DEFAULT_QUERY_PORT/', $contents)) {
                $hits[] = $file->getPathname();
            }
        }

        self::assertSame([], $hits);
        self::assertFileDoesNotExist($root . '/src/Transport/RawQueryTransport.php');
    }
}

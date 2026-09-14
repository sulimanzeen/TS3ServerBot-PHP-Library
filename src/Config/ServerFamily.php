<?php

declare(strict_types=1);

namespace TS3ServerBot\Config;

/**
 * TeamSpeak server family the Query client is talking to.
 *
 * Both families use SSH ServerQuery and WebQuery. Raw/telnet Query is never used.
 * TeamSpeak 3 uses the 3.13.8 catalog. TeamSpeak 6 uses the 6.0.0-beta12.1 catalog.
 *
 * @package TS3ServerBot
 */
enum ServerFamily: string
{
    case TeamSpeak3 = 'teamspeak3';
    case TeamSpeak6 = 'teamspeak6';
}

<?php

declare(strict_types=1);

namespace TS3ServerBot\Transport;

use TS3ServerBot\Config\Transport;
use TS3ServerBot\Protocol\QueryCommand;
use TS3ServerBot\Protocol\QueryResponse;

/**
 * Sends ServerQuery operations over SSH or WebQuery to TeamSpeak 3.13.8 or TeamSpeak 6.
 *
 * Implementations exist for SSH and WebQuery only.
 *
 * @package TS3ServerBot
 */
interface TransportInterface
{
    /**
     * Opens the transport.
     */
    public function connect(): void;

    /**
     * Closes the transport.
     */
    public function disconnect(): void;

    /**
     * Whether the transport currently has an open session.
     */
    public function isConnected(): bool;

    /**
     * Executes one catalog command and returns the decoded response.
     */
    public function execute(QueryCommand $command): QueryResponse;

    /**
     * Identifies the transport as SSH or WebQuery.
     */
    public function name(): Transport;
}

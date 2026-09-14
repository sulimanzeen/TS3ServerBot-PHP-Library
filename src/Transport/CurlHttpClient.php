<?php

declare(strict_types=1);

namespace TS3ServerBot\Transport;

use TS3ServerBot\Exception\TransportException;

/**
 * cURL implementation of WebQuery HTTP POST.
 *
 * @package TS3ServerBot
 */
final class CurlHttpClient implements HttpClientInterface
{
    /**
     * {@inheritDoc}
     */
    public function post(string $url, array $headers, string $body, float $timeout, bool $verifyTls): HttpResult
    {
        if (!function_exists('curl_init')) {
            throw new TransportException('The curl extension is required for WebQuery.');
        }

        $handle = curl_init($url);
        if ($handle === false) {
            throw new TransportException('Unable to start a WebQuery HTTP request.');
        }

        $headerLines = [];
        foreach ($headers as $name => $value) {
            $headerLines[] = $name . ': ' . $value;
        }

        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => (int) ceil($timeout),
            CURLOPT_CONNECTTIMEOUT => (int) ceil($timeout),
            CURLOPT_SSL_VERIFYPEER => $verifyTls,
            CURLOPT_SSL_VERIFYHOST => $verifyTls ? 2 : 0,
        ]);

        $response = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if ($response === false) {
            throw new TransportException('WebQuery HTTP request failed.');
        }

        if ($error !== '') {
            throw new TransportException('WebQuery HTTP request failed.');
        }

        return new HttpResult($status, (string) $response);
    }
}

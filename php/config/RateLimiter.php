<?php

declare(strict_types=1);

/**
 * Lightweight fixed-window limiter shared by PHP requests on this host.
 * The key is hashed before it is used as a filename, so email addresses and
 * client IPs are not written to disk in plain text.
 */
final class RateLimiter
{
    public static function consume(string $key, int $limit, int $windowSeconds): int
    {
        $directory = dirname(__DIR__, 2) . '/storage/rate-limits';
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('Rate limiter storage is unavailable.');
        }

        $path = $directory . '/' . hash('sha256', $key) . '.json';
        $handle = fopen($path, 'c+');
        if ($handle === false || !flock($handle, LOCK_EX)) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            throw new RuntimeException('Rate limiter storage is unavailable.');
        }

        try {
            rewind($handle);
            $contents = stream_get_contents($handle);
            $record = is_string($contents) ? json_decode($contents, true) : null;
            $now = time();

            if (!is_array($record)
                || !isset($record['started'], $record['count'])
                || !is_int($record['started'])
                || !is_int($record['count'])
                || $record['started'] + $windowSeconds <= $now
            ) {
                $record = ['started' => $now, 'count' => 0];
            }

            $retryAfter = max(0, $record['started'] + $windowSeconds - $now);
            if ($record['count'] >= $limit) {
                return $retryAfter;
            }

            $record['count']++;
            rewind($handle);
            ftruncate($handle, 0);
            fwrite($handle, json_encode($record, JSON_THROW_ON_ERROR));
            fflush($handle);

            return 0;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    public static function clear(string $key): void
    {
        $path = dirname(__DIR__, 2) . '/storage/rate-limits/' . hash('sha256', $key) . '.json';
        if (is_file($path)) {
            @unlink($path);
        }
    }

    public static function clientIp(): string
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
    }
}

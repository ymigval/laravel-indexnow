<?php

namespace Ymigval\LaravelIndexnow;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\File;

class PreventSpan
{
    /**
     * Path to the temporary usage blocking log file of IndexNow.
     */
    private const BLOCKING_LOG_FILE_PATH = __DIR__ . '/../storage/temporary_blocking.txt';

    /**
     * Checks if sending requests is allowed.
     */
    public static function isAllowed(): bool
    {
        if (!self::shouldBlock()) {
            self::deleteBlockingLogFile();
            return true;
        }
        return false;
    }

    /**
     * Detects potential spam and creates a blocking file if necessary.
     */
    public static function detectPotentialSpam(Response $response): void
    {
        if ($response->status() === 429) {
            $timestamp = strtotime('+1 day');
            File::put(self::BLOCKING_LOG_FILE_PATH, $timestamp);
        }
    }

    /**
     * Deletes the temporary blocking log file.
     */
    public static function deleteBlockingLogFile(): bool
    {
        return File::delete(self::BLOCKING_LOG_FILE_PATH);
    }

    /**
     * Determines whether requests should be blocked based on the log file timestamp.
     */
    private static function shouldBlock(): bool
    {
        if (!File::exists(self::BLOCKING_LOG_FILE_PATH)) {
            return false;
        }

        $timestamp = File::get(self::BLOCKING_LOG_FILE_PATH);

        return self::isTimestampValid($timestamp) && (int)$timestamp > time();
    }

    /**
     * Validates if a given timestamp is in the correct format.
     */
    private static function isTimestampValid(mixed $timestamp): bool
    {
        if (!is_numeric($timestamp) || (int)$timestamp < 0) {
            return false;
        }

        $timestampParts = date_parse(date('Y-m-d H:i:s', (int)$timestamp));
        return $timestampParts['error_count'] === 0
            && checkdate($timestampParts['month'], $timestampParts['day'], $timestampParts['year']);
    }
}

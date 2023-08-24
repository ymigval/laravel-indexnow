<?php

namespace Ymigval\LaravelIndexnow;

use Illuminate\Support\Facades\File;
use \Illuminate\Http\Client\Response;

class PreventSpan
{

    /**
     * Path to the temporary usage blocking log file of IndexNow.
     *
     * @var string
     */
    private static $blockingLogFilePath = __DIR__ . "/../storage/temporary_blocking.txt";

    /**
     * Checks if sending requests is allowed.
     *
     * @return bool
     */
    public static function isAllowed(): bool
    {
        if (File::exists(static::$blockingLogFilePath)) {
            $timestamp = File::get(static::$blockingLogFilePath);

            if (self::isValidTimestamp($timestamp) && $timestamp > time()) {
                $status = false;
            } else {
                $status = true;
            }
        } else {
            $status = true;
        }

        if ($status) {
            self::deleteBlockingLogFile();
        }

        return $status;
    }

    /**
     * Process response to detect possible span
     *
     * @return bool
     */
    public static function detectPotentialSpam(Response $response): void
    {
        // In the IndexNow documentation, a response code 429 is considered Too Many Requests (potential spam).
        // If this occurs, a temporary blocking file is created until a given date.
        if ($response->status() == 429) {
            $timestamp = strtotime('+1 day');
            File::put(static::$blockingLogFilePath, $timestamp);
        } 
    }

    /**
     * Deletes the temporary blocking log file.
     *
     * @return bool
     */
    public static function deleteBlockingLogFile(): bool
    {
        return File::delete(static::$blockingLogFilePath);
    }

    private static function isValidTimestamp($timestamp)
    {
        if (!is_numeric($timestamp)) {
            return false;
        }

        $timestamp = (int) $timestamp;

        if ($timestamp < 0) {
            return false;
        }

        $timestampParts = date_parse(date('Y-m-d H:i:s', $timestamp));

        if ($timestampParts['error_count'] === 0 && checkdate($timestampParts['month'], $timestampParts['day'], $timestampParts['year'])) {
            return true;
        } else {
            return false;
        }
    }
}

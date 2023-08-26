<?php

namespace Ymigval\LaravelIndexnow;

use DateTimeInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LogManager
{
    /**
     * Path to the log file.
     *
     * @var string
     */
    private static $logFilePath = __DIR__ . "/../storage/log.txt";

    /**
     * Show logs.
     *
     * @return string
     */
    public static function showLogs()
    {
        if (File::exists(static::$logFilePath)) {
            return File::get(static::$logFilePath);
        }

        return '';
    }

    /**
     * Add log entry.
     * @param  mixed $message
     * @return void
     */
    public static function addLog($message): void
    {
        self::writeLog($message);
    }

    /**
     * Delete the log file.
     *
     * @return bool
     */
    public static function deleteLogFile(): bool
    {
        return File::delete(static::$logFilePath);
    }

    /**
     * Write to the log file.
     *
     * @param  mixed $message
     * @return string
     */
    private static function writeLog($message): string
    {
        if (Config::get('indexnow.enable_logging') === false) {
            return 'logging is disabled';
        }

        if (File::exists(static::$logFilePath)) {
            // If the log file size exceeds 500,000 Bytes, delete it
            if (File::size(static::$logFilePath) > 500000) {
                self::deleteLogFile();
            }
        }

        $logEntry = Str::of(date(DateTimeInterface::W3C))
            ->append(' > ')
            ->append(json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
            ->append(PHP_EOL);

        File::append(static::$logFilePath, $logEntry);

        return $logEntry;
    }
}

<?php

namespace Ymigval\LaravelIndexnow;

use DateTimeInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LogManager
{
    /**
     * Maximum allowed size of the log file in bytes.
     */
    private const MAX_LOG_FILE_SIZE = 500000;


    /**
     * Date format used in log entries.
     */
    private const LOG_DATE_FORMAT = 'Y-m-d H:i:s';


    /**
     * Path to the log file.
     *
     * @var string
     */
    private static string $logFilePath = __DIR__ . '/../storage/log.txt';

    /**
     * Get the path to the log file.
     */
    private static function getLogFilePath(): string
    {
        return self::$logFilePath;
    }

    /**
     * Display the contents of the log file.
     */
    public static function showLogs(): string
    {
        if (File::exists(self::getLogFilePath())) {
            return File::get(self::getLogFilePath());
        }

        return '';
    }

    /**
     * Add a log entry to the log file.
     */
    public static function addMessage(string|array $message): void
    {
        self::write($message);
    }

    /**
     * Delete the log file.
     */
    public static function clearLogs(): bool
    {
        return File::delete(self::getLogFilePath());
    }

    /**
     * Ensures that the log file does not exceed the maximum allowed size.
     */
    private static function ensureLogFileWithinSizeLimit(): void
    {
        if (File::exists(self::getLogFilePath()) && File::size(self::getLogFilePath()) > self::MAX_LOG_FILE_SIZE) {
            self::clearLogs();
        }
    }

    /**
     * Write a message to the log file.
     */
    private static function write(string|array $message): void
    {
        if (Config::get('indexnow.enable_logging') === false) {
            return;
        }

        // Ensure the log file is within the defined size limit.
        self::ensureLogFileWithinSizeLimit();

        $logEntry = Str::of(sprintf("[%s]", date(self::LOG_DATE_FORMAT)))
            ->append(' ')
            ->append(json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
            ->append(PHP_EOL);

        File::append(self::getLogFilePath(), $logEntry);

    }
}

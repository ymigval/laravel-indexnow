<?php

namespace Ymigval\LaravelIndexnow;

use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class PreventSpam
{
    /**
     * Default blocking period in hours
     */
    private const DEFAULT_BLOCKING_HOURS = 24;

    /**
     * HTTP status codes that indicate potential spam or rate limiting
     */
    private const SPAM_DETECTION_STATUS_CODES = [
        429, // Too Many Requests
        403, // Forbidden (often used for rate limiting)
        418, // I'm a teapot (can be used to indicate bot detection)
    ];

    /**
     * Path to the temporary usage blocking data file
     */
    private static function getBlockingFilePath(): string
    {
        return storage_path('app/indexnow/spam_blocking.json');
    }

    /**
     * Check if request sending is currently allowed
     *
     * @return bool True if sending is allowed, false if blocked
     */
    public static function isAllowed(): bool
    {
        // If spam detection is disabled in config, always allow
        if (Config::get('indexnow.enable_spam_detection', true) === false) {
            return true;
        }

        // Check if blocking should be applied
        if (!self::shouldBlock()) {
            // If we had a block file but it's expired, clean it up
            if (File::exists(self::getBlockingFilePath())) {
                self::deleteBlockingFile();
            }
            return true;
        }

        // Get remaining time and log that requests are blocked
        $remainingTime = self::getRemainingBlockTime();
        LogManager::addMessage(sprintf(
            'Request blocked: Temporary submission limit reached. Will resume in %s',
            self::formatRemainingTime($remainingTime)
        ));

        return false;
    }

    /**
     * Format the remaining time in a human-readable format
     *
     * @param int $seconds Remaining seconds
     * @return string Formatted time string
     */
    private static function formatRemainingTime(int $seconds): string
    {
        if ($seconds < 60) {
            return "$seconds seconds";
        }

        if ($seconds < 3600) {
            return round($seconds / 60) . " minutes";
        }

        return round($seconds / 3600, 1) . " hours";
    }

    /**
     * Detect potential spam based on API response and block if necessary
     *
     * @param Response $response The HTTP response to check
     * @param string|null $url The URL that was being submitted (for logging)
     * @return void
     */
    public static function detectPotentialSpam(Response $response, ?string $url = null): void
    {
        $statusCode = $response->status();

        // Check if the status code indicates potential spam or rate limiting
        if (in_array($statusCode, self::SPAM_DETECTION_STATUS_CODES)) {
            // Get blocking data
            $blockingData = self::createBlockingData();

            // Log the blocking
            LogManager::addMessage([
                'type' => 'spam_protection',
                'status' => 'blocked',
                'reason' => "Received status code $statusCode",
                'url' => $url,
                'blocked_until' => $blockingData['expires_at'],
                'retry_after' => $response->header('Retry-After')
            ]);

            // Store the blocking data
            self::saveBlockingData($blockingData);
        }
    }

    /**
     * Get the remaining block time in seconds
     *
     * @return int Seconds until block expiration (0 if not blocked)
     */
    public static function getRemainingBlockTime(): int
    {
        if (!File::exists(self::getBlockingFilePath())) {
            return 0;
        }

        try {
            $blockingData = json_decode(File::get(self::getBlockingFilePath()), true);

            if (!isset($blockingData['expires_at'])) {
                return 0;
            }

            $expiresAt = Carbon::parse($blockingData['expires_at']);
            $now = Carbon::now();

            if ($now->gt($expiresAt)) {
                return 0;
            }

            return $now->diffInSeconds($expiresAt);
        } catch (\Exception $e) {
            LogManager::addMessage('Error reading spam blocking data: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Delete the temporary blocking file
     *
     * @return bool True if file was deleted successfully
     */
    public static function deleteBlockingFile(): bool
    {
        if (File::exists(self::getBlockingFilePath())) {
            LogManager::addMessage('Spam protection: Blocking file cleared - submissions resumed');
            return File::delete(self::getBlockingFilePath());
        }

        return true;
    }

    /**
     * Reset blocking status and allow requests again
     *
     * @return bool True if reset was successful
     */
    public static function resetBlocking(): bool
    {
        return self::deleteBlockingFile();
    }

    /**
     * Determine if requests should be blocked
     *
     * @return bool True if requests should be blocked
     */
    private static function shouldBlock(): bool
    {
        if (!File::exists(self::getBlockingFilePath())) {
            return false;
        }

        try {
            $blockingData = json_decode(File::get(self::getBlockingFilePath()), true);

            if (!isset($blockingData['expires_at'])) {
                return false;
            }

            $expiresAt = Carbon::parse($blockingData['expires_at']);
            return Carbon::now()->lt($expiresAt);
        } catch (\Exception $e) {
            LogManager::addMessage('Error checking spam blocking status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create blocking data with expiration time
     *
     * @return array The blocking data
     */
    private static function createBlockingData(): array
    {
        // Get blocking duration from config or use default
        $blockingHours = Config::get('indexnow.spam_blocking_hours', self::DEFAULT_BLOCKING_HOURS);

        // Ensure directory exists
        $directory = dirname(self::getBlockingFilePath());
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        return [
            'blocked_at' => Carbon::now()->toIso8601String(),
            'expires_at' => Carbon::now()->addHours($blockingHours)->toIso8601String(),
            'reason' => 'Rate limit exceeded or potential spam detected',
            'duration_hours' => $blockingHours
        ];
    }

    /**
     * Save blocking data to file
     *
     * @param array $data The blocking data to save
     * @return bool True if saved successfully
     */
    private static function saveBlockingData(array $data): bool
    {
        try {
            // Ensure directory exists
            $directory = dirname(self::getBlockingFilePath());
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            File::put(self::getBlockingFilePath(), json_encode($data, JSON_PRETTY_PRINT));
            return true;
        } catch (\Exception $e) {
            LogManager::addMessage('Error saving spam blocking data: ' . $e->getMessage());
            return false;
        }
    }
}

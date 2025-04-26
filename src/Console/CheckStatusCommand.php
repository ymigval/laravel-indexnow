<?php

namespace Ymigval\LaravelIndexnow\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Ymigval\LaravelIndexnow\Exceptions\InvalidKeyException;
use Ymigval\LaravelIndexnow\Exceptions\KeyFileDoesNotExistException;
use Ymigval\LaravelIndexnow\IndexNowApiKeyManager;
use Ymigval\LaravelIndexnow\LogManager;

class CheckStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indexnow:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the status of IndexNow package configuration and verify setup';

    /**
     * Status messages and indicators.
     */
    private const MESSAGES = [
        'title' => '<options=bold>IndexNow Package Status</>',
        'separator' => '-------------------------------------',
        'key_status' => 'API Key: <fg=%s>%s</>',
        'key_validity' => 'Key validity: <fg=%s>%s</>',
        'key_url' => 'Key URL: <options=bold>%s</>',
        'key_url_status' => 'Key URL accessibility: <fg=%s>%s</>',
        'config_path' => 'Config file: <fg=%s>%s</>',
        'search_engine' => 'Search engine: <options=bold>%s</>',
        'submissions_status' => 'Submissions enabled: <fg=%s>%s</>',
        'logging_status' => 'Logging enabled: <fg=%s>%s</>',
        'log_path' => 'Log file: <fg=%s>%s</>',
        'route_status' => 'Key route registered: <fg=%s>%s</>',
        'spam_detection' => 'Spam protection: <fg=%s>%s</>',
        'spam_blocking_status' => 'Submission block status: <fg=%s>%s</>',
        'recommendation' => '<fg=yellow>Recommendation:</> %s',
        'error' => '<fg=red>Error:</> %s',
        'success' => '<fg=green>Success:</> %s',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->displayHeader();

        // Check configuration file
        $this->checkConfigFile();

        // Check API key
        try {
            $apiKey = $this->checkApiKey();

            // Only proceed with key-related checks if we have a valid key
            if ($apiKey) {
                $keyUrl = $this->getKeyUrl($apiKey);
                $this->checkKeyUrlAccessibility($keyUrl);
                $this->checkRouteRegistration($apiKey);
            }
        } catch (Exception $e) {
            $this->line(sprintf(self::MESSAGES['error'], $e->getMessage()));
        }

        // Check search engine configuration
        $this->checkSearchEngine();

        // Check submissions settings
        $this->checkSubmissionsStatus();

        // Check logging configuration
        $this->checkLoggingStatus();

        // Check spam protection status
        $this->checkSpamProtectionStatus();

        // Display overall package status and recommendations
        $this->displaySummary();

        return self::SUCCESS;
    }

    /**
     * Display the command header.
     */
    private function displayHeader(): void
    {
        $this->newLine();
        $this->line(self::MESSAGES['title']);
        $this->line(self::MESSAGES['separator']);
        $this->newLine();
    }

    /**
     * Check if the config file exists and is properly loaded.
     */
    private function checkConfigFile(): void
    {
        $configPath = config_path('indexnow.php');
        $fileExists = File::exists($configPath);

        $statusColor = $fileExists ? 'green' : 'red';
        $statusText = $fileExists ? 'Found' : 'Not found';

        $this->line(sprintf(self::MESSAGES['config_path'], $statusColor, $statusText));

        if (!$fileExists) {
            $this->line(sprintf(
                self::MESSAGES['recommendation'],
                'Run "php artisan vendor:publish --tag=indexnow" to publish the configuration file.'
            ));
        }
    }

    /**
     * Check API key configuration.
     */
    private function checkApiKey(): ?string
    {
        try {
            $apiKey = IndexNowApiKeyManager::getKey();

            // Display API key status
            $this->line(sprintf(
                self::MESSAGES['key_status'],
                'green',
                'Configured: ' . substr($apiKey, 0, 4) . '...' . substr($apiKey, -4)
            ));

            // Display key validity
            $this->line(sprintf(self::MESSAGES['key_validity'], 'green', 'Valid'));

            return $apiKey;

        } catch (InvalidKeyException $e) {
            $this->line(sprintf(self::MESSAGES['key_status'], 'red', 'Invalid'));
            $this->line(sprintf(self::MESSAGES['key_validity'], 'red', 'Invalid: ' . $e->getMessage()));

            $this->line(sprintf(
                self::MESSAGES['recommendation'],
                'Configure a valid API key in your .env file with INDEXNOW_API_KEY or generate a new one.'
            ));

            return null;

        } catch (KeyFileDoesNotExistException $e) {
            $this->line(sprintf(self::MESSAGES['key_status'], 'red', 'Missing'));

            $this->line(sprintf(
                self::MESSAGES['recommendation'],
                'Configure an API key in your .env file with INDEXNOW_API_KEY.'
            ));

            return null;

        } catch (Exception $e) {
            $this->line(sprintf(self::MESSAGES['key_status'], 'red', 'Error'));
            $this->line(sprintf(self::MESSAGES['error'], $e->getMessage()));

            return null;
        }
    }

    /**
     * Get the verification URL for the API key.
     */
    private function getKeyUrl(string $apiKey): string
    {
        $keyUrl = sprintf(
            '%s/%s.txt',
            rtrim(config('app.url'), '/'),
            $apiKey
        );

        $this->line(sprintf(self::MESSAGES['key_url'], $keyUrl));

        return $keyUrl;
    }

    /**
     * Check if the key URL is accessible.
     */
    private function checkKeyUrlAccessibility(string $url): void
    {
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $isAccessible = $httpCode === 200;
            $statusColor = $isAccessible ? 'green' : 'red';
            $statusText = $isAccessible
                ? 'Accessible (HTTP 200)'
                : 'Not accessible (HTTP ' . $httpCode . ')';

            $this->line(sprintf(self::MESSAGES['key_url_status'], $statusColor, $statusText));

            if (!$isAccessible) {
                $this->line(sprintf(
                    self::MESSAGES['recommendation'],
                    'Check your server configuration to ensure the key file is publicly accessible.'
                ));
            }

        } catch (Exception $e) {
            $this->line(sprintf(
                self::MESSAGES['key_url_status'],
                'red',
                'Error checking URL: ' . $e->getMessage()
            ));
        }
    }

    /**
     * Check if the route for the key file is registered.
     */
    private function checkRouteRegistration(string $apiKey): void
    {
        $routeName = 'indexnow_key_verification';
        $routeExists = Route::has($routeName);

        $statusColor = $routeExists ? 'green' : 'red';
        $statusText = $routeExists ? 'Yes' : 'No';

        $this->line(sprintf(self::MESSAGES['route_status'], $statusColor, $statusText));

        if (!$routeExists) {
            $this->line(sprintf(
                self::MESSAGES['recommendation'],
                'Clear route cache with "php artisan route:clear" and reload the service provider.'
            ));
        }
    }

    /**
     * Check search engine configuration.
     */
    private function checkSearchEngine(): void
    {
        $searchEngine = Config::get('indexnow.search_engine', 'microsoft_bing');
        $validEngines = ['indexnow', 'microsoft_bing', 'naver', 'seznam', 'yandex'];

        $isValid = in_array($searchEngine, $validEngines);

        $this->line(sprintf(self::MESSAGES['search_engine'], $searchEngine));

        if (!$isValid) {
            $this->line(sprintf(
                self::MESSAGES['recommendation'],
                'Configure a valid search engine in config/indexnow.php. Valid options: ' . implode(', ', $validEngines)
            ));
        }
    }

    /**
     * Check submissions status.
     */
    private function checkSubmissionsStatus(): void
    {
        $submissionsEnabled = Config::get('indexnow.enable_submissions', false);

        $statusColor = $submissionsEnabled ? 'green' : 'yellow';
        $statusText = $submissionsEnabled ? 'Yes' : 'No';

        $this->line(sprintf(self::MESSAGES['submissions_status'], $statusColor, $statusText));

        if (!$submissionsEnabled) {
            $this->line(sprintf(
                self::MESSAGES['recommendation'],
                'Set INDEXNOW_ENABLE_SUBMISSIONS=true in your .env file to enable URL submissions.'
            ));
        }
    }

    /**
     * Check logging configuration.
     */
    private function checkLoggingStatus(): void
    {
        $loggingEnabled = Config::get('indexnow.enable_logging', true);

        $statusColor = $loggingEnabled ? 'green' : 'yellow';
        $statusText = $loggingEnabled ? 'Yes' : 'No';

        $this->line(sprintf(self::MESSAGES['logging_status'], $statusColor, $statusText));

        // Check log file if logging is enabled
        if ($loggingEnabled) {
            $logPath = LogManager::getLogFilePath();
            $logFileExists = File::exists($logPath);
            $logFileStatus = $logFileExists ? 'Found' : 'Not created yet';
            $logFileColor = $logFileExists ? 'green' : 'yellow';

            $this->line(sprintf(self::MESSAGES['log_path'], $logFileColor, $logFileStatus));
        }
    }

    /**
     * Check spam protection status.
     */
    private function checkSpamProtectionStatus(): void
    {
        // Check if spam detection is enabled
        $spamDetectionEnabled = Config::get('indexnow.enable_spam_detection', true);
        $statusColor = $spamDetectionEnabled ? 'green' : 'yellow';
        $statusText = $spamDetectionEnabled ? 'Enabled' : 'Disabled';

        $this->line(sprintf(self::MESSAGES['spam_detection'], $statusColor, $statusText));

        // Check if there's an active blocking
        if (class_exists('\Ymigval\LaravelIndexnow\PreventSpam')) {
            $remainingBlockTime = \Ymigval\LaravelIndexnow\PreventSpam::getRemainingBlockTime();

            if ($remainingBlockTime > 0) {
                $hours = floor($remainingBlockTime / 3600);
                $minutes = floor(($remainingBlockTime % 3600) / 60);
                $blockTimeText = sprintf('%d hours, %d minutes', $hours, $minutes);

                $this->line(sprintf(
                    self::MESSAGES['spam_blocking_status'],
                    'red',
                    "Submissions currently blocked for $blockTimeText"
                ));

                $this->line(sprintf(
                    self::MESSAGES['recommendation'],
                    'Wait for the block period to expire or run "php artisan indexnow:reset-block" to clear it.'
                ));
            } else {
                $this->line(sprintf(
                    self::MESSAGES['spam_blocking_status'],
                    'green',
                    "No active blocks, submissions allowed"
                ));
            }

            // Show blocking duration from config
            $blockingHours = Config::get('indexnow.spam_blocking_hours', 24);
            $this->line("Block duration when triggered: <options=bold>$blockingHours hours</>");
        }
    }

    /**
     * Display summary and final recommendations.
     */
    private function displaySummary(): void
    {
        $this->newLine();
        $this->line(self::MESSAGES['separator']);

        try {
            // Check if all critical components are correctly configured
            $apiKey = IndexNowApiKeyManager::getKey();
            $submissionsEnabled = Config::get('indexnow.enable_submissions', false);
            $configPath = config_path('indexnow.php');
            $configExists = File::exists($configPath);

            // Check for active blocks
            $isBlocked = false;
            if (class_exists('\Ymigval\LaravelIndexnow\PreventSpam')) {
                $isBlocked = \Ymigval\LaravelIndexnow\PreventSpam::getRemainingBlockTime() > 0;
            }

            if ($apiKey && $submissionsEnabled && $configExists && !$isBlocked) {
                $this->line(sprintf(
                    self::MESSAGES['success'],
                    'IndexNow package is properly configured and ready to submit URLs.'
                ));
            } else if ($isBlocked) {
                $this->line(sprintf(
                    self::MESSAGES['recommendation'],
                    'Submissions are temporarily blocked due to rate limiting. Check spam protection status above.'
                ));
            } else {
                $this->line(sprintf(
                    self::MESSAGES['recommendation'],
                    'Address the issues above to fully configure IndexNow package.'
                ));
            }
        } catch (Exception $e) {
            $this->line(sprintf(
                self::MESSAGES['recommendation'],
                'Fix the API key configuration issues before using IndexNow package.'
            ));
        }

        $this->newLine();
    }
}

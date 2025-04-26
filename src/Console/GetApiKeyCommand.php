<?php

namespace Ymigval\LaravelIndexnow\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Ymigval\LaravelIndexnow\Exceptions\InvalidKeyException;
use Ymigval\LaravelIndexnow\Exceptions\KeyFileDoesNotExistException;
use Ymigval\LaravelIndexnow\IndexNowApiKeyManager;

class GetApiKeyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indexnow:apikey 
                          {--source : Show the source of the API key}
                          {--verify : Verify if the key is properly configured}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve and verify IndexNow API Key';

    /**
     * Message templates.
     */
    private const MESSAGES = [
        'key' => 'API Key: <options=bold>%s</>',
        'source_config' => 'Source: Configuration file (config/indexnow.php)',
        'source_file' => '%s/%s.txt',  // Changed to use the public URL format
        'verification_url' => 'Verification URL: <options=bold>%s</>',
        'verification_success' => 'Key verification: <fg=green>Success</>',
        'verification_fail' => 'Key verification: <fg=red>Failed</> - %s',
        'error' => '<fg=red>Error:</> %s',
        'config_enabled' => 'Submissions enabled: <fg=green>Yes</>',
        'config_disabled' => 'Submissions enabled: <fg=red>No</>'
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            // Get the API key
            $apiKey = IndexNowApiKeyManager::getKey();

            // Display the API key
            $this->line(sprintf(self::MESSAGES['key'], $apiKey));

            // Show source if requested
            if ($this->option('source')) {
                $this->showKeySource($apiKey);
            }

            // Show configuration status
            $this->showConfigStatus();

            // Show verification URL
            $verificationUrl = $this->getVerificationUrl($apiKey);
            $this->line(sprintf(self::MESSAGES['verification_url'], $verificationUrl));

            // Verify key if requested
            if ($this->option('verify')) {
                $this->verifyKey($verificationUrl);
            }

            return self::SUCCESS;
        } catch (InvalidKeyException $e) {
            $this->error(sprintf(self::MESSAGES['error'], 'Invalid API key format: ' . $e->getMessage()));
            return self::FAILURE;
        } catch (KeyFileDoesNotExistException $e) {
            $this->error(sprintf(self::MESSAGES['error'], 'API key file not found: ' . $e->getMessage()));
            return self::FAILURE;
        } catch (Exception $e) {
            $this->error(sprintf(self::MESSAGES['error'], $e->getMessage()));
            return self::FAILURE;
        }
    }

    /**
     * Show the source of the API key.
     */
    private function showKeySource(string $apiKey): void
    {
        $configKey = Config::get('indexnow.indexnow_api_key');

        if ($configKey === $apiKey) {
            $this->line(self::MESSAGES['source_config']);
        } else {
            $this->line(sprintf(
                self::MESSAGES['source_file'],
                rtrim(config('app.url'), '/'),
                $apiKey
            ));
        }
    }

    /**
     * Show IndexNow configuration status.
     */
    private function showConfigStatus(): void
    {
        $submissionsEnabled = Config::get('indexnow.enable_submissions', false);
        $message = $submissionsEnabled
            ? self::MESSAGES['config_enabled']
            : self::MESSAGES['config_disabled'];

        $this->line($message);
    }

    /**
     * Get the verification URL for the API key.
     */
    private function getVerificationUrl(string $apiKey): string
    {
        return sprintf(
            '%s/%s.txt',
            rtrim(config('app.url'), '/'),
            $apiKey
        );
    }

    /**
     * Verify the API key by making a request to the verification URL.
     */
    private function verifyKey(string $url): void
    {
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $this->line(self::MESSAGES['verification_success']);
            } else {
                $this->line(sprintf(
                    self::MESSAGES['verification_fail'],
                    "HTTP status code: $httpCode"
                ));
            }
        } catch (Exception $e) {
            $this->line(sprintf(
                self::MESSAGES['verification_fail'],
                $e->getMessage()
            ));
        }
    }
}
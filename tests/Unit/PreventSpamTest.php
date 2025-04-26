<?php

namespace Ymigval\LaravelIndexnow\Tests\Unit;

use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Ymigval\LaravelIndexnow\PreventSpam;
use Ymigval\LaravelIndexnow\Tests\TestCase;

class PreventSpamTest extends TestCase
{
    protected $blockingFilePath;

    protected function setUp(): void
    {
        parent::setUp();

        // Make sure the directory exists
        Storage::makeDirectory('app/indexnow');

        // Set the blocking file path reflecting the getBlockingFilePath method
        $this->blockingFilePath = storage_path('app/indexnow/spam_blocking.json');

        // Remove the lock file if it exists
        if (File::exists($this->blockingFilePath)) {
            File::delete($this->blockingFilePath);
        }

        // Enable spam detection for tests
        Config::set('indexnow.enable_spam_detection', true);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->blockingFilePath)) {
            File::delete($this->blockingFilePath);
        }

        parent::tearDown();
    }

    #[Test] public function it_allows_requests_when_no_blocking_file_exists()
    {
        // Act
        $result = PreventSpam::isAllowed();

        // Assert
        $this->assertTrue($result);
    }

    #[Test] public function it_detects_potential_spam_from_429_response()
    {
        // Arrange
        $response = Mockery::mock(Response::class);
        $response->shouldReceive('status')->andReturn(429);
        $response->shouldReceive('header')->with('Retry-After')->andReturn(null);

        // Act
        PreventSpam::detectPotentialSpam($response, 'https://example.com/test');

        // Assert
        $this->assertFileExists($this->blockingFilePath);
        $blockingData = json_decode(File::get($this->blockingFilePath), true);
        $this->assertArrayHasKey('expires_at', $blockingData);
    }

    #[Test] public function it_blocks_requests_when_blocking_file_is_valid()
    {
        // Arrange - Create a blocking file that expires in the future
        $blockingData = [
            'blocked_at' => Carbon::now()->toIso8601String(),
            'expires_at' => Carbon::now()->addHours(1)->toIso8601String(),
            'reason' => 'Test blocking',
            'duration_hours' => 1
        ];

        File::put($this->blockingFilePath, json_encode($blockingData));

        // Act
        $result = PreventSpam::isAllowed();

        // Assert
        $this->assertFalse($result);
    }

    #[Test] public function it_allows_requests_when_blocking_has_expired()
    {
        // Arrange - Create a blocking file that has already expired
        $blockingData = [
            'blocked_at' => Carbon::now()->subHours(2)->toIso8601String(),
            'expires_at' => Carbon::now()->subHours(1)->toIso8601String(),
            'reason' => 'Test blocking',
            'duration_hours' => 1
        ];

        File::put($this->blockingFilePath, json_encode($blockingData));

        // Act
        $result = PreventSpam::isAllowed();

        // Assert
        $this->assertTrue($result);
        $this->assertFileDoesNotExist($this->blockingFilePath, 'Blocking file should be deleted after expiry');
    }

    #[Test] public function it_respects_config_setting_to_disable_spam_detection()
    {
        // Arrange
        Config::set('indexnow.enable_spam_detection', false);

        // Create a valid blocking file
        $blockingData = [
            'blocked_at' => Carbon::now()->toIso8601String(),
            'expires_at' => Carbon::now()->addHours(1)->toIso8601String(),
            'reason' => 'Test blocking',
            'duration_hours' => 1
        ];

        File::put($this->blockingFilePath, json_encode($blockingData));

        // Act
        $result = PreventSpam::isAllowed();

        // Assert
        $this->assertTrue($result, 'Should allow requests when spam detection is disabled regardless of blocking file');
    }

    #[Test] public function it_can_reset_blocking()
    {
        // Arrange - Create a blocking file
        $blockingData = [
            'blocked_at' => Carbon::now()->toIso8601String(),
            'expires_at' => Carbon::now()->addHours(1)->toIso8601String(),
            'reason' => 'Test blocking',
            'duration_hours' => 1
        ];

        File::put($this->blockingFilePath, json_encode($blockingData));

        // Act
        $result = PreventSpam::resetBlocking();

        // Assert
        $this->assertTrue($result);
        $this->assertFileDoesNotExist($this->blockingFilePath);
        $this->assertTrue(PreventSpam::isAllowed(), 'Should allow requests after reset');
    }

    #[Test] public function it_returns_correct_remaining_block_time()
    {
        // Arrange - Create a blocking file with 1 hour expiry
        $expiresAt = Carbon::now()->addHour();
        $blockingData = [
            'blocked_at' => Carbon::now()->toIso8601String(),
            'expires_at' => $expiresAt->toIso8601String(),
            'reason' => 'Test blocking',
            'duration_hours' => 1
        ];

        File::put($this->blockingFilePath, json_encode($blockingData));

        // Act
        $remainingTime = PreventSpam::getRemainingBlockTime();

        // Assert
        $expectedTimeRemaining = Carbon::now()->diffInSeconds($expiresAt);

        // Allow for a small deviation (5 seconds) due to test execution time
        $this->assertGreaterThan($expectedTimeRemaining - 5, $remainingTime);
        $this->assertLessThan($expectedTimeRemaining + 5, $remainingTime);
    }
}

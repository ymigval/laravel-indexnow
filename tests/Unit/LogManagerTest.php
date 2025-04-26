<?php

namespace Ymigval\LaravelIndexnow\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Ymigval\LaravelIndexnow\LogManager;
use Ymigval\LaravelIndexnow\Tests\TestCase;

class LogManagerTest extends TestCase
{
    protected $originalLogPath;
    protected $testLogPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Save the original log path
        $this->originalLogPath = LogManager::getLogFilePath();

        // Create a temporary log path for testing
        $this->testLogPath = storage_path('logs/indexnow_test.log');

        // Set private static property using reflection
        $reflectionClass = new \ReflectionClass(LogManager::class);
        $property = $reflectionClass->getProperty('logFilePath');
        $property->setAccessible(true);
        $property->setValue(null, $this->testLogPath);

        // Remove the test log file if it exists
        if (File::exists($this->testLogPath)) {
            File::delete($this->testLogPath);
        }

        // Make sure logging is enabled
        Config::set('indexnow.enable_logging', true);
    }

    protected function tearDown(): void
    {
        // Delete the test log file if it exists
        if (File::exists($this->testLogPath)) {
            File::delete($this->testLogPath);
        }

        // Restore the original log path
        $reflectionClass = new \ReflectionClass(LogManager::class);
        $property = $reflectionClass->getProperty('logFilePath');
        $property->setAccessible(true);
        $property->setValue(null, $this->originalLogPath);

        parent::tearDown();
    }

    #[Test] public function it_logs_string_messages()
    {
        // Arrange
        $message = 'Test log message';

        // Act
        LogManager::addMessage($message);

        // Assert
        $this->assertFileExists($this->testLogPath);
        $logContent = File::get($this->testLogPath);
        $this->assertStringContainsString('"Test log message"', $logContent);
    }

    #[Test] public function it_logs_array_messages()
    {
        // Arrange
        $message = ['key' => 'value', 'test' => 123];

        // Act
        LogManager::addMessage($message);

        // Assert
        $this->assertFileExists($this->testLogPath);
        $logContent = File::get($this->testLogPath);
        $this->assertStringContainsString('"key": "value"', $logContent);
        $this->assertStringContainsString('"test": 123', $logContent);
    }

    #[Test] public function it_does_not_log_when_logging_is_disabled()
    {
        // Arrange
        Config::set('indexnow.enable_logging', false);
        $message = 'This should not be logged';

        // Act
        LogManager::addMessage($message);

        // Assert
        if (File::exists($this->testLogPath)) {
            $logContent = File::get($this->testLogPath);
            $this->assertStringNotContainsString($message, $logContent);
        } else {
            // Si el archivo no existe, es también una confirmación de que no se registró
            $this->assertTrue(true);
        }
    }

    #[Test] public function it_clears_logs()
    {
        // Arrange - First add a log entry
        LogManager::addMessage('Test message before clearing');
        $this->assertFileExists($this->testLogPath);

        // Act
        $result = LogManager::clearLogs();

        // Assert
        $this->assertTrue($result);
        $this->assertFileDoesNotExist($this->testLogPath);
    }

    #[Test] public function it_shows_logs()
    {
        // Arrange - Add a known log entry
        $testMessage = 'Test message for showing';
        LogManager::addMessage($testMessage);

        // Act
        $logContent = LogManager::showLogs();

        // Assert
        $this->assertStringContainsString($testMessage, $logContent);
    }

    #[Test] public function it_returns_empty_string_when_showing_non_existent_logs()
    {
        // Arrange - Ensure log file does not exist
        if (File::exists($this->testLogPath)) {
            File::delete($this->testLogPath);
        }

        // Act
        $logContent = LogManager::showLogs();

        // Assert
        $this->assertEquals('', $logContent);
    }

    #[Test] public function it_limits_log_file_size()
    {
        // Arrange
        $reflectionClass = new \ReflectionClass(LogManager::class);
        $maxSizeConstant = $reflectionClass->getConstant('MAX_LOG_FILE_SIZE');

        // Crear un log grande que supere el tamaño máximo
        $largeMessage = str_repeat('a', $maxSizeConstant);

        // Act
        LogManager::addMessage($largeMessage);
        LogManager::addMessage('This should create a new log file');

        // Assert
        $this->assertFileExists($this->testLogPath);
        $logContent = File::get($this->testLogPath);

        // Verificar que el log se reinició (solo contiene el segundo mensaje)
        $this->assertStringNotContainsString($largeMessage, $logContent);
        $this->assertStringContainsString('This should create a new log file', $logContent);
    }
}

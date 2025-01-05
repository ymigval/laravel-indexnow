<?php

namespace Ymigval\LaravelIndexnow\Tests\Feature;

use Ymigval\LaravelIndexnow\LogManager;
use Ymigval\LaravelIndexnow\Tests\TestCase;

class LogManagerTest extends TestCase
{
    public function test_add_log()
    {
        LogManager::clearLogs();
        LogManager::addMessage('Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
        $this->assertFileExists('storage/log.txt');
    }

    public function test_show_logs()
    {
        LogManager::clearLogs();
        LogManager::addMessage('Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
        $this->assertFileExists('storage/log.txt');
        $this->assertStringContainsString('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', LogManager::showLogs());
    }
}

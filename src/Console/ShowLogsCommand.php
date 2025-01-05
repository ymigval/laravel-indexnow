<?php

namespace Ymigval\LaravelIndexnow\Console;

use Illuminate\Console\Command;
use Ymigval\LaravelIndexnow\LogManager;

class ShowLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'indexnow:logs';

    /**
     * The console command description.
     */
    protected $description = 'Display IndexNow logs';

    /**
     * Message template for displaying logs.
     */
    private const LOGS_OUTPUT_MESSAGE = "IndexNow Logs:\n%s";

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->executeCommand();
    }

    /**
     * Actual method to execute the command logic.
     */
    private function executeCommand(): int
    {
        $logs = LogManager::showLogs();
        $this->outputLogs($logs);

        return self::SUCCESS;
    }

    /**
     * Output logs to the console in a formatted way.
     *
     * @param string $logs
     * @return void
     */
    private function outputLogs(string $logs): void
    {
        $this->line(sprintf(self::LOGS_OUTPUT_MESSAGE, $logs));
    }
}
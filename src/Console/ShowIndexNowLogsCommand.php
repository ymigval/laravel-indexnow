<?php

namespace Ymigval\LaravelIndexnow\Console;

use Illuminate\Console\Command;
use Ymigval\LaravelIndexnow\LogManager;

class ShowIndexNowLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "indexnow:logs";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Display IndexNow logs";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $logs = LogManager::showLogs();
        $this->line($logs);
        return self::SUCCESS;
    }
}

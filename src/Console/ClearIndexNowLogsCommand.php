<?php

namespace Ymigval\LaravelIndexnow\Console;

use Illuminate\Console\Command;
use Ymigval\LaravelIndexnow\LogManager;

class ClearIndexNowLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "indexnow:clear-logs";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Clear IndexNow logs";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        LogManager::deleteLogFile();
        $this->info('IndexNow logs have been successfully cleared.');
        return self::SUCCESS;
    }
}

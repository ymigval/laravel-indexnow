<?php

namespace Ymigval\LaravelIndexnow\Console;

use Illuminate\Console\Command;
use Ymigval\LaravelIndexnow\LogManager;

class ClearLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indexnow:clear-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear IndexNow logs';

    /**
     * Success message for clearing logs.
     *
     * @var string
     */
    private const SUCCESS_MESSAGE = 'IndexNow logs have been successfully cleared.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        LogManager::clearLogs();
        $this->info(self::SUCCESS_MESSAGE);

        return self::SUCCESS;
    }

}

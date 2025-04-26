<?php

namespace Ymigval\LaravelIndexnow\Console;

use Exception;
use Illuminate\Console\Command;
use Ymigval\LaravelIndexnow\LogManager;
use Ymigval\LaravelIndexnow\PreventSpam;

class ResetBlockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indexnow:reset-block';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the IndexNow spam protection blocking state';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking IndexNow spam protection blocking status...');

        try {
            $remainingTime = PreventSpam::getRemainingBlockTime();

            if ($remainingTime > 0) {
                $hours = floor($remainingTime / 3600);
                $minutes = floor(($remainingTime % 3600) / 60);

                $this->info("Currently blocked for {$hours} hours and {$minutes} minutes remaining.");

                if ($this->confirm('Do you want to reset the block and allow submissions again?', true)) {
                    $success = PreventSpam::resetBlocking();

                    if ($success) {
                        $this->info('Block has been successfully reset. Submissions are now allowed.');
                        LogManager::addMessage('Spam protection block manually reset via command line');
                        return self::SUCCESS;
                    } else {
                        $this->error('Failed to reset blocking state.');
                        return self::FAILURE;
                    }
                } else {
                    $this->info('Operation cancelled. Block remains active.');
                    return self::SUCCESS;
                }
            } else {
                $this->info('No active blocking found. Submissions are already allowed.');
                return self::SUCCESS;
            }
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}

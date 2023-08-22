<?php

namespace Ymigval\LaravelIndexnow\Console;

use Illuminate\Console\Command;
use Ymigval\LaravelIndexnow\KeyIndexNow;

class KeyGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "indexnow:newkey";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Create an IndexNow key file.";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $key = KeyIndexNow::newkey();

        $this->info('Key file generated and created successfully!');
        $this->newLine();
        $this->line('Key:' . $key);

        return self::SUCCESS;
    }
}

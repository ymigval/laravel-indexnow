<?php

namespace Ymigval\LaravelIndexnow\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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
    protected $description = "Crear una nueva clave de Index Now.";

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
        $key = $this->newkey();

        $this->info('Key file generated and created successfully!');
        $this->line('Key:' . $key);

        return 1;
    }

    /**
     * Generate and create a new key file.
     *
     * @return string
     */
    private function newkey(): string
    {
        $key = Str::of(Str::uuid())->replace("-", "");

        File::put(__DIR__ . "/../../storage/key.txt", $key);

        return $key;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OutboxDispatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:outbox-dispatch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Dispatching outbox…');

        return self::SUCCESS;
    }
}

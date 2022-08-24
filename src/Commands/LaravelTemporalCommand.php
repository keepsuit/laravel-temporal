<?php

namespace Keepsuit\LaravelTemporal\Commands;

use Illuminate\Console\Command;

class LaravelTemporalCommand extends Command
{
    public $signature = 'laravel-temporal';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}

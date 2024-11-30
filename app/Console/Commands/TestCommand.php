<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class TestCommand extends Command
{
    protected $signature = 'tc';

    public function handle(): void
    {
        User::factory(10)
            ->create();

        $this->info(Cache::has('test') ? 1 : 0);
        $this->info(Cache::get('test'));

        Cache::set('test', now()->toDateTimeString(), now()->addMinutes(5));
    }
}

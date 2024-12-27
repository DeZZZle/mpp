<?php

namespace App\Console\Commands;

use App\Jobs\ClimateImportJob;
use Illuminate\Console\Command;

class ImportClimateCommand extends Command
{
    protected $signature = 'import:climate';

    public function handle(): void
    {
        $deviceId = '424c238d-e752-4f0a-97ea-f05f0b211140';

        ClimateImportJob::dispatch(
            $deviceId
        );
    }
}

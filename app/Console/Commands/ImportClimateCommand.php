<?php

namespace App\Console\Commands;

use App\Jobs\ClimateImportJob;
use Illuminate\Console\Command;

class ImportClimateCommand extends Command
{
    protected $signature = 'import:climate';

    public function handle(): void
    {
        $deviceId = 'ba81fcc7-dd2d-4b09-b591-0c7653b5960a';

        ClimateImportJob::dispatch(
            $deviceId
        );
    }
}

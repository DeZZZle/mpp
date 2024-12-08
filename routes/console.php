<?php

use App\Console\Commands\ImportClimateCommand;

Schedule::command(ImportClimateCommand::class)
    ->everyFifteenMinutes();

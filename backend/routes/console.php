<?php

use Illuminate\Support\Facades\Schedule;

// Daily lost-customer detection (Laravel 11+ schedules live here, not a Kernel).
Schedule::command('customers:detect-lost')
    ->dailyAt('01:00')
    ->onOneServer()
    ->withoutOverlapping();

<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command(
    'sales:cancel-expired-pending'
)->everyMinute();

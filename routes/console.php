<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('tasks:process-scheduled')->everyMinute();

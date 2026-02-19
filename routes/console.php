<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('eden:health-check')->daily();
Schedule::command('eden:cleanup')->daily();
Schedule::command('eden:remind-updates')->weekly();
Schedule::command('eden:newsletter')->weeklyOn(1, '8:00');

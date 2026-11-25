<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('ventas:automatizaciones')->everyMinute()->withoutOverlapping()->name('automatizaciones');
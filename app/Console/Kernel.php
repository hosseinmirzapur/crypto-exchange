<?php

namespace App\Console;

use App\Console\Commands\ApiRealTimePriceCommand;
use App\Console\Commands\RealTimePriceCommand;
use App\Console\Commands\UpdateBalancesCommand;
use App\Console\Commands\WeeklyPriceCommand;
use App\Jobs\AllCoinsJob;
use App\Jobs\ExchangeInfoJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ApiRealTimePriceCommand::class,
        UpdateBalancesCommand::class,
        WeeklyPriceCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

//        $schedule->job(new AllCoinsJob())
//            ->dailyAt('1:00');

        $schedule->command('updateAllCoins:run')
            ->dailyAt('2:00');

        $schedule->command('weeklyPrice:run')
            ->dailyAt('00:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

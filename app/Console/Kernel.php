<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('empresas_logada:cron')->everyMinute();
        $schedule->command('dfe:cron')->everyTwoHours();
        $schedule->command('cash-back:cron')->dailyAt('08:00');
        // $schedule->command('cash-back:cron')->everyMinute();
        $schedule->command('woocommerce:sync')->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    protected $commands = [
        Commands\WooCommerceSync::class,
        Commands\ViewPlug4MarketLogs::class,
        Commands\TestPlug4MarketProducts::class,
    ];
}

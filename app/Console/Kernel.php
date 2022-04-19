<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        // 参考手册 新加
        \App\Console\Commands\WorkNotice::class,
    ];

    /**
     * Define the application's command schedule.
     * 定义应用的命令调度
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        // 新加 每分钟执行一次
        $schedule->command('notice:insert')->everyMinute();
        //$schedule->command('reset:workRegion')->everyMinute();
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

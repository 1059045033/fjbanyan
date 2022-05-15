<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetWrokRegion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:workRegion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重置工作区';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::transaction(function () {
            DB::table('users')->where('role',10)->update([
                'work_region_id'=>null,
                'is_online'=>0
            ]);
        });
        return 0;
    }
}

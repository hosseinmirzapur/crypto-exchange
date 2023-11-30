<?php

namespace App\Console\Commands;

use App\Jobs\WeeklyPriceJob;
use App\Models\Coin;
use Illuminate\Console\Command;

class WeeklyPriceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weeklyPrice:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update weekly price ';

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
        $coins = Coin::where('status', 'ACTIVATED')
            ->where('code', '!=', 'TOMAN')
            ->get();


        foreach ($coins as $key => $coin) {

            WeeklyPriceJob::dispatch($coin)
                ->delay(now()->addSeconds($key * 20));

        }
        return 0;
    }
}

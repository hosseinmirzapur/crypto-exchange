<?php

namespace App\Console\Commands;

use App\Classes\Binance;
use App\Events\UpdateBalancesEvent;
use Illuminate\Console\Command;

class UpdateBalancesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateBalances:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'run socket for updating admin balances';

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
        $balanceUpdate = function($api, $balances) {
            event(new UpdateBalancesEvent($balances));
             };

         $orderUpdate = function($api, $report) {
         };


        (new Binance(true))
            ->binance
            ->userData($balanceUpdate, $orderUpdate);

    }
}

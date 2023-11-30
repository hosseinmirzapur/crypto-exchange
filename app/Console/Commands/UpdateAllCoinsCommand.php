<?php

namespace App\Console\Commands;

use App\Models\Config;
use App\Models\CryptoNetwork;
use Illuminate\Console\Command;

class UpdateAllCoinsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateAllCoins:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update all coins from exchange provider';


    public function handle()
    {
        CryptoNetwork::updateConfig();
    }
}

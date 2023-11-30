<?php

namespace App\Http\Controllers;

use App\Models\Config;
use function App\Helpers\successResponse;

class BinanceConfigController extends Controller
{

    public function store()
    {
            Config::updateBinanceConfig();
        return successResponse();
    }
}

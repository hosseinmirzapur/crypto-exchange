<?php

namespace App\Http\Controllers;

use App\Classes\Binance;
use function App\Helpers\is_product_env;
use function App\Helpers\successResponse;

class DepositAddressController extends Controller
{
    // todo must auth for both
    public function show($coin, $network)
    {
        $address = (new Binance())
            ->depositAddress($coin, $network);

        return successResponse(
            $address
        );

    }

}

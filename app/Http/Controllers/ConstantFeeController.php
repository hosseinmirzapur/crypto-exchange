<?php

namespace App\Http\Controllers;

use App\Models\Config;
use App\Models\Option;
use Illuminate\Http\Request;
use function App\Helpers\successResponse;

class ConstantFeeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['fee' => 'required']);

        $config = Config::updateOrCreate(
            [
                'type' => 'MAIN',
                'key' => 'constant_fee'
            ],
            [
                'type' => 'MAIN',
                'key' => 'constant_fee',
                'value' => $request->fee
            ]
        );

        return successResponse(
            $config
        );
    }

    public function show()
    {
        return successResponse(
            Config::typeOf('MAIN')
            ->where('key', 'constant_fee')
            ->first()
        );
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;

class AdminAccountController extends Controller
{
    public function index()
    {
        return \App\Helpers\successResponse(
            Config::where('type', 'ADMIN_ACCOUNT')
                ->pluck('value', 'key')
        );
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'account_owner' => 'required',
            'sheba' => 'required',
            'account_number' => 'required',
            'card_number' => 'required',
        ]);



        foreach ($attributes as $key => $value) {

            Config::updateOrCreate(
                [
                    'type' => 'ADMIN_ACCOUNT',
                    'key' => strtoupper($key)
                ],
                [
                    'type' => 'ADMIN_ACCOUNT',
                    'key' => strtoupper($key),
                    'value' => $value
                ]
            );
        }
    }
}

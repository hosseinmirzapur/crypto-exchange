<?php

namespace App\Http\Controllers;

use App\Http\Resources\CreditResource;
use App\Models\Coin;
use App\Models\Credit;
use Illuminate\Http\Request;
use function App\Helpers\current_user;

class CreditController extends Controller
{
    public function index()
    {
        $userCredits = current_user()->credits();

        return \App\Helpers\successResponse(
            Coin::query()
                ->selectRaw('coins.id as coin_id, coins.code, coins.name, coins.label, userCredits.credit, userCredits.blocked, userCredits.created_at, userCredits.updated_at')
                ->leftJoinSub($userCredits,'userCredits', function ($join) {
                    $join->on('userCredits.coin_id','=','coins.id');
                } )
                ->orderBy('coins.id')
                ->get()
        );
    }
}

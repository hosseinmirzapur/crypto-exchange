<?php

namespace App\Http\Controllers;

use function App\Helpers\current_user;

class ReferralCodeController extends Controller
{
    public function index()
    {
        if (!current_user()->referral()->exists()) {
            current_user()->addReferral();
        }

        $referrals_array = current_user()->referral->toArray();
        $referrals_array = array_merge(
            $referrals_array,
            ['count' => current_user()->referringUsers()
                ->count()],
            ['commission' => current_user()
                ->transactions()
                ->where('type', 'REFERRAL')
                ->sum('amount')]
        );


        return \App\Helpers\successResponse(
            $referrals_array
        );
    }
}

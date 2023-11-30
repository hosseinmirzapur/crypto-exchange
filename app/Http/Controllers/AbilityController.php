<?php

namespace App\Http\Controllers;

use App\Models\Ability;

class AbilityController extends Controller
{
    public function index()
    {
        $array = ['LIST', 'DETAILS', 'CREATE', 'UPDATE', 'DELETE'];
        $list = [];
        $a = Ability::all()->sortBy('name');
        $a->map(function ($i) use (&$list, $array) {
            $s = explode('_', $i->name, 2);
            if (!isset($list[$s[1]])) {
                foreach ($array as $v) {
                    $list[$s[1]][$v] = false;
                }
            }
            $list[$s[1]][$s[0]] = true;
        });

        return \App\Helpers\successResponse(
            $list
        );
    }
}

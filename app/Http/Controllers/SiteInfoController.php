<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\Page;
use Illuminate\Http\Request;

class SiteInfoController extends Controller
{

    public function store(Request $request)
    {
        $options = [
            'ADDRESS' => [
                'address',
                'phone',
                'email',
            ],
            'SOCIAL' => [
                'telegram',
                'twitter',
                'instagram',
                'linkedin'
            ],
            'APP' => [
                'ios',
                'android'
            ]
        ];

        foreach ($options as $key => $option) {

            foreach ($option as $item) {

                Option::updateOrCreate(
                    ['option_key' => $item],
                    [
                        'option_key' => $item,
                        'section' => $key,
                        'option_value' => $request->$item ?? ""
                    ]);
            }
        }

        Page::updateOrCreate(
            ['name' => 'POLICY'],
            [
                'name' => 'POLICY',
                'content' => $request->policy ?? ""
            ]
        );
    }

    public function show()
    {
        $options = Option::whereIn('section', ['ADDRESS', 'SOCIAL', 'APP'])
            ->get();

        $rulePage = Page::whereName('POLICY')
            ->first();

        return \App\Helpers\successResponse(
            [
                'options' => $options,
                'page' => $rulePage
            ]
        );
    }
}

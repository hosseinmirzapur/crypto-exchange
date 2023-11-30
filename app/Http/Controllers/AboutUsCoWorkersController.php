<?php

namespace App\Http\Controllers;

use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AboutUsCoWorkersController extends Controller
{
    public function store(Request $request)
    {
        $attributes = $request->validate(
            [
                "image" => "required|image",
                'name' => 'required',
                'role' => 'required',
            ]
        );

        $path = $attributes['image']->store('AboutUs');

        $array = [
            'image' => $path,
            'name' => $attributes['name'],
            'role' => $attributes['role'],
        ];
        $option = Option::create(
            [
                'section' => 'AboutUs',
                'option_key' => '',
                'key_label' => '',
                'option_value' => json_encode($array)
            ]
        );

        return \App\Helpers\successResponse(
            $option,
            201,
        );
    }

    public function index()
    {
        $options = Option::where('section', 'AboutUs')
            ->get();



        $coWorkers = $options->map(function ($option) {
            $a = json_decode($option->option_value, true);
            $a['id'] = $option->id;
            $a['image'] = Storage::url($a['image']);
            return $a;
        });

        return \App\Helpers\successResponse(
            $coWorkers,
            200,
        );

    }

    public function destroy(Option $option)
    {
        $coWorker = $option->option_value;
        Storage::delete((json_decode($coWorker, true))['image']);
        $option->delete();
        return \App\Helpers\successResponse(
            '',
            204,
        );
    }

}

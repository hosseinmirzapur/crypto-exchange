<?php

namespace App\Http\Controllers;

use App\Models\Option;
use Illuminate\Http\Request;
use function App\Helpers\successResponse;

class OptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param null $section
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($section = null)
    {
        return successResponse(
            Option::when(
                isset($section),
                function ($query) use ($section) {
                    $query->where('section', $section);
                }
            )->get()
        );
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $attributes = $request->validate([
            'section' => ['required'],
            'option_key' => ['required'],
            'key_label' => ['required'],
            'option_value' => ['required', 'max:1000']
        ]);

        $option = Option::create($attributes);

        return successResponse(
            $option,
        );
    }

    /**
     * Display the specified resource.
     *
     * @param $section
     * @param $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($section, $key)
    {
        $option = Option::sectionKey($section, $key)
            ->findOrFail();
        return successResponse(
            $option,
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param $section
     * @param $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $section, $key)
    {
        $attributes = $request->validate([
            'section' => ['required'],
            'option_key' => ['required'],
            'key_label' => ['required'],
            'option_value' => ['required', 'max:1000']
        ]);
        $option = Option::sectionKey($section, $key)
            ->findOrFail();
        $option->update($attributes);
        return successResponse(
            $option,
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($section, $key)
    {
        $option = Option::sectionKey($section, $key)
            ->findOrFail();
        $option->delete();
        return successResponse(
            null,
            204,
        );
    }
}

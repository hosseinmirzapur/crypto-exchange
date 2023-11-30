<?php

namespace App\Http\Controllers;

use App\Models\Rank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use function App\Helpers\successResponse;

class RankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return successResponse(
            Rank::orderBy('criterion')
                ->get(),
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $attribute = $request->validate([
            'name' => ['required', Rule::unique('ranks', 'name')],
            'label' => ['required'],
            'criterion' => ['required', Rule::unique('ranks', 'criterion')],
        ]);
        $rank = Rank::create($attribute);
        return successResponse(
            $rank,
        );
    }

    /**
     * Display the specified resource.
     *
     * @param Rank $rank
     * @return Rank
     */
    public function show(Rank $rank)
    {
        return $rank;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Rank $rank
     * @return JsonResponse
     */
    public function update(Request $request, Rank $rank)
    {
        $attribute = $request->validate([
            'fee' => ['sometimes', 'min:0', 'numeric', 'max:1'],
            'criterion' => ['sometimes', 'numeric', 'min:0'],
        ]);
        $rank->update($attribute);
        return successResponse(
            $rank,
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Rank $rank
     * @return JsonResponse
     */
    public function destroy(Rank $rank)
    {
        return successResponse(
            $rank->delete(),
            204
        );
    }
}

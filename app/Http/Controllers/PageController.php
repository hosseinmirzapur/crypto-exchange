<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function App\Helpers\successResponse;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return successResponse(
            Page::filter(['name'])
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
            'name' => ['required'],
            'title' => ['sometimes', 'max:255'],
            'content' => 'required'
        ]);
        $page = Page::create($attribute);
        return successResponse(
            $page,
        );
    }

    /**
     * Display the specified resource.
     *
     * @param Page $page
     * @return Page
     */
    public function show(Page $page)
    {
        return \App\Helpers\successResponse(
            $page
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Page $page
     * @return JsonResponse
     */
    public function update(Request $request, Page $page)
    {
        $attribute = $request->validate([
            'name' => ['required'],
            'title' => ['sometimes', 'max:255'],
            'content' => 'required'
        ]);
        $page->update($attribute);
        return successResponse(
            $page,
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Page $page
     * @return JsonResponse
     */
    public function destroy(Page $page)
    {
        return successResponse(
            $page->delete(),
            \Symfony\Component\HttpFoundation\Response::HTTP_NO_CONTENT
        );
    }
}

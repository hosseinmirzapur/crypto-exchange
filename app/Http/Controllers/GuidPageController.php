<?php

namespace App\Http\Controllers;

use App\Models\GuidPage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use function App\Helpers\successResponse;

class GuidPageController extends Controller
{

    public function index()
    {
        return successResponse(
            GuidPage::all()
        );
    }

    public function show(GuidPage $guidPage)
    {
        return successResponse(
            $guidPage,
        );
    }

    public function store(Request $request)
    {
        $page = GuidPage::create($request->all());

        return successResponse(
            $page,
            201
        );
    }

    public function update(Request $request, GuidPage $guidPage)
    {
        $guidPage->update($request->all());

        return successResponse(
            $guidPage
        );
    }

    public function destroy(GuidPage $guidPage)
    {
        $guidPage->delete();
        return \App\Helpers\successResponse(
            $guidPage,
            Response::HTTP_NO_CONTENT
        );
    }

}

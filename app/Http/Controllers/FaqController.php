<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use function App\Helpers\successResponse;

class FaqController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'user:admin'])
            ->except('index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return \App\Helpers\successResponse(
            Faq::orderBy('priority')->get(),
        );
    }

    public function store(Request $request)
    {
        $count = Faq::count();
        $attributes = $request->validate([
            'question' => 'required',
            'answer' => 'required',
//            'priority' => ['gt:0', 'lte:' . ($count + 1)]
        ]);
        if (!$request->has('priority')) {
            $attributes['priority'] = $count + 1;
            Faq::where('priority', '>=', $attributes['priority'])->increment('priority');
        }
        $faq = Faq::create($attributes);
        return successResponse(
            $faq,
            201,
        );
    }

    public function show(Faq $faq)
    {
        return successResponse(
            $faq,
            200,
        );
    }

    public function update(Request $request, Faq $faq)
    {
//        $count = Faq::count();
        $attributes = $request->validate([
            'question' => 'required',
            'answer' => 'required',
//            'priority' => ['gt:0', 'lte:' . ($count)]
        ]);
//        if ($attributes['priority'] != $faq->priority) {
//            if ($attributes['priority'] > $faq->priority) {
//                Faq::where('priority', '>=', $faq->priority)
//                    ->where('priority', '<=', $attributes['priority'])
//                    ->decrement('priority');
//            } else {
//                Faq::where('priority', '<=', $faq->priority)
//                    ->where('priority', '>=', $attributes['priority'])
//                    ->increment('priority');
//            }
//        }
        $faq->update($attributes);
        return $faq;
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
//        Faq::where('priority', '>', $faq->priority)->decrement('priority');
        return successResponse(
            null,
            204,
        );
    }
}

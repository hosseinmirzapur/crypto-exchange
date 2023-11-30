<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use function App\Helpers\current_user;
use function App\Helpers\successResponse;

class DocumentController extends Controller
{

    public function index()
    {
        $documents = current_user()
            ->documents;

        return successResponse(
            $documents,
        );

    }


    public function store(Request $request)
    {
        $attributes = $request->validate([
            "document" => "required|image",
            "type" => ["required", Rule::in(["SELFIE", 'BANK_CARD', "NATIONAL_CARD"])]
        ]);

        $user = current_user();

        $path = $attributes['document']
            ->store('documents');

        $document = $user->documents()
            ->whereType($attributes['type'])
            ->first();

        if (isset($document)) {
            $old_image = $document->image;
            $document->update([
                'image' => $path,
                'status' => 'PENDING'
            ]);
            Storage::delete($old_image);
            return successResponse(
                $document,
                200,
                ['message' => trans('messages.IMAGE_UPLOADED')]
            );
        }
        $document = $user->documents()
            ->create([
                'type' => $attributes['type'],
                'image' => $path
            ]);
        return successResponse(
            $document,
            201,
            ['message' => trans('messages.IMAGE_UPLOADED')]
        );
    }

    public function update(Request $request, Document $document)
    {
        $attributes = $request->validate([
            'status' => ['required', Rule::in(Document::STATUS)]
        ]);
        $document
            ->update($attributes);

        return successResponse(
            [],
            200,
            ['message' => trans('messages.STATUS_UPDATED')]
        );
    }
}

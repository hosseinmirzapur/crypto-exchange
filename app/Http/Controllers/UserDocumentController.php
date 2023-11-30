<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use function App\Helpers\current_user;
use function App\Helpers\successResponse;

class UserDocumentController extends Controller
{


    public function index(User $user)
    {
        $documents = $user->documents;

        return successResponse(
            $documents,
        );

    }


    public function store(Request $request, User $user)
    {
        $attributes = $request->validate([
            "document" => "required|image",
            "type" => ["required", Rule::in(["SELFIE", 'BANK_CARD', "NATIONAL_CARD"])]
        ]);

        $path = $attributes['document']
            ->store('documents');

        $document = $user->documents()
            ->whereType($attributes['type'])
            ->first();

        if ($document) {
            $old_image = $document->image;
            $document->update([
                'image' => $path
            ]);
            Storage::delete($old_image);
            return successResponse(
                [],
                200,
                ['message' => trans('messages.IMAGE_UPLOADED')]
            );
        }
        $user->documents()
            ->create([
                'type' => $attributes['type'],
                'image' => $path
            ]);
        return successResponse(
            [],
            201,
            ['message' => trans('messages.IMAGE_UPLOADED')]
        );
    }



}

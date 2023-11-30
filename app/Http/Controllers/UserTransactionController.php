<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use function App\Helpers\current_user;
use function App\Helpers\successResponse;

class UserTransactionController extends Controller
{

    public function index(User $user)
    {
        $transactions = $user
            ->transaction()
            ->withFilter()
            ->paginate();

        return successResponse(
            $transactions,
        );
    }


}

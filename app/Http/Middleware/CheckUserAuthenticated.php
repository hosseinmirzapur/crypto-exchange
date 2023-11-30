<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Models\User;
use Cassandra\Exception\UnauthorizedException;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @param $user
     * @return mixed
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next, $user)
    {
        switch ($user) {
            case 'admin' :
                if (!$request->user() || !($request->user() instanceof Admin)) {
                    throw new AuthorizationException();
                }
                break;
            case 'user':
                if (!$request->user() || !($request->user() instanceof User)) {
                    throw new AuthorizationException();
                }
                break;
            default :
                throw new \Exception("WRONG", Response::HTTP_BAD_REQUEST);
        }
//        if (!$request->user() || !$request->user()->isAccepted()) {
//        }
        return $next($request);
    }
}

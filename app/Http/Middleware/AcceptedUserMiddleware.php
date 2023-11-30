<?php

namespace App\Http\Middleware;

use App\Exceptions\CustomException;
use App\Models\Admin;
use App\Models\User;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AcceptedUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()) {
            throw new AuthenticationException();
        }
        throw_if(!$request->user()->isAdmin() && $request->user()->status !== 'ACCEPTED',
            CustomException::class, trans('messages.JUST_ACCEPTED'),
            Response::HTTP_UNAUTHORIZED
        );
        return $next($request);
    }
}

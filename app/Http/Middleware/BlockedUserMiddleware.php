<?php

namespace App\Http\Middleware;

use App\Exceptions\CustomException;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockedUserMiddleware
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
        throw_if(!$request->user()->isAdmin() && $request->user()->is_banned,
            CustomException::class, trans('messages.BANNED_USER'), Response::HTTP_UNAUTHORIZED);
        throw_if($request->user()->isAdmin() && $request->user()->status === 'DISABLED',
            CustomException::class, trans('messages.BANNED_USER'), Response::HTTP_UNAUTHORIZED);

       return $next($request);
    }
}

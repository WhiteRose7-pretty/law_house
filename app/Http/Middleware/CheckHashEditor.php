<?php

namespace App\Http\Middleware;

use App\ApiHash;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckHashEditor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $hash_id = $request->input('hash_id');
        if (!ApiHash::checkHashStatus($hash_id)) {
            return response()->json(
                [
                    'message'=> 'Jesteś wylogowany...',
                    'location' => '/login',
                ],
                401
            );
        }

        if (!ApiHash::checkHash($hash_id,'editor')) {
            return response()->json(
                [
                  'message'=> 'Nie masz dostępu do tego zasobu, bądź Twoja sesja wygasła. Nastąpi przekierowanie...',
                  'location' => RouteServiceProvider::HOME,
                ],
                401
            );
        }

        return $next($request);
    }
}

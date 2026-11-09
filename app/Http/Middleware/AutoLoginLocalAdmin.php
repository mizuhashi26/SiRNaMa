<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AutoLoginLocalAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            ! Auth::check()
            && app()->environment('local')
            && ! $request->session()->get('manual_logout', false)
        ) {
            $admin = User::where('email', 'admin@unpam-simulasi.ac.id')->first()
                ?? User::orderBy('id')->first();

            if ($admin) {
                Auth::login($admin);
            }
        }

        if (! Auth::check()) {
            return redirect()->guest(route('login'));
        }

        return $next($request);
    }
}

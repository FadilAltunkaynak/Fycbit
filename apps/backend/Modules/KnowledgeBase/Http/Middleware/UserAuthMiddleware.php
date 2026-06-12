<?php

namespace Modules\KnowledgeBase\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAuthMiddleware
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
        $user = Auth::user();
        if(isset($user) && $user->status == STATUS_ACTIVE && $user->role == USER_ROLE_USER)
        {
            return $next($request);
        }
        return redirect()->route('support_login')->with(['dismiss' => __('You have to login to access this')]);
    }
}

<?php

namespace Modules\KnowledgeBase\Http\Middleware;

use Closure;
use Illuminate\Http\Request;


class KnowledgeBaseOffMiddleware
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
        return redirect()->route('adminDashboard')->with('dismiss',__('KnowledgeBase and Support feature is disable for user'));

        return $next($request);

    }
}

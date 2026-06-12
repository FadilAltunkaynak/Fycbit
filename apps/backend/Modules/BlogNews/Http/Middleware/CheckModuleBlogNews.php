<?php

namespace Modules\BlogNews\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckModuleBlogNews
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
        $setting = allsetting(['blog_news_module']);
        if($setting['blog_news_module'] ?? false)
        return $next($request);
        return redirect()->back()->with('dismiss',__("BlogNews addon is not activated"));
    }
}

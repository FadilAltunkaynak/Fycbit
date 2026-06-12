<?php

namespace Modules\KnowledgeBase\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class CheckKnowledgeBaseModuleStatusMiddleware
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
        if(DB::connection()->getDatabaseName()) {
            if (Schema::hasTable('admin_settings')) {
                if (allsetting('knowledgebase_support_module') == 1) {
                    return $next($request);
                } else {
                    if ($request->header('accept') == "application/json") {
                        return response()->json(['success' => false, 'disable' => true, 'message' => __('KnowledgeBase and Support feature is disable')]);
                    }
                    return redirect()->route('adminDashboard')->with('dismiss',__('KnowledgeBase and Support feature is disable'));
                }
            }
        }

        return $next($request);

    }
}

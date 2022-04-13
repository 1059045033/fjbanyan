<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TraceRecordMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

//        SystemTraceRecord::create([
//            'method' => $request->getMethod(),
//            'secure' => $request->getScheme(),
//            'uri' => $request->getRequestUri(),
//            'port' => $request->getPort()
//        ]);
        //Log::info('------------ '.json_encode($request->header()));
        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OptRecord
{

    public function handle(Request $request, Closure $next)
    {
        $agent = $request->header('User-Agent');
        $token = $request->header('x-token');
        $api_url = $request->url();
        $api_url = $request->getRequestUri();
        echo json_encode($request->ip());die;
        return $next($request);

    }
}

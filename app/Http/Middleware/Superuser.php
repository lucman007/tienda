<?php

namespace sysfact\Http\Middleware;

use Closure;

class Superuser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $tenantPermision = json_decode(cache('config')['emisor'], true)['superuser']??false;
        if($tenantPermision){
            return $next($request);
        }
        return redirect()->back();
    }
}

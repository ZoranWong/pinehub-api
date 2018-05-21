<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Cross
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next = null)
    {
        if(strtoupper($request->method()) === "OPTIONS"){
            $response = app('api.http.response')->noContent()->setStatusCode(HTTP_STATUS_NO_CONTENT);
            $this->setHeader($response);
        }else{
            $response = $next($request);
            $this->setHeader($response);
        }

        return $response;
    }

    private function setHeader( &$response ) {
        if(!($response instanceof RedirectResponse)) {
            Log::debug('set header');
            return $response->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Cookie')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        }else{
            return $response;
        }

    }
}

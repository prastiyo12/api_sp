<?php
namespace App\Http\Middleware;
use Closure;
class Cors2Middleware {
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set('Access-Control-Allow-Origin' , '*');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Allow-Methods', 'HEAD, PATCH, POST, GET, OPTIONS, PUT, DELETE');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Application, '.$request->header('Access-Control-Request-Headers'));

        return $response;
    }
}

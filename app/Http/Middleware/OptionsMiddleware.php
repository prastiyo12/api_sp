<?php
namespace App\Http\Middleware;
use Closure;
class OptionsMiddleware {
  public function handle($request, Closure $next)
  {

    $request_ = app('request');
    //  return response($request->getMethod('OPTIONS'));
    if ($request_->isMethod('OPTIONS'))
    {
      app()->router->options($request_->path(), function() {
        //   echo('<script>console.log("woo");</script>');
         return response('ok', 200);
       });
    }
    return $next($request);
  }
}

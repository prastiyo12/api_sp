<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWTAuth;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    // protected $jwt;
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(JWTAuth $jwt, Auth $auth)
    {
        $this->jwt = $jwt;
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    { 
        if ($this->auth->guard($guard)->guest()) {
             $res['success'] = false;
             $res['message'] = 'Unauthorized';
             return response()->json($res, 401);
         }
 
         return $next($request);
        //  try
        //  {
        //      if (!$user = $this->jwt->parseToken()->authenticate() )
        //      {
        //           return response()->json([
        //             'success'   => false,
        //             'message' => 'Auth error' 
        //           ], 401);
        //      }
        //  }
        //  catch (TokenExpiredException $e)
        //  {
        //      try
        //      {
        //          $refreshed = $this->jwt->refresh($this->jwt->getToken());
        //          $user = $this->jwt->setToken($refreshed)->toUser();
        //          // $request->headers->set('Authorization', 'Bearer '.$refreshed);
        //          return response()->json([
        //             'success'   => true,
        //             'message' => 'Token Refresh',
        //             'token' => $refreshed
        //          ], 201); 
        //          // header('Authorization: Bearer ' . $refreshed);
        //      }
        //      catch (JWTException $e)
        //      {
        //          return response()->json([
        //             'success'   => false,
        //             'message' => 'Unauthorized'
        //          ], 401);
        //      }
        //  }
        //  catch (JWTException $e)
        //  {
        //      return response()->json([
        //          'success'   => false,
        //          'message' => $e->getMessage()
        //      ], 409);
        //  }
 
        //  $this->auth->login($user, false);
 
        //  return $next($request);
     }
}

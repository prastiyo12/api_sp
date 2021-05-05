<?php
namespace App\Http\Controllers;

use Validator;
use App\User;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWTAuth;

class  AuthController extends Controller
{
    protected $jwt;
    private $request;
    private $email;
    private $auth;
   
    public function __construct(JWTAuth $jwt, Request $request, Auth $auth)
    {
        $this->jwt = $jwt;
        $this->request = $request;
        $this->auth = $auth;
    }

    /**
     * @OA\Post(
     *   path="/auth/admin/login",
     *   summary="Login Admin",
     *   tags={"Admin Credentials"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *                  @OA\Property(property="email", type="string"),
     *                  @OA\Property(property="password", type="string"),
     *                  @OA\Property(property="gate", type="string")
     *          )
     *       )
     *    ),
     *   @OA\Response(
     *     response="200",
     *     description="success"
     *   ),
     *   @OA\Response(
     *     response="500",
     *     description="error"
     *   )
     * )
     */
    public function login()
    {   
        $validator = Validator::make($this->request->all(), [
            'email'    => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $fields = '';
            foreach($validator->errors()->all() as $key => $value){
                $fields .= 'The '.$value.', ';
            }
            $res['code'] = 400;
            $res['error'] = $fields;
            return response()->json($res, 400);
        }

        $user = User::where('email','=',$this->request->input('email'))->first();
        if (empty($user)) {
            $res['code'] = 404;
            $res['message'] = 'User not found';
            return response()->json($res, 404);
        }
        try {
            if (!$token = $this->jwt->attempt(array('email' => $this->request->input('email'), 'secret_key' => $this->request->input('password')))) {
                $res['code'] = 401;
                $res['message'] = 'You email or password incorrect!';
                return response()->json($res, 401);
            }
           
            if ($user->aktif != 'Y') {
                $res['code'] = 401;
                $res['message'] = 'User not active';
                return response()->json($res, 401);
            }


            $data = [
                'uid' => $user->id_user,
                'username' => $user->username,
                'fullname' => $user->nama_lengkap,
                'email' => $user->email,
                'phone' => $user->no_telpon,
                'gate' => $this->request->input('gate')
            ];
           
            $res['code'] = 201;
            $res['data'] = $data;
            $res['token'] = $this->respondWithToken($token);
            return response()->json($res, 201);
        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['token_absent' => $e->getMessage()], $e->getStatusCode());
        }
    }

    
    public function gen_token($text)
    {
        $token_pass = $text;
        $generate_password = substr(str_shuffle($token_pass), 5, 5);
        $data              = md5($generate_password);
        $passs             = hash("sha512", $data);
        return $passs;
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->jwt->factory()->getTTL() * 60
        ]);
    }

    /**
     * @OA\Post(
     *   path="/auth/admin/forgot-password",
     *   summary="Forgot Password Admin",
     *   tags={"Admin Credentials"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *                  @OA\Property(property="email", type="string")
     *          )
     *       )
     *    ),
     *   @OA\Response(
     *     response="200",
     *     description="success"
     *   ),
     *   @OA\Response(
     *     response="500",
     *     description="error"
     *   )
     * )
     */
    public function forgot_password()
    {
        $validator = Validator::make($this->request->all(), [
            'email' => 'required|email|unique:users',
        ]);

        if ($validator->fails()) {
            $fields = '';
            foreach($validator->errors()->all() as $key => $value){
                $fields .= 'The '.$value.', ';
            }
            $res['code'] = 400;
            $res['error'] = $fields;
            return response()->json($res, 400);
        }

        try {
            $otp = (string) random_int(10, 999999);
            $check_email = User::where('email','=',$this->request->input('email'))->first();
            if(empty($check_email)){
                $res['code'] = 401;
                $res['message'] = 'email not valid!';
                return response()->json($res, 401);
            }
            $data = array(
                'password' => $this->gen_token($otp),
                'secret_key' => app('hash')->make($otp)
            );

            $update_pwd = User::where('id_user','=',$check_email->id_user)->update($data);

            $config = [
                'name' => $check_email->nama_lengkap,
                'new_password' => $otp
            ];

            Mail::send('emails.forgot_password', $config, function($message) use ($check_email){
                $message->to($check_email->email, $check_email->nama_lengkap)
                    ->subject('Forgot Password');
                $message->from('solusibejo@gmail.com','SABAH PARK');
            });

            if (Mail::failures()) {
                $res['code'] = 409;
                $res['message'] = 'Email not sent.';
                return response()->json($res, 409);
            }

            $res['code'] = 201;
            $res['message'] = 'New Password has been sent to your email. Please check your email.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }
    }
}

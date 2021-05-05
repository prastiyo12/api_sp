<?php
namespace App\Http\Controllers;

use Validator;
use App\User;
use App\Member;
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

class  AuthMemberController extends Controller
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
     *   path="/auth/member/register",
     *   summary="Registrasi Member",
     *   tags={"Member Credentials"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *                  @OA\Property(property="first_name", type="string"),
     *                  @OA\Property(property="password", type="string"),
     *                  @OA\Property(property="phone", type="string"),
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
    public function register()
    {
        //validate incoming request 
        $validator = Validator::make($this->request->all(), [
            'phone' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string',
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
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 10; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            $check_email = Member::where('email','=',$this->request->input('email'))->first();
            if(!empty($check_email)){
                $res['code'] = 401;
                $res['message'] = 'This email account has already been registered.';
                return response()->json($res, 401);
            }
            
            $token_pass = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $generate_password = substr(str_shuffle($token_pass), 5, 5);
            $user = new Member;
            $user->buyerid = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 5, 5);
            $user->first_name = $this->request->input('first_name');
            $user->last_name =  $this->request->input('last_name');
            $user->company_name = '-';
            $user->country = 'INDONESIA';
            $user->address = '-';
            $user->city = '-';
            $user->postcode = '0';
            $user->updateid = $generate_password;
            $user->createdate = date('Y-m-d');
            $user->phone = $this->request->input('phone');
            $user->email = @$this->request->input('email');
            $user->status = 0;
            $plainPassword = $this->request->input('password');
            $user->password = $this->gen_token($plainPassword);
            $user->secret_key = app('hash')->make($plainPassword);
            $user->active_key = $randomString;
            if($user->save()){
                $data = [
                    'name' => $this->request->input('first_name'),
                    'token' => $user->active_key
                ];
    
                Mail::send('emails.activation', $data, function($message) use ($user){
                    $message->to($user->email, $user->nama_lengkap)
                        ->subject('Account Activation');
                    $message->from('noreply@booking.sabahparks.org.my','SABAH PARKS');
                });
    
                if (Mail::failures()) {
                    $res['code'] = 409;
                    $res['message'] = 'Email failed to send';
                    $res['data'] = $data['token'];
                    return response()->json($res, 409);
                }
    
                $res['code'] = 201;
                $res['message'] = 'Register success. Please check your email.';
                $res['data'] = $data['token'];
                return response()->json($res, 201);
            }
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }

    }

    /**
     * @OA\Post(
     *   path="/auth/member/login",
     *   summary="Login Member",
     *   tags={"Member Credentials"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *                  @OA\Property(property="email", type="string"),
     *                  @OA\Property(property="password", type="string")
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

        $user = Member::where('email','=',$this->request->input('email'))->first();
        if (empty($user)) {
            $res['code'] = 404;
            $res['message'] = 'User not found';
            return response()->json($res, 404);
        }
        try {
            if($this->gen_token($this->request->input('password')) == $user->password )
            {
                Member::where('email','=',$this->request->input('email'))->update(['secret_key'=>app('hash')->make($this->request->input('password'))]);
            }else{
                $res['code'] = 401;
                $res['message'] = 'You email or password incorrect!';
                return response()->json($res, 401);
            }
            if (!$token = Auth::guard('member')->attempt(array('email' => $this->request->input('email'), 'secret_key' => $this->request->input('password')))) {
                $res['code'] = 401;
                $res['message'] = 'You email or password incorrect!';
                return response()->json($res, 401);
            }
           
            if ($user->status != '1') {
                $res['code'] = 401;
                $res['message'] = 'User not active';
                return response()->json($res, 401);
            }


            $data = [
                'uid' => $user->id,
                'bid' => $user->buyerid,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone
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
        $data              = md5($text);
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
     * @OA\Get(
     *   path="/auth/member/active",
     *   summary="Aktivasi Akun Member",
     *   tags={"Member Credentials"},
     *   @OA\Parameter(
     *       in ="query",
     *       name="key",
     *       required=true,
     *       @OA\Schema(
     *          type="string"
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

    public function active()
    {
        
        try {
            $data = Member::where('active_key','=',$this->request->get('key'))->update(['status' => 1]);
            return redirect('auth/member/success');
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }
    }

    /**
     * @OA\Post(
     *   path="/auth/member/forgot-password",
     *   summary="Forgot Password Member",
     *   tags={"Member Credentials"},
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
        //validate incoming request 
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
            $check_email = Member::where('email','=',$this->request->input('email'))->first();
            if(empty($check_email)){
                $res['code'] = 401;
                $res['message'] = 'email not valid!';
                return response()->json($res, 401);
            }
            $data = array(
                'password' => $this->gen_token($otp),
                'secret_key' => app('hash')->make($otp)
            );

            $update_pwd = Member::where('id','=',$check_email->id)->update($data);

            $config = [
                'name' => ucwords($check_email->first_name),
                'new_password' => $otp
            ];

            Mail::send('emails.forgot_password', $config, function($message) use ($check_email){
                $message->to($check_email->email, $check_email->nama_lengkap)
                    ->subject('Forgot Password');
                $message->from('noreply@booking.sabahparks.org.my','SABAH PARKS');
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

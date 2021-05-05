<?php
namespace App\Http\Controllers;

use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use  App\Member;
use  App\Negara;

class  MemberController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        $this->middleware('auth:member');
        $this->request = $request;
    }

    /**
     * @OA\Post(
     *   path="/api/member/logout",
     *   summary="Logout Member",
     *   tags={"Member Credentials"},
     *   @OA\Parameter(
     *       in ="header",
     *       name="Authorization",
     *       required=true,
     *       description="Bearer {access-token}",
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
    public function logout()
    {
        $data = Auth::logout();
        $res['code'] = 200;
        $res['message'] = "Successfully logged out.";
        return response()->json($res, 200);
    }

    /**
     * @OA\Get(
     *   path="/api/member/profile",
     *   summary="Get Profile Member",
     *   tags={"Member Credentials"},
     *   @OA\Parameter(
     *       in ="header",
     *       name="Authorization",
     *       required=true,
     *       description="Bearer {access-token}",
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
    public function profile()
    {
        $data = Auth::guard('member')->user();
        $negara = Negara::where('id','=', $data->country)->first();
        $user = array();
        // $user['fullname'] = $data->first_name;
        $user['firstname'] = $data->first_name;
        $user['lastname'] = $data->last_name;
        $user['phone'] = $data->phone;
        $user['city'] = $data->city;
        $user['postcode'] = intval($data->postcode);
        $user['address'] = $data->address;
        $user['email'] = $data->email;
        $user['nationality'] = $data->country;

        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['data'] = $user;
        return response()->json($res, 200);
    }

    /**
     * @OA\Post(
     *   path="/api/member/change-password",
     *   summary="Change Password Member",
     *   tags={"Member Credentials"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *               @OA\Property(property="password", type="string"),
     *               @OA\Property(property="confirm_password", type="string")
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
    public function change_password()
    {
        $validator = Validator::make($this->request->all(), [
            'password' => 'required|string',
            'confirm_password' => 'required|string'
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
            $check_data = Auth::guard('member')->user();
            if($this->request->input('password') != $this->request->input('confirm_password') ){
                $res['code'] = 401;
                $res['message'] = 'Password not valid.';
                return response()->json($res, 401);
            }

            $data = array(
                'password' => $this->gen_token($this->request->input('password')),
                'secret_key' => app('hash')->make($this->request->input('password'))
            );

            $update_pwd = Member::where('id','=',$check_data->id)->update($data);
            $res['code'] = 201;
            $res['message'] = 'New Password has been changed.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }

    }

    /**
     * @OA\Post(
     *   path="/api/member/edit-profile",
     *   summary="Edit Profile Member",
     *   tags={"Member Credentials"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *               @OA\Property(property="firstname", type="string"),
     *               @OA\Property(property="lastname", type="string"),
     *               @OA\Property(property="fullname", type="string"),
     *               @OA\Property(property="phone", type="string"),
     *               @OA\Property(property="email", type="string"),
     *               @OA\Property(property="address", type="string"),
     *               @OA\Property(property="city", type="string"),
     *               @OA\Property(property="postcode", type="string"),
     *               @OA\Property(property="nationality", type="string"),
     *               @OA\Property(property="gender", type="string", description="1 = Pria, 2 = Wanita"),
     *               @OA\Property(property="tgl_lahir", type="string")
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
    public function edit_profile()
    {
        $validator = Validator::make($this->request->all(), [
            'firstname' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|string'
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
            $check_data = Auth::guard('member')->user();
            $user = array();
            $user['first_name'] = $this->request->input('firstname');
            $user['last_name'] = $this->request->input('lastname');
            $user['address'] = @$this->request->input('address');
            $user['phone'] = @$this->request->input('phone');
            $user['city'] = @$this->request->input('city');
            $user['postcode'] = @$this->request->input('postcode');
            $user['email'] = @$this->request->input('email');
            $user['country'] = @$this->request->input('nationality');
            // $user['gender'] = @$this->request->input('gender');
            // $user['birthdate'] = @$this->request->input('tgl_lahir');
            Member::where('id','=',$check_data->id)->update($user);
            $res['code'] = 200;
            $res['message'] = 'Edit Profile success';
            $res['data'] = $user;
            return response()->json($res, 200);
    } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }

    }

    public function gen_token($text)
    {
        $data              = md5($text);
        $passs             = hash("sha512", $data);
        return $passs;
    }
}

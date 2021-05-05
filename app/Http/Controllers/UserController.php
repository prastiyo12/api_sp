<?php
namespace App\Http\Controllers;

use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use  App\User;
use  App\Negara;
use  App\Menu;
use  App\Role;

class  UserController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        $this->middleware('auth');
        $this->request = $request;
    }

    /**
     * @OA\Post(
     *   path="/api/admin/logout",
     *   summary="Logout Admin",
     *   tags={"Admin Credentials"},
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
     *   path="/api/admin/profile",
     *   summary="Get Profile Admin",
     *   tags={"Admin Credentials"},
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
        $data = Auth::guard()->user();
        $negara = Negara::where('id','=', $data->negara)->first();
        $user = array();
        $user['fullname'] = $data->nama_lengkap;
        // $user['firstname'] = $data->nama_depan;
        // $user['lastname'] = $data->nama_belakang;
        $user['phone'] = $data->no_telpon;
        // $user['city'] = $data->kota;
        // $user['postcode'] = $data->kodepos;
        // $user['address'] = $data->alamat;
        $user['email'] = $data->email;
        // $user['gender'] = (@$data->gender==1)?'Laki-laki':'Wanita';
        // $user['tgl_lahir'] = @$data->tgl_lahir;
        // $user['nationality'] = (@$negara->country_name)? $negara->country_name: "-";
        // $user['id_gender'] = @$data->gender;
        // $user['id_nationality'] = @$negara->id;

        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['data'] = $user;
        return response()->json($res, 200);
    }

    /**
     * @OA\Get(
     *   path="/api/admin/menu",
     *   summary="Get Menu Backoffice",
     *   tags={"Admin Credentials"},
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
    public function menu()
    {
        $user = Auth::user();
        $data = DB::table('stp_role_menu')
        ->leftJoin('stp_menu', function($join){
            $join->on('stp_menu.id','=','stp_role_menu.id_menu');
        })
        ->select(
            'stp_menu.id as id',
            'stp_menu.menu as title',
            'stp_menu.url as url',
            'stp_menu.icon as icon',
            'stp_menu.id_root as parent_id'
        )
        ->where('stp_role_menu.id_role','=', $user->id_role)
        ->where('stp_role_menu.status','=', 1)->get();
        //   var_dump('tes');
        // die();
        $results = $this->buildTree($data);
        return response()->json($results,200);
    }
    
    /**
     * @OA\Get(
     *   path="/api/admin/menu/role",
     *   summary="Get Menu Backoffice",
     *   tags={"Admin Credentials"},
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
    public function get_role_all()
    {
        $data = DB::table('rb_users_role');

        if($this->request->get('condition')){
            $data->where('role_name','like','%'.$this->request->get('condition').'%');
        }
        //ORDER BY
        if($this->request->get('dir')){
            $data->orderBy($this->request->get('dir'), $this->request->get('sort'));
        }else{
            $data->orderBy('role_name', 'ASC');
        }
        //PAGINATION
        $page = $this->request->get('page') != '' ? (int)$this->request->get('page') : 1;
        $rows = $this->request->get('rows') != '' &&  $this->request->get('rows') != 0 ? (int)$this->request->get('rows') : 10;
        $dir = $this->request->get('dir');
        $sort = $this->request->get('sort');

        $total = count($data->get());
        $start = ($page > 1) ? $page : 0;
        $start = ($total <= $rows) ? 0 : $start;
        $pages = ceil($total / $rows);
        $data->offset($start);
        $data->limit($rows);
        $grid = $data->get();
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['totalRow'] = $total;
        $res['totalPages'] = $pages;
        $res['data'] = $grid;
        return response()->json($res, 200);
    }
    
    /**
     * @OA\Get(
     *   path="/api/admin/menu/get",
     *   summary="Get Menu Backoffice",
     *   tags={"Admin Credentials"},
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
    public function get_menu()
    {
        $data = Menu::leftJoin('stp_role_menu', function($join){
            $join->on('stp_menu.id','=','stp_role_menu.id_menu');
        })
        ->select(
            'stp_menu.id as id_menu',
            'stp_menu.menu as title',
            'stp_menu.url as url',
            'stp_menu.icon as icon',
            'stp_menu.id_root as parent_id',
            'stp_role_menu.status'
        )
        ->where('stp_role_menu.id_role','=', $this->request->input('id_role'));

        if($this->request->get('condition')){
            $data->where('rb_users.nama_lengkap','like','%'.$this->request->get('condition').'%');
            $data->orWhere('rb_users.nama_lengkap','like','%'.$this->request->get('condition').'%');
        }
        //ORDER BY
        if($this->request->get('dir')){
            $data->orderBy($this->request->get('dir'), $this->request->get('sort'));
        }else{
            $data->orderBy('rb_users.nama_lengkap', 'ASC');
        }
        //PAGINATION
        $page = $this->request->get('page') != '' ? (int)$this->request->get('page') : 1;
        $rows = $this->request->get('rows') != '' &&  $this->request->get('rows') != 0 ? (int)$this->request->get('rows') : 10;
        $dir = $this->request->get('dir');
        $sort = $this->request->get('sort');

        $total = count($data->get());
        $start = ($page > 1) ? $page : 0;
        $start = ($total <= $rows) ? 0 : $start;
        $pages = ceil($total / $rows);
        // $data->offset($start);
        // $data->limit($rows);
        $grid = $data->get();
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['totalRow'] = $total;
        $res['totalPages'] = $pages;
        $res['data'] = $grid;
        return response()->json($res, 200);
    }

    /**
     * @OA\Post(
     *   path="/api/admin/change-password",
     *   summary="Change Password Admin",
     *   tags={"Admin Credentials"},
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
            $check_data = Auth::user();
            if($this->request->input('password') != $this->request->input('confirm_password') ){
                $res['code'] = 401;
                $res['message'] = 'Password not valid.';
                return response()->json($res, 401);
            }

            $data = array(
                'password' => $this->gen_token($this->request->input('password')),
                'secret_key' => app('hash')->make($this->request->input('password'))
            );

            $update_pwd = User::where('id_user','=',$check_data->id_user)->update($data);
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
     *   path="/api/admin/edit-profile",
     *   summary="Edit Profile Admin",
     *   tags={"Admin Credentials"},
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
            'fullname' => 'required|string',
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
            $check_data = Auth::user();
            $user = array();
            $user['nama_lengkap'] = $this->request->input('fullname');
            // $user['nama_depan'] = $this->request->input('firstname');
            // $user['nama_belakang'] = $this->request->input('lastname');
            // $user['alamat'] = @$this->request->input('address');
            $user['no_telpon'] = @$this->request->input('phone');
            // $user['kota'] = @$this->request->input('city');
            // $user['kodepos'] = @$this->request->input('postcode');
            // $user['alamat'] = @$this->request->input('address');
            $user['email'] = @$this->request->input('email');
            // $user['negara'] = @$this->request->input('nationality');
            // $user['gender'] = @$this->request->input('gender');
            // $user['tgl_lahir'] = @$this->request->input('tgl_lahir');
            User::where('id_user','=',$check_data->id_user)->update($user);
            $res['code'] = 200;
            $res['message'] = 'Register success. Please check your email.';
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
        $token_pass = $text;
        $generate_password = substr(str_shuffle($token_pass), 5, 5);
        $data              = md5($generate_password);
        $passs             = hash("sha512", $data);
        return $passs;
    }

    function buildTree($elements, $parentId = NULL) {
        $branch = array();
        foreach ($elements as $element) {
            if ($element->parent_id === $parentId) {
                $children = $this->buildTree($elements, $element->id);
                if ($children) {
                    $element->items = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }

    /**
     * @OA\Get(
     *   path="/api/user/all",
     *   summary="Get User",
     *   tags={"Setup User (BackOffice)"},
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
    public function all()
    {
        $data = User::leftJoin('rb_users_role', function($join){
                    $join->on('rb_users_role.id','=','rb_users.id_role');
                });

        if($this->request->get('condition')){
            $data->where(function($search){
                $search->where('rb_users.nama_lengkap','like','%'.$this->request->get('condition').'%');
                $search->orWhere('rb_users.username','like','%'.$this->request->get('condition').'%');
                $search->orWhere('rb_users.email','like','%'.$this->request->get('condition').'%');
                $search->orWhere('rb_users.no_telpon','like','%'.$this->request->get('condition').'%');
                $search->orWhere('rb_users_role.role_name','like','%'.$this->request->get('condition').'%');
            });
        }
        //ORDER BY
        if($this->request->get('dir')){
            $data->orderBy($this->request->get('dir'), $this->request->get('sort'));
        }else{
            $data->orderBy('rb_users.nama_lengkap', 'ASC');
        }
        //PAGINATION
        $page = $this->request->get('page') != '' ? (int)$this->request->get('page') : 1;
        $rows = $this->request->get('rows') != '' &&  $this->request->get('rows') != 0 ? (int)$this->request->get('rows') : 10;
        $dir = $this->request->get('dir');
        $sort = $this->request->get('sort');

        $total = count($data->get());
        $start = ($page > 1) ? $page : 0;
        $start = ($total <= $rows) ? 0 : $start;
        $pages = ceil($total / $rows);
        $data->offset($start);
        $data->limit($rows);
        $grid = $data->get();
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['totalRow'] = $total;
        $res['totalPages'] = $pages;
        $res['data'] = $grid;
        return response()->json($res, 200);
    }

    /**
     * @OA\Get(
     *   path="/api/user/role",
     *   summary="Get Role user",
     *   tags={"Setup User (BackOffice)"},
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
    public function get_role()
    {
        $data = DB::table('rb_users_role')->get();
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['data'] = $data;
        return response()->json($res, 200);
    }

    /**
     * @OA\Post(
     *   path="/api/user/manage",
     *   summary="Manage User",
     *   tags={"Setup User (BackOffice)"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *               @OA\Property(property="id_user", type="string"),
     *               @OA\Property(property="username", type="string"),
     *               @OA\Property(property="password", type="string"),
     *               @OA\Property(property="email", type="string"),
     *               @OA\Property(property="no_telpon", type="string"),
     *               @OA\Property(property="nama_lengkap", type="string"),
     *               @OA\Property(property="id_role", type="string")
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
    public function manage()
    {
        $validator = Validator::make($this->request->all(), [
            'username' => 'required|string',
            'nama_lengkap' => 'required|string',
            'email' => 'required|email|string'
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
            if($this->request->input('id_user')){
                $data = array();
                foreach($this->request->all() as $key => $value){
                    if($key != 'id_user'){
                        $data[$key] = $value;
                    }
                }
                $data = User::where('id_user','=', $this->request->input('id_user'))->update($data);
            }else{
                $data = new User;
                foreach($this->request->all() as $key => $value){
                    if($key != 'id_user'){
                        $data->{$key} = $value;
                    }
                }
                $data->jabatan = 'Konsumen';
                $data->level = 'user';
                $plainPassword = $this->request->input('password');
                $data->password = $this->gen_token($plainPassword);
                $data->secret_key = app('hash')->make($plainPassword);
                $data->save();              
            }
            $res['code'] = 201;
            $res['message'] = 'Data Success Created.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }
    }
    
    /**
     * @OA\Post(
     *   path="/api/admin/menu/role-manage",
     *   summary="Manage User",
     *   tags={"Setup User (BackOffice)"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *               @OA\Property(property="role_name", type="string")
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
    public function manage_role()
    {
        $validator = Validator::make($this->request->all(), [
            'role_name' => 'required|string',
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
            if($this->request->input('id')){
                $data = array();
                foreach($this->request->all() as $key => $value){
                    if($key != 'id'){
                        $data[$key] = $value;
                    }
                }
                $data = Role::where('id','=', $this->request->input('id'))->update($data);
            }else{
                $data = new Role;
                foreach($this->request->all() as $key => $value){
                    if($key != 'id'){
                        $data->{$key} = $value;
                    }
                }
                if($data->save()){
                    $menu = Menu::get();
                    foreach($menu as $key_menu => $mn){
                        $data_menu = array();
                        $data_menu['id_role'] = $data->max('id');
                        $data_menu['id_menu'] = $mn->id;
                        $data_menu['status'] = 1;
                        DB::table('stp_role_menu')->insert($data_menu);
                    }
                }              
            }
            $res['code'] = 201;
            $res['message'] = 'Data Success Created.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }
    }
    
    /**
     * @OA\Post(
     *   path="/api/admin/menu/role-menu-manage",
     *   summary="Manage User",
     *   tags={"Setup User (BackOffice)"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *               @OA\Property(property="role_name", type="string")
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
    public function manage_role_menu()
    {
        $validator = Validator::make($this->request->all(), [
            'id_role' => 'required|integer',
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
            DB::table('stp_role_menu')->where('id_role','=',$this->request->input('id_role'))->delete();
            foreach($this->request->input('data') as $key => $value){
                $data = array();
                $data['id_role'] = $this->request->input('id_role');
                $data['id_menu'] = $value['id_menu'];
                $data['status'] = $value['status'];
                DB::table('stp_role_menu')->insert($data);
            }
            $res['code'] = 201;
            $res['message'] = 'Data Success Created.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }
    }
    
    /**
     * @OA\Post(
     *   path="/api/admin/menu/role-delete",
     *   summary="Manage User",
     *   tags={"Setup User (BackOffice)"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *               @OA\Property(property="role_name", type="string")
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
    public function delete_role()
    {
        $validator = Validator::make($this->request->all(), [
            'id' => 'required|integer',
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
            DB::table('rb_users_role')->where('id','=',$this->request->input('id'))->delete();  
            DB::table('stp_role_menu')->where('id_role','=',$this->request->input('id'))->delete();              
            $res['code'] = 201;
            $res['message'] = 'Data Success Created.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }
    }
    
    public function delete()
    {
        $validator = Validator::make($this->request->all(), [
            'id' => 'required|integer',
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
            User::where('id_user','=',$this->request->input('id'))->delete();  
            $res['code'] = 201;
            $res['message'] = 'Data Success Created.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }
    }
}

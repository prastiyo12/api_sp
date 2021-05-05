<?php
namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use  App\Member;

class  FCMController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        $this->middleware('auth:member');
        $this->request = $request;
    }

    public function update_token()
    {
        $validator = Validator::make($this->request->all(), [
            'firebase_id' => 'required|string'
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
            $user = Auth::guard('member')->user();
            $master = Member::where('id','=',$user->id)
                        ->update(['active_key' => $this->request->input('firebase_id')]);
            $res['code'] = 201;
            $res['message'] = 'Token Success Updated.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }
    }

}

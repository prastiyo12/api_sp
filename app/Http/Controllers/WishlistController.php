<?php
namespace App\Http\Controllers;

use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use  App\Broadcast;
use  App\Destination;
use  App\Member;
use  App\Ticket;

class  WishlistController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        $this->middleware('auth:member');
        $this->request = $request;
    }

    public function get()
    {
        $member =  $data = Auth::guard('member')->user();
        $data = Destination::with(['images', 'prices'])
                ->leftJoin('trx_member_dest',function($join)
                {
                    $join->on('stp_dest.destid','=','trx_member_dest.destid');
                })
                ->select('stp_dest.*')
                ->where('trx_member_dest.buyerid','=', $member->buyerid)
                ->where('stp_dest.status','=','1');

        if($this->request->get('offset') && $this->request->get('limit'))
        {
            $start = $this->request->get('offset');
            $limit = $this->request->get('limit');
            $data->offset($start);
            $data->limit($limit);
        }
        $grid = $data->get();
        
        foreach($grid as $key => $value)
        {
            $value->{'dest_type'} = $this->request->get('id_type') == 1?'MONTAIN CLIMBING': $this->request->get('id_type') == 2?'ISLAND HOPPING':'HILL PARKS';
            if(!empty($value->destphoto_cover)){
                $img = explode('.',$value->destphoto_cover);
                $extension = end($img);
                $filename = substr($value->destphoto_cover, 0, strpos($value->destphoto_cover, '.'.$extension));
                $value->destphoto = 'https://'.$_SERVER['SERVER_NAME'].'/public/images/'.$filename.'/'.$extension;
            }else{
                $value->destphoto = 'https://'.$_SERVER['SERVER_NAME'].'/public/default/default-photo/png';
            }
            
            foreach($value->images as $val){
                $val->destphoto = $value->destphoto;
            }
            
        }
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['data'] = $grid;
        return response()->json($res, 200);
    }

    public function manage()
    {
        $validator = Validator::make($this->request->all(), [
            'destid' => 'required|string'
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
            $member =  $data = Auth::guard('member')->user();
            $check = DB::table('trx_member_dest')
                ->where('destid','=',$this->request->input('destid'))
                ->where('buyerid', '=', $member->buyerid )
                ->first();
            if(!empty($check)){
                $res['code'] = 200;
                $res['message'] = 'Destination already in wishlist.';
                return response()->json($res, 200);    
            }
            $data = array();
            $data['destid'] = $this->request->input('destid');
            $data['buyerid'] = $member->buyerid;
            $data['status'] = 1;
            DB::table('trx_member_dest')->insert($data);
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
            'destid' => 'required|string'
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
            $member =  $data = Auth::guard('member')->user();
            DB::table('trx_member_dest')
            ->where('destid','=',$this->request->input('destid'))
            ->where('buyerid','=', $member->buyerid)
            ->delete();
            $res['code'] = 201;
            $res['message'] = 'Deleted Success Created.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }

    }
}

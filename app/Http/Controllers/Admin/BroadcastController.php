<?php
namespace App\Http\Controllers\Admin;

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
use App\Helpers\CustomHelper;

class  BroadcastController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        $this->middleware('auth');
        $this->request = $request;
    }

    /**
     * @OA\Get(
     *   path="/api/admin/broadcast/get",
     *   summary="Get Broadcast",
     *   tags={"Setup Broadcast (BackOffice)"},
     *   @OA\Parameter(
     *       in ="header",
     *       name="Authorization",
     *       required=true,
     *       description="Bearer {access-token}",
     *       @OA\Schema(
     *          type="string"
     *       )
     *    ),
     *    @OA\Parameter(
     *       in ="query",
     *       name="search",
     *       required=true,
     *       description="Pencarian Judul",
     *       @OA\Schema(
     *          type="string"
     *       )
     *    ),
     *    @OA\Parameter(
     *       in ="query",
     *       name="filter",
     *       required=true,
     *       description=" {kosong} = all, 1 = News, 2 = Promotion, 3 = Events",
     *       @OA\Schema(
     *          type="string"
     *       )
     *    ),
     *   @OA\Parameter(
     *       in ="query",
     *       name="offset",
     *       required=true,
     *       description="index start data",
     *       @OA\Schema(
     *          type="string"
     *       )
     *    ),
     *   @OA\Parameter(
     *       in ="query",
     *       name="limit",
     *       required=true,
     *       description=" data yang ingin di tampilkan",
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
    public function get()
    {
        $data = Broadcast::with(['details'])
                ->orderBy('date_created', 'DESC');

        if($this->request->get('condition')){
            $data->where('title','like','%'.$this->request->get('condition').'%');
        }
        //ORDER BY
        if($this->request->get('dir')){
            $data->orderBy($this->request->get('dir'), $this->request->get('sort'));
        }else{
            $data->orderBy('date_created', 'DESC');
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
        foreach($grid as $key => $value)
        {
            switch($value->id_type)
            {
                case 1:
                    $value->{'type'} = 'News';
                    break;
                case 2:
                    $value->{'type'} = 'Promotion';
                    break;
                case 3:
                    $value->{'type'} = 'Events';
                    break;
            }
            
            $date = date_create($value->date_created);
            $value->date_created = date_format($date,"Y-m-d");
            
            if(empty($value->path))
            {
                $value->path = 'http://'. $_SERVER['SERVER_NAME'].'/public/default/default-photo/png';
            }else{
                $url = explode('.',$value->path);
                $extension = end($url);
                $filename = substr($value->path, 0, strpos($value->path, '.'.$extension));
                $value->path ='http://'.$_SERVER['SERVER_NAME'].'/public/images/'.$filename.'/'.$extension;
            }

        }
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['totalRow'] = $total;
        $res['totalPages'] = $pages;
        $res['data'] = $grid;
        return response()->json($res, 200);
    }

    /**
     * @OA\Post(
     *   path="/api/admin/broadcast/manage",
     *   summary="Manage Broadcast",
     *   tags={"Setup Broadcast (BackOffice)"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *               @OA\Property(property="id", type="string"),
     *               @OA\Property(property="id_type", type="string"),
     *               @OA\Property(property="title", type="string"),
     *               @OA\Property(property="content", type="string"),
     *               @OA\Property(property="date_create", type="string"),
     *               @OA\Property(property="expire_date", type="string"),
     *               @OA\Property(property="status", type="string")
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
            'title' => 'required|string',
            'content' => 'required|string'
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
                $data = Broadcast::find(intval($this->request->input('id')));
                foreach($this->request->all() as $key => $value){
                    $data->{$key} = $value;
                }
                if(empty($this->request->input('path')))
                {
                    unset($data->path);
                }
                $data->save();
            }else{
                $data = new Broadcast;
                foreach($this->request->all() as $key => $value){
                    if($key != 'id'){
                        $data->{$key} = $value;
                    }
                }
    
                switch($data->id_type)
                {
                    case 1:
                        $data->{'icon'} = 'fa fa-newspaper';
                        break;
                    case 2:
                        $data->{'icon'} = 'fa fa-bullhorn';
                        break;
                    case 3:
                        $data->{'icon'} = 'fa fa-calendar';
                        break;
                }
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
     *   path="/api/admin/broadcast/delete",
     *   summary="Delete Inbox",
     *   tags={"Setup Broadcast (BackOffice)"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *               @OA\Property(property="id", type="string"),
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
    public function delete()
    {
        $validator = Validator::make($this->request->all(), [
            'id' => 'required|integer'
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
            $master = Broadcast::where('id','=',$this->request->input('id'))
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

    /**
     * @OA\Post(
     *   path="/api/admin/broadcast/send-email",
     *   summary="Send Email",
     *   tags={"Setup Broadcast (BackOffice)"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *               @OA\Property(property="id", type="string"),
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
    public function send_email()
    {
        $validator = Validator::make($this->request->all(), [
            'id' => 'required|integer'
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
            $broadcast = Broadcast::where('id','=',$this->request->input('id'))->first();
            $member = Ticket::where('status','=',1)
                    ->select(
                        'email',
                        DB::raw('max(visitor_name) as name')
                    )
                    ->groupBy(
                        'email'
                    )
                    ->get();
            $emails = Ticket::where('status','=',1)
                    ->select('email')
                    ->groupBy('email')
                    ->pluck('email')
                    ->toArray();
            if(!empty($broadcast->path))
            {
                $path = env('IMG_PATH').$broadcast->path;
            }else{
                $path = '';
            }
            
            foreach($member as $id => $mbr)
            {  
                $data = [
                    'title' => $broadcast->title,
                    'content' => $broadcast->content,
                    'name' => $mbr->visitor_name,
                    'image' => $path,
                    'token' => 'ssdfdss'
                ];
    
                Mail::send('emails.broadcast', $data, function($message) use ($broadcast,$mbr){
                    $message->to($mbr->email,$mbr->visitor_name)
                        ->subject( $broadcast->title);
                    $message->from('noreply@booking.sabahparks.org.my','SABAH PARKS');
                });
    
                if (Mail::failures()) {
                    $res['code'] = 409;
                    $res['message'] = 'Email failed to send';
                    return response()->json($res, 409);
                }
            }
            $res['code'] = 201;
            $res['message'] = 'Email sent.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/admin/broadcast/send-inbox",
     *   summary="Send Inbox",
     *   tags={"Setup Broadcast (BackOffice)"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *               @OA\Property(property="id", type="string"),
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
    // public function send_inbox()
    // {
    //     $validator = Validator::make($this->request->all(), [
    //         'id' => 'required|integer'
    //     ]);
        
    //     if ($validator->fails()) {
    //         $fields = '';
    //         foreach($validator->errors()->all() as $key => $value){
    //             $fields .= 'The '.$value.', ';
    //         }
    //         $res['code'] = 400;
    //         $res['error'] = $fields;
    //         return response()->json($res, 400);
    //     }

    //     try {
    //         $broadcast = Broadcast::where('id','=',$this->request->input('id'))->first();
    //         $member = Ticket::where('status','=',1)->get();
    //         foreach($member as $id => $mbr)
    //         {
    //             $insert = DB::table('trx_member_broadcast')
    //             ->insert([
    //                 'id_member' => $mbr->buyerid,
    //                 'id_broadcast' => $broadcast->id,
    //                 'id_blog' => 0,
    //                 'status' => 1
    //             ]);
    //         }
    //         $res['code'] = 201;
    //         $res['message'] = 'Email not sent.';
    //         return response()->json($res, 201);
    //     } catch (\Exception $e) {
    //         $res['code'] = 500;
    //         $res['message'] = $e->getMessage();
    //         return response()->json($res, 500);
    //     }
    // }
    
    public function send_inbox()
    {
        $validator = Validator::make($this->request->all(), [
            'id' => 'required|integer'
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
            $broadcast = Broadcast::where('id','=',$this->request->input('id'))->first();
            $member = Ticket::where('status','=',1)->get();
            foreach($member as $id => $mbr)
            {
                $check = DB::table('trx_member_broadcast')
                        ->where('id_member','=',$mbr->buyerid)
                        ->where('id_broadcast','=',$broadcast->id)
                        ->count();
                if($check == 0){
                     $insert = DB::table('trx_member_broadcast')
                    ->insert([
                        'id_member' => $mbr->buyerid,
                        'id_broadcast' => $broadcast->id,
                        'id_blog' => 0,
                        'status' => 1
                    ]);
                }
            }
            
            $user = Member::leftJoin('trx_ticket',function($join){
                        $join->on('stp_member.buyerid','=','trx_ticket.buyerid');
                    })
                    ->where('trx_ticket.status','=',1)
                    ->pluck('active_key')
                    ->toArray();
            $data = [
                  "notif_type"=> 1,
                  "data"=>$broadcast
            ];
            $shorttext = substr($broadcast->content, 0, 25);
            $push = CustomHelper::sendpush('Sabah Parks Information',$broadcast->title , $data, array_filter($user));
            if ($push->numberSuccess() > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notif send',
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'error_message_code' => 'failed_sending_notification',
                    'message' => $push
                ], 200);
                
            }
            $res['code'] = 201;
            $res['message'] = 'Message not sent.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }
    }
}

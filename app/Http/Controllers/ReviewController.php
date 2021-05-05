<?php
namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Review;

class  ReviewController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        $this->middleware('auth:member');
        $this->request = $request;
    }


    public function get()
    {
        $user = Auth::guard('member')->user();
        $data1 = DB::table('trx_broadcast')
                ->leftJoin('trx_member_broadcast',function($join){
                    $join->on('trx_member_broadcast.id_broadcast','=','trx_broadcast.id');
                })
                ->select(
                    'trx_broadcast.id',
                    'trx_broadcast.id_type',
                    'trx_broadcast.title',
                    'trx_broadcast.content',
                    'trx_broadcast.date_created',
                    'trx_broadcast.path'
                )
                ->where('trx_member_broadcast.id_member','=',$user->buyerid)
                ->where('trx_member_broadcast.status','=',1)
                 ->where('trx_broadcast.status','=',1);;
        $data = DB::table('trx_blog')
                ->leftJoin('trx_member_broadcast',function($join){
                    $join->on('trx_member_broadcast.id_blog','=','trx_blog.id');
                })
                ->select(
                    'trx_blog.id as id',
                    'trx_blog.type as id_type',
                    'trx_blog.tag as title',
                    'trx_blog.description as content',
                    'trx_blog.createdate as date_created ',
                    'trx_blog.path_cover as path'
                )
                ->where('trx_member_broadcast.id_member','=',$user->buyerid)
                ->where('trx_member_broadcast.status','=',1)
                ->where('trx_blog.status','=',1)
                ->union($data1);
        // $data = Broadcast::leftJoin('trx_broadcast',function($join){
        //             $join->on('trx_member_broadcast.id_broadcast','=','trx_broadcast.id');
        //         })
        //         ->select('trx_broadcast.*')
        //         ->orderBy('trx_broadcast.date_created', 'DESC')
        //         ->where('trx_member_broadcast.id_member','=',$user->id)
        //         ->where('trx_member_broadcast.status','=',1);

        if($this->request->get('filter')){
            $data->where('trx_broadcast.id_type','=', $this->request->get('filter'));
        }

        if($this->request->get('search')){
            $data->where('title','like','%'.$this->request->get('search').'%');
        }
        
        if($this->request->get('offset')){
            $start = $this->request->get('offset');
            $limit = $this->request->get('limit');
            $data->offset($start);
            $data->limit($limit);
        }
        
       
       
        $grid = $data->get();
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['data'] = $grid;
        return response()->json($res, 200);
    }

    /**
     * @OA\Post(
     *   path="/api/member/inbox/delete",
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
    public function review()
    {
        $validator = Validator::make($this->request->all(), [
            'billing_id' => 'required|string',
            'destid' => 'required|string',
            'rate' => 'required|integer',
            'ulasan' => 'required|string'
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
            $data = new Review;
            foreach($this->request->all() as $key => $value){
                if($key != 'prices' && $key != 'id'){
                    $data->{$key} = $value;
                }
            }
            $data->createdate = date('Y-m-d H:i:s');
            $data->buyerid = $user->buyerid;
            $data->save();         
            $res['code'] = 201;
            $res['message'] = 'Success Created.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }

    }
}

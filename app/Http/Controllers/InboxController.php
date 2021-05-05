<?php
namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use  App\Broadcast;
use  App\DestinationType;
use  App\DestinationPrice;

class  InboxController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        $this->middleware('auth:member');
        $this->request = $request;
    }


    /**
     * @OA\Get(
     *   path="/api/member/inbox/get",
     *   summary="Get Inbox Broadcast",
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
                ->where('trx_broadcast.status','=',1);
        if($this->request->get('filter') != 5 && !empty($this->request->get('filter'))){
            $data1->where('trx_broadcast.id_type','=', $this->request->get('filter'));
        }else{
            $data1->where('trx_broadcast.id_type','=', 4);
        }
        $data = DB::table('trx_blog')
                ->leftJoin('trx_member_broadcast',function($join){
                    $join->on('trx_member_broadcast.id_blog','=','trx_blog.id');
                })
                ->select(
                    'trx_blog.id as id',
                    'trx_blog.type as id_type',
                    'trx_blog.tag as title',
                    'trx_blog.description as content',
                    'trx_blog.createdate as date_created',
                    'trx_blog.path_cover as path'
                )
                ->where('trx_member_broadcast.id_member','=',$user->buyerid)
                ->where('trx_member_broadcast.status','=',1)
                ->where('trx_blog.status','=',1)
                ->where('trx_blog.type','!=',4);
                if($this->request->get('filter')){
                    $data->where('trx_blog.type','=', $this->request->get('filter'));
                }
                $data->union($data1);
        // $data = Broadcast::leftJoin('trx_broadcast',function($join){
        //             $join->on('trx_member_broadcast.id_broadcast','=','trx_broadcast.id');
        //         })
        //         ->select('trx_broadcast.*')
        //         ->orderBy('trx_broadcast.date_created', 'DESC')
        //         ->where('trx_member_broadcast.id_member','=',$user->id)
        //         ->where('trx_member_broadcast.status','=',1);

        

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
        foreach($grid as $key => $value)
        {
            $date = date_create($value->date_created);
            $value->date_created = date_format($date,"Y-m-d");
        }
        
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
    public function delete()
    {
        $validator = Validator::make($this->request->all(), [
            'id' => 'required|string'
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
            $master = DB::table('trx_member_broadcast')
                        ->where('id_broadcast','=',$this->request->input('id'))
                        ->where('id_member','=',$user->id)
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

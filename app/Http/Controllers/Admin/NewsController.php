<?php
namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use  App\Blog;
use  App\Destination;
use  App\Member;

class  NewsController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        $this->middleware('auth');
        $this->request = $request;
    }

    /**
     * @OA\Get(
     *   path="/api/admin/blog/get",
     *   summary="Get News,Blogs & Events",
     *   tags={"Setup News,Blogs & Events (BackOffice)"},
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
        $data = Blog::with(['details'])
                ->orderBy('createdate', 'DESC');

        if($this->request->get('condition')){
            $data->where('blogtitle','like','%'.$this->request->get('condition').'%');
            $data->orWhere('description','like','%'.$this->request->get('condition').'%');
        }
        //ORDER BY
        if($this->request->get('dir')){
            $data->orderBy($this->request->get('dir'), $this->request->get('sort'));
        }else{
            $data->orderBy('createdate', 'DESC');
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
            switch($value->type)
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
                case 4:
                    $value->{'type'} = 'Blogs';
                    break;
            }
            
            if(empty($value->path_cover))
            {
                $value->path_cover = 'http://'. $_SERVER['SERVER_NAME'].'/public/default/default-photo/png';
            }else{
                $url = explode('.',$value->path_cover);
                $extension = end($url);
                $filename = substr($value->path_cover, 0, strpos($value->path_cover, '.'.$extension));
                $value->path_cover ='http://'.$_SERVER['SERVER_NAME'].'/public/images/'.$filename.'/'.$extension;
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
     *   path="/api/admin/blog/manage",
     *   summary="Manage News,Blogs & Events",
     *   tags={"Setup News,Blogs & Events (BackOffice)"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *               @OA\Property(property="id", type="string"),
     *               @OA\Property(property="blogid", type="string"),
     *               @OA\Property(property="description", type="string"),
     *               @OA\Property(property="tag", type="string"),
     *               @OA\Property(property="type", type="string"),
     *               @OA\Property(property="path_cover", type="string"),
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
            'description' => 'required|string'
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
                $data = Blog::find(intval($this->request->input('id')));
                foreach($this->request->all() as $key => $value){
                    $data->{$key} = $value;
                }
                if(empty($this->request->input('path_cover')))
                {
                    unset($data->path_cover);
                }
                $data->save();
            }else{
                $data = new Blog;
                foreach($this->request->all() as $key => $value){
                    if($key != 'id'){
                        $data->{$key} = $value;
                    }
                }
    
                $data->blogid = (string) random_int(10, 99999);
                $data->tag = $data->blogtitle;
                $data->path_cover = empty($data->path_cover)?"":$data->path_cover; 
                $data->updateid = 1;
                
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
     *   path="/api/admin/blog/delete",
     *   summary="Delete News,Blogs & Events",
     *   tags={"Setup News,Blogs & Events (BackOffice)"},
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
            $master = Blog::where('id','=',$this->request->input('id'))
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
     *   path="/api/admin/blog/send-email",
     *   summary="Send Email News,Blogs & Events",
     *   tags={"Setup News,Blogs & Events (BackOffice)"},
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
            $broadcast = Blog::where('id','=',$this->request->input('id'))->first();
            $member = Member::where('status','=',1)->get();
            if(!empty($broadcast->path_cover))
            {
                $path = env('IMG_PATH').$broadcast->path_cover;
            }else{
                $path = '';
            }
            
            $type = ($broadcast->type == 1)?'News':($broadcast->type == 2)?'Events':'Promotion';
            foreach($member as $id => $mbr)
            {
                 $data = [
                    'title' => $broadcast->tag,
                    'content' => $broadcast->description,
                    'image' => $path,
                ];
    
                Mail::send('emails.broadcast', $data, function($message) use ($mbr,$type,$broadcast){
                    $message->to($mbr->email, $mbr->first_name)
                        ->subject($broadcast->blogtitle);
                    $message->from('noreply@booking.sabahparks.org.my','SABAH PARKS');
                });
    
                if (Mail::failures()) {
                    $res['code'] = 409;
                    $res['message'] = 'Email failed to send';
                    $res['data'] = $data['token'];
                    return response()->json($res, 409);
                }
            }
            $res['code'] = 201;
            $res['message'] = 'Email not sent.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/admin/blog/send-inbox",
     *   summary="Send Inbox News,Blogs & Events",
     *   tags={"Setup News,Blogs & Events (BackOffice)"},
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
            $broadcast = Blog::where('id','=',$this->request->input('id'))->first();
            $member = Member::get();
            foreach($member as $id => $mbr)
            {
                $insert = DB::table('trx_member_broadcast')
                ->insert([
                    'id_member' => $mbr->buyerid,
                    'id_broadcast' => 0,
                    'id_blog' => $broadcast->id,
                    'status' => 1
                ]);
            }
            $res['code'] = 201;
            $res['message'] = 'Email not sent.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }
    }
}

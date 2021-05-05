<?php
namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use  App\Gallery;

class  GalleryController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        $this->middleware('auth');
        $this->request = $request;
    }


    /**
     * @OA\Get(
     *   path="/api/admin/gallery/get",
     *   summary="Get Gallery",
     *   tags={"Setup Gallery (BackOffice)"},
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
        $data = Gallery::leftJoin('stp_dest_type',function($join){
                $join->on('stp_dest_type.id','=','stp_gallery.type');
            })
            ->select('stp_gallery.*','stp_dest_type.desttype')
            ->orderBy('stp_gallery.createdate', 'DESC')
            ;

        if($this->request->get('condition')){
            $data->where('stp_gallery.description','like','%'.$this->request->get('condition').'%');
            $data->orWhere('stp_dest_type.desttype','like','%'.$this->request->get('condition').'%');
        }
        //ORDER BY
        if($this->request->get('dir')){
            $data->orderBy($this->request->get('dir'), $this->request->get('sort'));
        }else{
            $data->orderBy('stp_gallery.id', 'DESC');
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
     *   path="/api/admin/gallery/manage",
     *   summary="Manage Gallery",
     *   tags={"Setup Gallery (BackOffice)"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *               @OA\Property(property="id", type="string"),
     *               @OA\Property(property="path", type="string"),
     *               @OA\Property(property="description", type="string"),
     *               @OA\Property(property="type", type="string"),
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
                $data = Gallery::find(intval($this->request->input('id')));
                foreach($this->request->all() as $key => $value){
                    if($key != 'prices'){
                        $data->{$key} = $value;
                    }
                }
                $data->save();
            }else{
                $data = new Gallery;
                foreach($this->request->all() as $key => $value){
                    if($key != 'prices' && $key != 'id'){
                        $data->{$key} = $value;
                    }
                }
                $data->createdate = date('Y-m-d H:i:s');
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
     *   path="/api/admin/gallery/delete",
     *   summary="Delete Gallery",
     *   tags={"Setup Gallery (BackOffice)"},
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
            $master = Gallery::where('id','=',$this->request->input('id'))
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

    public function upload()
    {
        try {
            if ($this->request->hasFile('images')) {
                $allowedfileExtension=['jpg','jpeg','png','JPG','JPEG'];
                $file = $this->request->file('images');
                $filename = $file->getClientOriginalName();
                $path = storage_path();
                $path2 = env('IMG_PATH');
                $extension = $file->getClientOriginalExtension();
                $check=in_array($extension,$allowedfileExtension);
                if($check)
                {
                    // $file->move($path."/images/gallery", $filename);
                    $file->move($path2, $filename);
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
}

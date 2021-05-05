<?php
namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\CustomHelper;
use App\Destination;
use App\DestinationType;
use App\DestinationPrice;
use App\DestinationDetail;
use App\DestinationImage;
use App\BillingDetail;

class  DestinationController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        $this->middleware('auth');
        $this->request = $request;
    }

    /**
     * @OA\Get(
     *   path="/api/admin/destination/type",
     *   summary="Get Type Destination",
     *   tags={"Setup Destination (BackOffice)"},
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
    public function get_type()
    {   
        $data = DestinationType::with(['destination'])
                ->orderBy('desttype', 'asc');

        $grid = $data->get();
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['data'] = $grid;
        return response()->json($res, 200);
    }

    /**
     * @OA\Get(
     *   path="/api/admin/destination/get",
     *   summary="Get Data Destination",
     *   tags={"Setup Destination (BackOffice)"},
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
        date_default_timezone_set("Asia/Kuala_Lumpur");
        
        $data = Destination::with(['prices', 'details','images'])
                ->leftJoin('stp_dest_type', function($join){
                    $join->on('stp_dest.desttype','=','stp_dest_type.id');
                })
                ->select(
                    'stp_dest.*', 
                    'stp_dest_type.desttype as type')
               ;

        if($this->request->get('condition')){
            $data->where('stp_dest.destname','like','%'.$this->request->get('condition').'%');
            $data->orWhere('stp_dest_type.desttype','like','%'.$this->request->get('condition').'%');
        }
        //ORDER BY
        if($this->request->get('dir')){
            $data->orderBy($this->request->get('dir'), $this->request->get('sort'));
        }else{
            $data->orderBy('stp_dest.destname', 'DESC');
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
        {   $images = array();
            $value->all_quota = intval($value->loc_quota) + intval($value->int_quota);
            foreach($value->images as $i => $item)
            {   
                $dt = new \stdClass;
                $url = explode('.',$item->destphoto);
                $extension = end($url);
                $filename = substr($item->destphoto, 0, strpos($item->destphoto, '.'.$extension));
                $item->destphoto_path ='http://'.$_SERVER['SERVER_NAME'].'/public/images/'.$filename.'/'.$extension;
                
            }
            
            if($value->destphoto_cover){
                $url = explode('.',$value->destphoto_cover);
                $extension = end($url);
                $filename = substr($value->destphoto_cover, 0, strpos($value->destphoto_cover, '.'.$extension));
                $value->destphoto_cover_path = 'http://'.$_SERVER['SERVER_NAME'].'/public/images/'.$filename.'/'.$extension;
            }else{
                $value->destphoto_cover_path = 'http://'.$_SERVER['SERVER_NAME'].'/public/default/default-photo/png';
            }
            
             $quota = BillingDetail::whereDate('ticketdatefrom','=',$this->request->get('ticketdatefrom'))
                ->where('destid', '=',$value->destid)
                ->select(
                    'ticketdatefrom',
                    // DB::raw('count(billing_id) as total_booked'),
                    DB::raw('(sum(loc_qty_18above) + sum(loc_qty_18below)) as loc_booked'),
                    DB::raw('(sum(int_qty_18above) + sum(int_qty_18below)) as int_booked')
                )
                ->groupBy('ticketdatefrom')->first();
            if($quota){
                 $value->available_quota = (intval($value->all_quota)) - (intval($quota->loc_booked) + intval($quota->int_booked));
            }else{
                $value->available_quota =$value->all_quota;
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
     *   path="/api/admin/destination/manage",
     *   summary="Manage Destination",
     *   tags={"Setup Destination (BackOffice)"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *               @OA\Property(property="id", type="string"),
     *               @OA\Property(property="destid", type="string"),
     *               @OA\Property(property="destname", type="string"),
     *               @OA\Property(property="description", type="string"),
     *               @OA\Property(property="desttype", type="string"),
     *               @OA\Property(property="loc_price_18above", type="string"),
     *               @OA\Property(property="loc_price_18below", type="string"),
     *               @OA\Property(property="int_price_18above", type="string"),
     *               @OA\Property(property="int_price_18below", type="string"),
     *               @OA\Property(property="loc_quota", type="string"),
     *               @OA\Property(property="int_quota", type="string"),
     *               @OA\Property(property="status", type="string"),
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
            'destname' => 'required|string'
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
                $data = Destination::find(intval($this->request->input('id')));
                foreach($this->request->all() as $key => $value){
                    if($key != 'prices' && $key != 'details'){
                        $data->{$key} = $value;
                    }
                }
                $data->save();
                $delete = DestinationPrice::where('destid','=',$this->request->input('destid'))->delete();
                foreach($this->request->input('prices') as $k => $v){
                    $prop = array();
                    $prop['destid'] = $data->destid;
                    $prop['price_type'] = $v['price_type'];
                    $prop['loc_price_18above'] = $v['loc_price_18above'];
                    $prop['loc_price_18below'] = $v['loc_price_18below'];
                    $prop['int_price_18above'] = $v['int_price_18above'];
                    $prop['int_price_18below'] = $v['int_price_18below'];
                    $prop['createdate'] = date('Y-m-d H:i:s');
                    $prop['updateid'] = 1;
                    $query = DestinationPrice::insert($prop);
                }

                foreach($this->request->input('details') as $key => $val){
                    $prop2 = array();
                    $prop2['destid'] = $data->destid;
                    $prop2['destphoto'] = $val['destphoto'];
                    $prop2['status'] = 1;
                    $prop2['createdate'] = date('Y-m-d H:i:s');
                    $prop2['createid'] = 1;
                    $query = DestinationImage::insert($prop2);
                }
            }else{
                $data = new Destination;
                foreach($this->request->all() as $key => $value){
                    if($key != 'prices' && $key != 'id' && $key != 'details'){
                        $data->{$key} = $value;
                    }
                }
                $data->destid = $this->GenerateNumberTrans('SB','destid',2,3);
                $data->createdate = date('Y-m-d H:i:s');
                $data->updateid = 1;
                if($data->save())
                {   
                    foreach($this->request->input('prices') as $k => $v){
                        $prop = array();
                        $prop['destid'] = $data->destid;
                        $prop['price_type'] = $v['price_type'];
                        $prop['loc_price_18above'] = $v['loc_price_18above'];
                        $prop['loc_price_18below'] = $v['loc_price_18below'];
                        $prop['int_price_18above'] = $v['int_price_18above'];
                        $prop['int_price_18below'] = $v['int_price_18below'];
                        $prop['createdate'] = date('Y-m-d H:i:s');
                        $prop['updateid'] = 1;
                        $query = DestinationPrice::insert($prop);
                    }

                    foreach($this->request->input('details') as $key => $val){
                        $prop2 = array();
                        $prop2['destid'] = $data->destid;
                        $prop2['destphoto'] = $val['destphoto'];
                        $prop2['status'] = 1;
                        $prop2['createdate'] = date('Y-m-d H:i:s');
                        $prop2['createid'] = 1;
                        $query = DestinationImage::insert($prop2);
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

    public function GenerateNumberTrans($Faktur,$Field,$Digit,$Len){
        $number = Destination::select(DB::raw('MAX(RIGHT('.$Field.','.$Len.')) as Increment'))
                    ->whereRaw("LEFT(".$Field.",".$Digit.") = '".$Faktur."'")
                    ->first();

        $numb = intval($number->Increment);
        $increment = $numb+1;
        $newFaktur = $Faktur.str_pad((string)$increment,$Len,"0", STR_PAD_LEFT);
        return $newFaktur;
    }
    
    /**
     * @OA\Post(
     *   path="/api/admin/destination/delete",
     *   summary="Delete Destination",
     *   tags={"Setup Destination (BackOffice)"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *               @OA\Property(property="destid", type="string"),
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
            $master = Destination::where('destid','=',$this->request->input('destid'))->delete();
            $price = DestinationPrice::where('destid','=',$this->request->input('destid'))->delete();
            $res['code'] = 201;
            $res['message'] = 'Data Success Created.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }

    }
    
    public function delete_images()
    {
        $validator = Validator::make($this->request->all(), [
            'id' => 'required|string',
            'destphoto' => 'required|string'
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
            $master = DestinationImage::where('id','=',$this->request->input('id'))->delete();
            $file = env('IMG_PATH').$this->request->input('destphoto');
            if(is_file($file)) {
                unlink($file);
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

    public function upload()
    {
        try {
            if ($this->request->hasFile('images')) {
                $allowedfileExtension=['jpg','jpeg','png','JPG'];
                $files = $this->request->file('images');
                foreach($files as $file)
                {
                    $filename = $file->getClientOriginalName();
                    $path = storage_path();
                    $path2 = env('IMG_PATH');
                    $extension = $file->getClientOriginalExtension();
                    $check=in_array($extension,$allowedfileExtension);
                    if($check)
                    {
                        // $file->move($path."/images/destination", $filename);
                        $file->move($path2, $filename);
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
    
    public function upload_cover()
    {
        ini_set("memory_limit", "100M");
        ini_set('upload_max_filesize','5MB');
        ini_set('post_max_size','5MB');
        try {
            // var_dump($this->request->hasFile('cover'));
            // if ($this->request->hasFile('cover')) {
                $allowedfileExtension=['jpg','jpeg','png','JPG'];
                $file = $this->request->file('cover');
                $filename = $file->getClientOriginalName();
                $path = storage_path();
                $path2 = env('IMG_PATH');
                $extension = $file->getClientOriginalExtension();
                $check=in_array($extension,$allowedfileExtension);
                if($check)
                {
                    $file->move($path2, $filename);
                }
            // }
            
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

<?php
namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use IPay88\Payment\Request as IPay88;
use IPay88\Payment\Response as IPay88Res;
use  App\Member;
use  App\Billing;
use  App\BillingDetail;
use  App\BillingVisitor;
use  App\Cart;
use  App\CartDetail;
use  App\Destination;
use  App\DestinationType;
use  App\DestinationPrice;
use  App\DestinationDetail;
use  App\DestinationImage;
use  App\Ticket;


class  OrderController extends Controller
{
    private $request;
    protected $_merchantCode;
	protected $_merchantKey;

    public function __construct( Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->middleware('auth:member');
        $this->request = $request;
       $this->_merchantCode = 'M16043'; //MerchantCode confidential
		$this->_merchantKey = 'EeshUGtg7P'; //MerchantKey confidential
    }


    /**
     * @OA\Get(
     *   path="/api/member/order/type",
     *   summary="Get Destination",
     *   tags={"ORDER"},
     *   @OA\Parameter(
     *       in ="header",
     *       name="Authorization",
     *       required=true,
     *       description="Bearer {access-token}",
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
    public function type()
    {
        $data = DestinationType::orderBy('desttype', 'ASC');
        
        $start = $this->request->get('offset');
        $limit = $this->request->get('limit');
        $data->offset($start);
        $data->limit($limit);
        $grid = $data->get();
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['data'] = $grid;
        return response()->json($res, 200);
    }

    /**
     * @OA\Get(
     *   path="/api/member/order/destination",
     *   summary="Get Destination",
     *   tags={"ORDER"},
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
     *       name="id_type",
     *       required=true,
     *       description="Filter Berdasarkan Type Destination",
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
        $data = Destination::with(['images', 'prices'])
                ->leftJoin('stp_dest_type', function($join){
                    $join->on('stp_dest.desttype','=','stp_dest_type.id');
                })
                ->select('stp_dest.*', 'stp_dest_type.desttype as dest_type')
                ->where('stp_dest.desttype','=',$this->request->get('id_type'))
                ->where('stp_dest.status','=','1');

        if($this->request->get('search')){
            $data->where('destname','like','%'.$this->request->get('search').'%');
        }
        
        $start = $this->request->get('offset');
        $limit = $this->request->get('limit');
        $data->offset($start);
        $data->limit($limit);
        $grid = $data->get();
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['data'] = $grid;
        return response()->json($res, 200);
    }
    
    /**
     * @OA\Get(
     *   path="/api/member/order/destination/id",
     *   summary="Get Destination",
     *   tags={"ORDER"},
     *   @OA\Parameter(
     *       in ="header",
     *       name="Authorization",
     *       required=true,
     *       description="Bearer {access-token}",
     *       @OA\Schema(
     *          type="string"
     *       )
     *    ),
     *     @OA\Parameter(
     *       in ="query",
     *       name="destid",
     *       required=true,
     *       description="ID DESTINATION",
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
     *       name="id_type",
     *       required=true,
     *       description="Filter Berdasarkan Type Destination",
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
    public function by_id()
    {
        $data = Destination::with(['images', 'prices'])
                ->leftJoin('stp_dest_type', function($join){
                    $join->on('stp_dest.desttype','=','stp_dest_type.id');
                })
                ->select('stp_dest.*', 'stp_dest_type.desttype as dest_type')
                ->where('stp_dest.desttype','=',$this->request->get('id_type'))
                ->where('stp_dest.destid','=',$this->request->get('destid'))
                ->where('stp_dest.status','=','1');

        if($this->request->get('search')){
            $data->where('destname','like','%'.$this->request->get('search').'%');
        }
        
        $start = $this->request->get('offset');
        $limit = $this->request->get('limit');
        $data->offset($start);
        $data->limit($limit);
        $grid = $data->get();
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['data'] = $grid;
        return response()->json($res, 200);
    }

    /**
     * @OA\Post(
     *   path="/api/member/order/process",
     *   summary="Manage Order",
     *   tags={"ORDER"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *               @OA\Property(property="first_name", type="string"),
     *               @OA\Property(property="last_name", type="string"),
     *               @OA\Property(property="company_name", type="string"),
     *               @OA\Property(property="country", type="string"),
     *               @OA\Property(property="address", type="string"),
     *               @OA\Property(property="city", type="string"),
     *               @OA\Property(property="postcode", type="string"),
     *               @OA\Property(property="email", type="string"),
     *               @OA\Property(property="phone", type="string"),
     *               @OA\Property(property="total_pax", type="string"),
     *               @OA\Property(property="total_cost", type="string"),
     *               @OA\Property(
     *                  property="detail",
     *                  type="array",
     *                  @OA\Items(
     *                       type="object",
     *                       @OA\Property(property="destid", type="string"),
     *                       @OA\Property(property="ticketdatefrom", type="string"),
     *                       @OA\Property(property="ticketdateto", type="string"),
     *                       @OA\Property(property="loc_qty_18above", type="string"),
     *                       @OA\Property(property="loc_qty_18below", type="string"),
     *                       @OA\Property(property="int_qty_18above", type="string"),
     *                       @OA\Property(property="int_qty_18below", type="string")
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="visitor",
     *                  type="array",
     *                  @OA\Items(
     *                       type="object",
     *                       @OA\Property(property="visitor_name", type="string"),
     *                       @OA\Property(property="id_number", type="string"),
     *                       @OA\Property(property="email", type="string"),
     *                       @OA\Property(property="phone", type="string")
     *                  )
     *              )
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
            
            $cek_quota = BillingDetail::leftJoin('trx_billing',function($join){
                $join->on('trx_billing.billing_id', '=', 'trx_billing_dtl.billing_id');
            })
                ->whereDate('trx_billing_dtl.ticketdatefrom', '=', $this->request->input('detail')[0]['ticketdatefrom'])
                ->where('trx_billing.pr_holder','=', 2)
                ->select(
                    DB::raw('count(trx_billing_dtl.billing_id) as booked')
                )->first();
            $cek_destid = Destination::where('destid','=',$this->request->input('detail')[0]['destid'])->first();
            
            if($cek_quota->booked == $cek_destid->loc_quota)
            {
                $res['code'] = 200;
                $res['data'] = null;
                $res['message'] = "Quota full . order can't be process";
            }
            $data = new Billing;
            foreach($this->request->all() as $key => $value){
                if($key != 'detail' && $key != 'visitor' ){
                    $data->{$key} = $value;
                }
            }
           
            $check_member = DB::table('stp_member')->where('email', '=', $this->request->input('email'))->first();
            if(empty($check_member))
            {
                $member = new Member;
                foreach($this->request->all() as $key => $value){
                    if($key != 'detail' && $key != 'visitor' && $key != 'pr_holder' && $key != 'total_pax' && $key != 'total_cost'){
                        $member->{$key} = $value;
                    }
                }

                $member->buyerid = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 5, 5);
                $member->password = (string) random_int(10, 999999);
                $member->createdate = date('Y-m-d');
                $member->updateid = 1;
                $member->status = 1;
                if($member->save()){
                    $data->buyerid = $member->buyerid;
                }
            }else{
                $data->buyerid = $check_member->buyerid;
            }
            
            // $data->billing_id = '#'.substr(str_shuffle('0123456789'), 5, 5);
            $data->billing_id = '#'.substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 8, 8);
            $data->total_pax = $this->request->input('total_pax');
            $data->total_cost = $this->request->input('total_cost');
            $data->status_approved = $this->request->input('pr_holder') == 1? 1:2;
            $data->status = 1;
            if($data->save())
            {   
                $DeleteCart = Cart::where('buyerid','=',$data->buyerid)->delete();
                $DeleteCartDetail = CartDetail::where('buyerid','=',$data->buyerid)->delete();
                $details = $this->check_billing_details($this->request->input('detail'));
                foreach($details as $k_det => $detail){
                    $prop = array();
                    $prop['billing_id'] = $data->billing_id;
                    $prop['buyerid'] = $data->buyerid;
                    $prop['destid'] = $detail['destid'];
                    $prop['ticketdatefrom'] = $detail['ticketdatefrom'];
                    $prop['ticketdateto'] = $detail['ticketdateto'];
                    $prop['loc_qty_18above'] = $detail['loc_qty_18above'];
                    $prop['loc_qty_18below'] = $detail['loc_qty_18below'];
                    $prop['int_qty_18above'] = $detail['int_qty_18above'];
                    $prop['int_qty_18below'] = $detail['int_qty_18below'];
                    $prop['createdate'] = date('Y-m-d H:i:s');
                    $prop['status'] = 1;
                    $query = BillingDetail::insert($prop);
                    
                    $prop2 = array();
                    foreach($detail['visitor'] as $k => $visitor){
                        $prop2['billing_id'] = $data->billing_id;
                        $prop2['destid'] = $prop['destid'];
                        $prop2['buyerid'] = $data->buyerid;
                        $prop2['ticketdatefrom'] = $prop['ticketdatefrom'];
                        $prop2['ticketdateto'] = $detail['ticketdateto'];
                        $prop2['visitor_name'] = $visitor['visitor_name'];
                        $prop2['id_number'] = $visitor['id_number'];
                        $prop2['email'] = $visitor['email'];
                        $prop2['phone'] = $visitor['phone'];
                        $prop2['age'] = $visitor['age'];
                        $prop2['nationality'] = $visitor['nationality'];
                        $prop2['gender'] = $visitor['gender'];
                        $prop2['status'] = 1;
                        $query = BillingVisitor::insert($prop2);
                    }
                }
               
            }
            
            $array = array(
                'billing_id'=>$data->billing_id,
                'billing_no' => str_replace('#','',$data->billing_id),
                'total_cost' =>$data->total_cost
            );
            Mail::send('emails.billing', $array, function($message) use ($data){
                $message->to($data->email, $data->first_name)
                    ->cc(['e_support@sabahparks.org.my'])
                    ->bcc(['inv_repository@terazglobal.com.my'])
                    ->subject('INVOICE No '.$data->billing_id);
                $message->from('noreply@booking.sabahparks.org.my','SABAH PARKS');
            });

            if (Mail::failures()) {
                $res['code'] = 409;
                $res['message'] = 'Email failed to send';
                // $res['data'] = $data['token'];
                return response()->json($res, 409);
            }
            
            $res['code'] = 201;
            $res['data'] = $this->request->all();
            $res['billing_id'] = $data->billing_id;
            $res['message'] = 'Data Success Created.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }

    }

    public function get_invoice()
    {
        $billing_id = '#'.$this->request->input('billing_id');
        $data = Billing::with(['details'])
                ->where('billing_id','=',$billing_id)
                ->get();
        foreach($data as $key => $value)
        {
            foreach($value->details as $k => $val)
            {
                $val->{'visitors'} = DB::table('trx_billing_visitor')->where('billing_id','=',$billing_id)->get();
                $val->{'destination'} = Destination::with(['prices','images'])->where('destid','=',$val->destid)->get();
            }
            
        }
        if(count($data) == 0){
            $data = null;
        }
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['data'] = $data;
        return response()->json($res, 200);
    }

    public function manage_cart()
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
            $member=  Auth::guard('member')->user();
            $prop =new Cart;
            $prop->chatid = 'CRT/'.str_pad(($prop->max('id') + 1), 3, 0, STR_PAD_LEFT);
            $prop->buyerid =$member->buyerid;
            $prop->destid = $this->request->input('destid');
            $prop->ticketdatefrom = $this->request->input('ticketdatefrom');
            $prop->ticketdateto = $this->request->input('ticketdateto');
            $prop->loc_qty_18above = $this->request->input('loc_qty_18above');
            $prop->loc_qty_18below = $this->request->input('loc_qty_18below');
            $prop->int_qty_18above = $this->request->input('int_qty_18above');
            $prop->int_qty_18below = $this->request->input('int_qty_18below');
            $prop->pr_holder = $this->request->input('pr_holder');
            $prop->status_approved = 1;
            $prop->createdate = date('Y-m-d H:i:s');
            $prop->status = 1;
            if($prop->save())
            {
                foreach($this->request->input('detail') as $k => $visitor){
                    $prop2['buyerid'] = $prop->buyerid;
                    $prop2['destid'] = $prop->destid;
                    $prop2['ticketdatefrom'] = $prop->ticketdatefrom;
                    $prop2['ticketdateto'] = $prop->ticketdateto;
                    $prop2['visitor_name'] = $visitor['visitor_name'];
                    $prop2['id_number'] = $visitor['id_number'];
                    $prop2['email'] = $visitor['email'];
                    $prop2['phone'] = $visitor['phone'];
                    $prop2['nationality'] = $visitor['nationality'];
                    $prop2['gender'] = $visitor['gender'];
                    $prop2['age'] = $visitor['gender'];
                    $prop2['status'] = 1;
                    $query = CartDetail::insert($prop2);
                }
            }
            
            $res['code'] = 201;
            $res['data'] = $this->request->all();
            $res['buyerid'] = $prop->buyerid;
            $res['message'] = 'Data Success Created.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }

    }

    public function get_cart()
    {
        $cart_id = $this->request->input('buyerid');
        // $datamember = Auth::guard('member')->user();
        $data = Cart::with(['details','destination','destination_image'])
                ->where('buyerid','=',$cart_id)
                ->get();
        $data = $this->check_cart_details($data);
        foreach($data as $key => $value)
        {
            foreach($value->destination_image as $k => $val)
            {
                // $destphoto = $value->destination[0]->destphoto_cover;
                $destphoto = $val->destphoto;
                $img = explode('.',$destphoto);
                $extension = end($img);
                $filename = substr($destphoto, 0, strpos($destphoto, '.'.$extension));
                $val->destphoto_path = 'http://'.$_SERVER['SERVER_NAME'].'/public/images/'.$filename.'/'.$extension;
            }
        }
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['data'] = $data;
        return response()->json($res, 200);
    }
    
    public function delete_cart()
    {
         $validator = Validator::make($this->request->all(), [
            'buyerid' => 'required|string',
            'ticketdatefrom' => 'required|string',
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
            $chatid = $this->request->input('chatid');
            $DeleteCart = Cart::where('buyerid','=',$this->request->input('buyerid'))
            ->whereDate('ticketdatefrom','=',$this->request->input('ticketdatefrom'))
            ->where('destid','=',$this->request->input('destid'))
            ->delete();
            $DeleteCartDetail = CartDetail::where('buyerid','=',$this->request->input('buyerid'))
            ->whereDate('ticketdatefrom','=',$this->request->input('ticketdatefrom'))
            ->where('destid','=',$this->request->input('destid'))
            ->delete();
            $res['code'] = 201;
            $res['data'] = $this->request->all();
            $res['message'] = 'Data Success Created.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }
        
    }
    
    public function cek_quota($date){
         $query_billing = BillingDetail::whereDate('ticketdatefrom', '=', $date)
                ->select(
                    DB::raw('count(billing_id) as booked')
                );
         $query_cart = Cart::whereDate('ticketdatefrom', '=', $date)
                ->select(
                    'ticketdatefrom',
                    DB::raw('count(billing_id) as booked')
                )
                ->groupBy('ticketdatefrom')->get();
    }
    
    public function payment()
    {
        try {
            
            $request = new IPay88($this->_merchantKey);
            $this->_data = array(
                'merchantCode' => $request->setMerchantCode($this->_merchantCode),
                'paymentId' =>  $request->setPaymentId(1),
                'refNo' => $request->setRefNo('EXAMPLES03813'),
                'amount' => $request->setAmount('0.50'),
                'currency' => $request->setCurrency('MYR'),
                'prodDesc' => $request->setProdDesc('Testing'),
                'userName' => $request->setUserName('Your name'),
                'userEmail' => $request->setUserEmail('prastiyo.beka12@gmail.com'),
                'userContact' => $request->setUserContact('0123456789'),
                'remark' => $request->setRemark('Some remarks here..'),
                'lang' => $request->setLang('UTF-8'),
                'signature' => $request->getSignature(),
                // 'responseUrl' => $request->setResponseUrl('https://sabahpark.datanonisoeroso.com/?s=payment_confirmation'),
                'backendUrl' => $request->setBackendUrl('http://api.sabahparks.masuk.id/public/response')
                );
                //  var_dump($request);
                //  die();

            IPay88::make($this->_merchantKey, $this->_data);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }

    }

    public function response_payment()
	{	
	    $response = (new IPay88Res)->init($this->_merchantCode);
		$res['code'] = 200;
        $res['message'] = $response;
        return response()->json($res, 200);
	}
	
	public function get_history()
    {
        $member =  $data = Auth::guard('member')->user();
        $data = BillingDetail::with(['destination_image','billing','visitor','destination','review'])
                ->where('buyerid','=',$member->buyerid)
                ->orderBy('ticketdatefrom','ASC')
                ->get();
        $path = env('IMG_PATH');
        foreach($data as $key => $value)
        {
            $value->payment_link = 'http://'.$_SERVER['SERVER_NAME'].'/public/payment';
            $img = explode('.', $value->destination['destphoto_cover']);
            $extension = end($img);
            $filename = substr($value->destination['destphoto_cover'], 0, strpos($value->destination['destphoto_cover'], '.'.$extension));
            $value->destination['destination_cover'] = 'http://'.$_SERVER['SERVER_NAME'].'/public/images/'.str_replace(' ','%20',$filename).'/'.$extension;
            foreach($value->destination_image as $k => $val)
            {
                $image_slider = explode('.', $val->destphoto);
                $extension = end($image_slider);
                $filename = substr($val->destphoto, 0, strpos($val->destphoto, '.'.$extension));
                $val->destphoto_path = 'http://'.$_SERVER['SERVER_NAME'].'/public/images/'.str_replace(' ','%20',$filename).'/'.$extension;
            }
            
            $tickets = array();
            foreach($value->visitor as $t =>  $tor)
            {
                $obj = Ticket::where('billing_id','=',$tor->billing_id)
                                ->where('buyerid','=',$tor->buyerid)
                                ->where('destid','=',$tor->destid)
                                ->where('visitor_name','=',$tor->visitor_name)
                                ->first();
               if($obj){
                   $obj->download_link = 'http://'.$_SERVER['SERVER_NAME'].'/public/download/ticket?ticket_id='.$obj->ticket_id;
                   array_push($tickets, $obj);
               }
            }
            
            $value->ticket = $tickets; 
            
            // foreach($value->ticket as $t =>  $tor)
            // {
            //     $value->download_link = 'http://'.$_SERVER['SERVER_NAME'].'/public/download/ticket?ticket_id='.$tor->ticket_id;
            // }
        }
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['data'] = $data;
        return response()->json($res, 200);
    }
    
    function check_billing_details($array){
        $results = array();
        $keys = array();
        $key_tickedatefrom = array();
        
        foreach ($array as $key => $val){
            if($key == 0){
                array_push($results, $val);
                array_push($keys, $val['destid'].'-'.$val['ticketdatefrom']);
            }else{
             if(!in_array($val['destid'].'-'.$val['ticketdatefrom'], $keys))
                {
                    array_push($results, $val);
                    array_push($keys, $val['destid'].'-'.$val['ticketdatefrom']);
                }
            }
        }
        return $results;
    }
    
    function check_harga($array,$buyerid){
        $price = Cart::leftJoin('stp_dest','stp_dest.destid','=','trx_cart.destid')
        ->where('buyerid','=',$buyerid)
        ->select(
            'buyerid',
            DB::raw('SUM(trx_cart.loc_qty_18above) as loc_qty_18above'),
            DB::raw('SUM(trx_cart.loc_qty_18below) as loc_qty_18below'),
            DB::raw('SUM(trx_cart.int_qty_18above) as int_qty_18above'),
            DB::raw('SUM(trx_cart.int_qty_18below) as int_qty_18below'),
            DB::raw('(SUM(trx_cart.loc_qty_18above) * MAX(stp_dest.loc_price_18above)) as loc_price_18above'),
            DB::raw('(SUM(trx_cart.loc_qty_18below) * MAX(stp_dest.loc_price_18below)) as loc_price_18below'),
            DB::raw('(SUM(trx_cart.int_qty_18above) * MAX(stp_dest.int_price_18above)) as int_price_18above'),
            DB::raw('(SUM(trx_cart.int_qty_18below) * MAX(stp_dest.int_price_18below)) as int_price_18below')
        )->groupBy('buyerid')->first();
        
        $results = array(
            "total_tax" => ($price->loc_qty_18above + $price->loc_qty_18below + $price->int_qty_18above + $price->int_qty_18below),
            "total_cost" => ($price->loc_price_18above + $price->loc_price_18below + $price->int_price_18above + $price->int_price_18below)
        );
        return $results;
    }
    
    function check_cart_details($array){
        $results = array();
        $keys = array();
        $key_tickedatefrom = array();
        
        foreach ($array as $key => $val){
            if($key == 0){
                $price = Cart::where('buyerid','=',$val['buyerid'])
                ->where('destid','=',$val['destid'])
                ->where('ticketdatefrom','=',$val['ticketdatefrom'])
                ->select(
                    'destid',
                    DB::raw('SUM(loc_qty_18above) as loc_qty_18above'),
                    DB::raw('SUM(loc_qty_18below) as loc_qty_18below'),
                    DB::raw('SUM(int_qty_18above) as int_qty_18above'),
                    DB::raw('SUM(int_qty_18below) as int_qty_18below')
                )->groupBy('destid')->first();
                
                $val['loc_qty_18above'] = intval($price['loc_qty_18above']);
                $val['loc_qty_18below'] = intval($price['loc_qty_18below']);
                $val['int_qty_18above'] = intval($price['int_qty_18above']);
                $val['int_qty_18below'] = intval($price['int_qty_18below']);

                array_push($results, $val);
                array_push($keys, $val['destid'].'-'.$val['ticketdatefrom']);
            }else{
             if(!in_array($val['destid'].'-'.$val['ticketdatefrom'], $keys))
                {
                    $price = Cart::where('buyerid','=',$val['buyerid'])
                    ->where('destid','=',$val['destid'])
                    ->where('ticketdatefrom','=',$val['ticketdatefrom'])
                    ->select(
                        'destid',
                        DB::raw('SUM(loc_qty_18above) as loc_qty_18above'),
                        DB::raw('SUM(loc_qty_18below) as loc_qty_18below'),
                        DB::raw('SUM(int_qty_18above) as int_qty_18above'),
                        DB::raw('SUM(int_qty_18below) as int_qty_18below')
                    )->groupBy('destid')->first();
                    
                    $val['loc_qty_18above'] = intval($price['loc_qty_18above']);
                    $val['loc_qty_18below'] = intval($price['loc_qty_18below']);
                    $val['int_qty_18above'] = intval($price['int_qty_18above']);
                    $val['int_qty_18below'] = intval($price['int_qty_18below']);
                    array_push($results, $val);
                    array_push($keys, $val['destid'].'-'.$val['ticketdatefrom']);
                }
            }
        }
        return $results;
    }

}

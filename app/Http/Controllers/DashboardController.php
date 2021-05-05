<?php
namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use IPay88\Payment\Request as IPay88;
use IPay88\Payment\Response as IPay88Res;
use App\Destination;
use App\DestinationType;
use App\DestinationPrice;
use App\BillingDetail;
use App\Blog;
use App\Ticket;
use App\Receipt;
use CustomHelper;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class  DashboardController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->request = $request;
    }
    
    public function banner_promo()
    {
        $data = Blog::whereIn('type',array(2,4))
                ->where('status','=',1)
                ->orderBy('createdate', 'DESC');
                
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
            if(!file_exists (storage_path().'/images/gallery/'.$value->path_cover))
            {
                $value->path = 'http://'.$_SERVER['SERVER_NAME'].'/public/default/default-photo/png';
            }else{
                $url = explode('.',$value->path_cover);
                $extension = end($url);
                $filename = substr($value->path_cover, 0, strpos($value->path_cover, '.'.$extension));
                if(count($url) > 1){
                    $value->path = 'http://'.$_SERVER['SERVER_NAME'].'/public/images/gallery/'.$filename.'/'.$extension;
                }else{
                    $value->path = 'http://'.$_SERVER['SERVER_NAME'].'/public/default/default-photo/png';
                }
            }
            
        }
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['data'] = $grid;
        return response()->json($res, 200);
    }

    public function news_event()
    {
        $data = Blog::whereIn('type',array(1,3))
                ->where('status','=',1)
                ->orderBy('createdate', 'DESC');
                
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
            if(!file_exists (storage_path().'/images/gallery/'.$value->path))
            {
                $value->path = 'http://'.$_SERVER['SERVER_NAME'].'/public/default/default-photo/png';
            }else{
                $url = explode('.',$value->path_cover);
                $extension = end($url);
                $filename = substr($value->path_cover, 0, strpos($value->path_cover, '.'.$extension));
                if(count($url) > 1){
                    $value->path = 'http://'.$_SERVER['SERVER_NAME'].'/public/images/gallery/'.$filename.'/'.$extension;
                }else{
                    $value->path = 'http://'.$_SERVER['SERVER_NAME'].'/public/default/default-photo/png';
                }
            }
            
        }
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['data'] = $grid;
        return response()->json($res, 200);
    }

    public function top_destination()
    {
        $data =  Destination::with(['images', 'prices']) 
                ->leftJoin('trx_billing_dtl', function($join){
                    $join->on('stp_dest.destid','=','trx_billing_dtl.destid');
                })
                ->leftJoin('stp_dest_type', function($join){
                    $join->on('stp_dest.desttype','=','stp_dest_type.id');
                })
                ->select(
                    'stp_dest.destid', 
                    'stp_dest.id',
                    'stp_dest.destname',
                    'stp_dest.description',
                    'stp_dest.loc_price_18above',
                    'stp_dest.loc_price_18below',
                    'stp_dest.int_price_18above',
                    'stp_dest.int_price_18below',
                    'stp_dest.loc_quota',
                    'stp_dest.int_quota',
                    'stp_dest.desttype',
                    'stp_dest.term',
                    'stp_dest.status',
                    'stp_dest.createdate',
                    'stp_dest.updateid',
                    DB::raw('COUNT(trx_billing_dtl.billing_id) as total'))
                ->where('stp_dest.status','=','1')
                // ->whereNotNull('trx_billing_dtl.destid')
                ->orderBy(DB::raw('count(trx_billing_dtl.billing_id)'),'DESC')
                ->groupBy('trx_billing_dtl.destid', 'stp_dest.id',
                    'stp_dest.destname',
                    'stp_dest.description',
                    'stp_dest.loc_price_18above',
                    'stp_dest.loc_price_18below',
                    'stp_dest.int_price_18above',
                    'stp_dest.int_price_18below',
                    'stp_dest.loc_quota',
                    'stp_dest.int_quota',
                    'stp_dest.desttype',
                    'stp_dest.term',
                    'stp_dest.status',
                    'stp_dest.createdate',
                    'stp_dest.updateid')->limit(6);

        
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
            foreach($value->images as $k => $val)
            {
                $img = explode('.',$val->destphoto);
                $extension = end($img);
                $filename = substr($val->destphoto, 0, strpos($val->destphoto, '.'.$extension));
                $val->destphoto = 'http://'.$_SERVER['SERVER_NAME'].'/public/images/'.$filename.'/'.$extension;
                
            }
        }
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['data'] = $grid;
        return response()->json($res, 200);
    }
    

    
    public function get_event()
    {
        $month = $this->request->input('month');
        $end_date = strtotime(date('Y-'.$month.'-d'));
        $format_date = strtotime(date('Y-m-t',$end_date));
        $last_date = date('d',$format_date);
        if($month != date('m')){
            $current_date = strtotime(date('Y-'.$month.'-01'));
            $dt = 1; 
        }else{
            $current_date = strtotime(date('Y-'.$month.'-d'));
            $dt = date('d');
            
        }
        $count_day = abs($end_date - $current_date);  
        $numberDays = $count_day/86400;
        $numberDays = intval($numberDays);
        $array = array();
        for($i = intval($dt);$i <= $last_date;$i++)
        {
            $obj = new \stdClass;
            $obj->date = date('Y-'.str_pad($month,2,'0',STR_PAD_LEFT).'-'.str_pad($i,2,'0',STR_PAD_LEFT));
            $obj->booked = 0;
            array_push($array, $obj);
        }

        

        $query = BillingDetail::whereRaw('MONTH(ticketdatefrom) = '.$month)
                ->select(
                    'ticketdatefrom',
                    DB::raw('count(billing_id) as booked')
                )
                ->groupBy('ticketdatefrom')->get();
        foreach ($query as $key => $value) {
            $timestamp = strtotime($value->ticketdatefrom);
            $day = date('d', $timestamp);
            $array[$day-1]->booked = $value->booked;
        }
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['data'] = $array;
        return response()->json($res, 200);
    }
    
    public function payment()
    {
        $billing_id = '#'.$this->request->input('billing_id');
        $billing = BillingDetail::with(['destination', 'destination_detail','billing','visitor'])
        ->where('billing_id','=',$billing_id)->first();
        $trans_id = 'T0479278'.rand(10,99);
	    $receipt = new Receipt;
	    $receipt->receipt_id = '';
	    $receipt->billing_id = $billing_id;
	    $receipt->bank_name = 'MANDIRI';
	    $receipt->bank_no = $billing_id;
	    $receipt->bank_acc = $billing_id;
	    $receipt->remark = '1';
	    $receipt->amount = $billing->billing->total_cost;
	    $receipt->createdate = date('Y-m-d H:i');
        
	    if($receipt->save()){
	        foreach($billing->visitor as $key => $value)
	        {
	             $ticket = new Ticket;
        	    $ticket->ticket_id = $trans_id.rand(10,9999);
        	    $ticket->receipt_id = $receipt->receipt_id;
        	    $ticket->billing_id = $billing_id;
        	    $ticket->buyerid = $billing->billing->buyerid;
        	    $ticket->destid = $billing->destid;
        	    $ticket->ticketdatefrom = $billing->ticketdatefrom;
        	    $ticket->ticketdateto = $billing->ticketdateto;
        	    $ticket->visitor_name = $value->visitor_name;
        	    $ticket->id_number = $value->id_number;
        	    $ticket->email = $value->email;
        	    $ticket->phone = $value->phone;
        	    $ticket->nationality = $value->nationality;
        	    $ticket->gender = $value->gender;
        	    $ticket->age = $value->age;
        	    $ticket->status = '1';
        	    if($ticket->save())
        	    {
        	        QrCode::format('svg')->errorCorrection('H')->size(300)->generate($value->ticket_id,storage_path().'/qrcode'.$value->ticket_id.'.png');
                    $data = array(
                        'billing_id'=>$ticket->ticket_id,
                        'total_cost' =>$receipt->amount,
                        'destname' => $billing->destination->destname,
                        'datego'=> date('l, d F Y', strtotime($billing->ticketdatefrom)),
                        'nama_visitor' => $value->visitor_name,
                        'email'  => $value->email,
                        'phone' => $value->phone,
                        'ticket' => storage_path().'/qrcode'.$value->ticket_id.'.png'
                    );
        
        	        
        	         Mail::send('ticket.billing', $array, function($message) use ($data){
                        $message->to($data->email, $data->first_name)
                            ->subject('Ticket No '.$data->billing_id);
                        $message->from('noreply@booking.sabahparks.org.my','SABAH PARKS');
                    });
        	    }
	        }
          
            // if (Mail::failures()) {
            //     $res['code'] = 409;
            //     $res['message'] = 'Email failed to send';
            //     // $res['data'] = $data['token'];
            //     return response()->json($res, 409);
            // }
	       
	    }
	    
	    return view('payment');
	    
	    
	    
	    
        // $request = new IPay88($this->_merchantKey);
        // $this->_data = array(
        //     'merchantCode' => $request->setMerchantCode($this->_merchantCode),
        //     'paymentId' =>  $request->setPaymentId($billing_id),
        //     'refNo' => $request->setRefNo($billing_id),
        //     'amount' => $request->setAmount($billing->billing->total_cost),
        //     'currency' => $request->setCurrency('MYR'),
        //     'prodDesc' => $request->setProdDesc('Ticket Sabah Park '.$billing_id),
        //     'userName' => $request->setUserName($billing->billing->first_name.' '.$billing->billing->last_name),
        //     'userEmail' => $request->setUserEmail($billing->billing->email),
        //     'userContact' => $request->setUserContact($billing->billing->phone),
        //     'remark' => $request->setRemark('-'),
        //     'lang' => $request->setLang('UTF-8'),
        //     'signature' => $request->getSignature(),
        //     // 'responseUrl' => $request->setResponseUrl('https://sabahpark.datanonisoeroso.com/?s=payment_confirmation'),
        //     'backendUrl' => $request->setBackendUrl('https://www.api.sabahparks.masuk.id/public/response')
        //     );
        // IPay88::make($this->_merchantKey, $this->_data);
    }

    public function response_payment($billing_id)
	{
	    
// 	    $response = (new IPay88Res)->init($this->_merchantCode);
// 	    $push = CustomHelper::sendpush('Payment Status', 'Payment Successfully.', $response, array_filter($user));

//         if ($push->numberSuccess() > 0) {
//             return response()->json([
//                 'success' => true,
//                 'message' => 'Notif send',
//             ], 201);
//         }
// 		$res['code'] = 200;
//         $res['message'] = $response;
//         return response()->json($res, 200);
	}

}

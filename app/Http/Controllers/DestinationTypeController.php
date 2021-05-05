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
use App\Billing;
use App\BillingDetail;
use App\Ticket;
use App\Receipt;
use App\Member;
use CustomHelper;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Mail;


class  DestinationTypeController extends Controller
{
    private $request;
    protected $_merchantCode;
	protected $_merchantKey;

    public function __construct( Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->request = $request;
        $this->_merchantCode = 'M31589_S0001'; //MerchantCode confidential
// 		$this->_merchantKey = 'S6nAi8J1jj'; //MerchantKey confidential
        $this->_merchantKey = 'S6nAi8J1jj';
    }

    /**
     * @OA\Get(
     *   path="/public/order/type",
     *   summary="Get Destination",
     *   tags={"Public"},
     *   @OA\Parameter(
     *       in ="query",
     *       name="offset",
     *       description="index start data",
     *       @OA\Schema(
     *          type="string"
     *       )
     *    ),
     *   @OA\Parameter(
     *       in ="query",
     *       name="limit",
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
        if($this->request->get('offset') && $this->request->get('limit'))
        {
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
     * @OA\Get(
     *   path="/public/order/destination/id",
     *   summary="Get Destination",
     *   tags={"Public"},
     *    @OA\Parameter(
     *       in ="query",
     *       name="search",
     *       description="Pencarian Judul",
     *       @OA\Schema(
     *          type="string"
     *       )
     *    ),
     *    @OA\Parameter(
     *       in ="query",
     *       name="id_type",
     *       required = true,
     *       description="Filter Berdasarkan Type Destination",
     *       @OA\Schema(
     *          type="string"
     *       )
     *    ),
     *   @OA\Parameter(
     *       in ="query",
     *       name="offset",
     *       description="index start data",
     *       @OA\Schema(
     *          type="string"
     *       )
     *    ),
     *   @OA\Parameter(
     *       in ="query",
     *       name="limit",
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
        $data = Destination::with(['images', 'prices', 'review'])
                ->select('stp_dest.*')
                ->where('stp_dest.status','=','1');

        if($this->request->get('search')){
            $data->where('destname','like','%'.$this->request->get('search').'%');
        }else{
            $data->where('stp_dest.desttype','=',$this->request->get('id_type'));
        }
        
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

                $val->destphoto = 'http://'.$_SERVER['SERVER_NAME'].'/public/images/'.$img[0].'/'.$img[1];
                
            }
            
            foreach($value->review as $r => $rev)
            {
                $query = Member::where('buyerid','=',$rev->buyerid)->first();
                $rev->name = $query->first_name;
                
            }
        }
        
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
            // $obj->total_booked = 0;
            $obj->loc_booked = 0;
            $obj->int_booked = 0;
            array_push($array, $obj);
        }

        $query = BillingDetail::whereRaw('MONTH(ticketdatefrom) = '.$month)
                ->where('destid', '=',$this->request->get('destid'))
                ->select(
                    'ticketdatefrom',
                    // DB::raw('count(billing_id) as total_booked'),
                    DB::raw('(sum(loc_qty_18above) + sum(loc_qty_18below)) as loc_booked'),
                    DB::raw('(sum(int_qty_18above) + sum(int_qty_18below)) as int_booked')
                )
                ->groupBy('ticketdatefrom')->get();
        foreach ($query as $key => $value) {
            $timestamp = strtotime($value->ticketdatefrom);
            $day = intval(date('d', $timestamp));
            $cur_day = intval(date('d'));
            $int = $day - $cur_day;
            if($month != date('m')){
                // $array[$day-1]->total_booked = $value->total_booked;
                $array[$day-1]->loc_booked = intval($value->loc_booked);
                $array[$day-1]->int_booked = intval($value->int_booked);
            }else{
                if(date('Y-m-d', $timestamp) >= date('Y-m-d'))
                {
                    // $array[$int]->total_booked = $value->total_booked;
                    $array[$int]->loc_booked = intval($value->loc_booked);
                    $array[$int]->int_booked = intval($value->int_booked);
                }
            }
            
        }
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['data'] = $array;
        return response()->json($res, 200);
    }
    
//     public function payment()
//     {
//         // $billing_id = '#'.$this->request->input('billing_id');
//         // $billing = BillingDetail::with(['destination', 'destination_detail','billing'])
//         // ->where('billing_id','=',$billing_id)->first();
//         // $request = new IPay88($this->_merchantKey);
//         // $this->_data = array(
//         //     'merchantCode' => $request->setMerchantCode($this->_merchantCode),
//         //     'paymentId' =>  $request->setPaymentId($billing_id),
//         //     'refNo' => $request->setRefNo($billing_id),
//         //     'amount' => $request->setAmount(1.00),
//         //     'currency' => $request->setCurrency('MYR'),
//         //     'prodDesc' => $request->setProdDesc('Ticket Sabah Park '.$billing_id),
//         //     'userName' => $request->setUserName($billing->billing->first_name.' '.$billing->billing->last_name),
//         //     'userEmail' => $request->setUserEmail($billing->billing->email),
//         //     'userContact' => $request->setUserContact($billing->billing->phone),
//         //     'remark' => $request->setRemark('-'),
//         //     'lang' => $request->setLang('UTF-8'),
//         //     'signature' => $request->getSignature(),
//         //     // 'responseUrl' => $request->setResponseUrl('https://sabahpark.datanonisoeroso.com/?s=payment_confirmation'),
//         //     'responseUrl' => $request->setBackendUrl('https://www.api.sabahparks.masuk.id/public/response')
//         //     );
//         // // return IPay88::make($this->_merchantKey, $this->_data);
//     }

//     public function response_payment()
// 	{	
// 	    $response = (new IPay88Res)->init($this->_merchantCode);
// 	   // $push = CustomHelper::sendpush('Payment Status', 'Payment Successfully.', $response, array_filter($user));

//     //     if ($push->numberSuccess() > 0) {
//     //         return response()->json([
//     //             'success' => true,
//     //             'message' => 'Notif send',
//     //         ], 201);
//     //     }
// 		$res['code'] = 200;
//         $res['message'] = $response;
//         return response()->json($res, 200);
// 	}

    public function payment()
    {
        // $billing_id = '#'.$this->request->input('billing_id');
        // $billing = BillingDetail::with(['destination', 'destination_detail','billing','visitor'])
    //     // ->where('billing_id','=',$billing_id)->first();
    //     $billing = Billing::with(['details','visitors'])->where('billing_id','=',$billing_id)->first();
    //     $trans_id = 'T0479278'.rand(10,99);
	   // $receipt = new Receipt;
	   // $receipt->receipt_id = $trans_id;
	   // $receipt->billing_id = $billing_id;
	   // $receipt->bank_name = 'MANDIRI';
	   // $receipt->bank_no = $billing_id;
	   // $receipt->bank_acc = $billing_id;
	   // $receipt->remark = '-';
	   // $receipt->amount = $billing->total_cost;
	   // $receipt->createdate = date('Y-m-d H:i');
	   // $receipt->status = '1';
	   // if($receipt->save()){
	   //     foreach($billing->visitors as $key => $value)
	   //     {
	   //         $ticket = new Ticket;
    //     	    $ticket->ticket_id = $trans_id.rand(10,9999);
    //     	    $ticket->receipt_id = $receipt->receipt_id;
    //     	    $ticket->billing_id = $billing_id;
    //     	    $ticket->buyerid = $billing->buyerid;
    //     	    $ticket->destid = $value->destid;
    //     	    $ticket->ticketdatefrom = $value->ticketdatefrom;
    //     	    $ticket->ticketdateto = $value->ticketdateto;
    //     	    $ticket->visitor_name = $value->visitor_name;
    //     	    $ticket->id_number = $value->id_number;
    //     	    $ticket->email = $value->email;
    //     	    $ticket->phone = $value->phone;
    //     	    $ticket->nationality = $value->nationality;
    //     	    $ticket->gender = $value->gender;
    //     	    $ticket->age = $value->age;
    //     	    $ticket->status = '1';
    //     	    if($ticket->save())
    //     	    {   
    //     	        Billing::where('billing_id','=', $billing_id)->update(['status' => 2]);
        	       // if (!extension_loaded('imagick'))
                //         echo 'imagick not installed';
        	       ////QrCode::format('png')->errorCorrection('H')->size(300)->generate($ticket->ticket_id,storage_path().'/qrcode/'.$ticket->ticket_id.'.png');
        	       //var_dump($ticket->ticket_id);die();
                //     $array = array(
                //         'billing_id'=>$ticket->ticket_id,
                //         'total_cost' =>$receipt->amount,
                //         'destname' => $billing->destination->destname,
                //         'datego'=> date('l, d F Y', strtotime($billing->ticketdatefrom)),
                //         'nama_visitor' => $value->visitor_name,
                //         'email'  => $value->email,
                //         'phone' => $value->phone,
                //         'ticket' => QrCode::errorCorrection('H')->size(300)->generate($ticket->ticket_id)
                //     );
                  
        	       //  Mail::send('emails.eticket', $array, function($message) use ($ticket){
                //         $message->to($ticket->email, $ticket->first_name)
                //             ->subject('Ticket No '.$ticket->ticket_id);
                //         $message->from('noreply@booking.sabahparks.org.my','SABAH PARKS');
                //     });
                //     if (Mail::failures()) {
                //         $res['code'] = 409;
                //         $res['message'] = 'Email failed to send';
                //         // $res['data'] = $data['token'];
                //         return response()->json($res, 409);
                //     }
        	   // }
	       // }
          
            // if (Mail::failures()) {
            //     $res['code'] = 409;
            //     $res['message'] = 'Email failed to send';
            //     // $res['data'] = $data['token'];
            //     return response()->json($res, 409);
            // }
	       
	   // }
	    
	   // return view('payment');
	    
	    
	    
	    $billing_id = '#'.$this->request->get('billing_id');
	    $billing = Billing::with(['details','visitors'])->where('billing_id','=',$billing_id)->first();
        $request = new IPay88($this->_merchantKey);
        $this->_data = array(
            'merchantCode' => $request->setMerchantCode($this->_merchantCode),
            'paymentId' =>  $request->setPaymentId($billing_id),
            'refNo' => $request->setRefNo($billing_id),
            'amount' => $request->setAmount($billing->total_cost),
            'currency' => $request->setCurrency('MYR'),
            'prodDesc' => $request->setProdDesc('Ticket Sabah Park '.$billing_id),
            'userName' => $request->setUserName($billing->first_name.' '.$billing->last_name),
            'userEmail' => $request->setUserEmail($billing->email),
            'userContact' => $request->setUserContact($billing->phone),
            'remark' => $request->setRemark('-'),
            'lang' => $request->setLang('UTF-8'),
            'signature' => $request->getSignature(),
            'responseUrl' => $request->setResponseUrl('https://www.apisptix.sabahparks.org.my/public/response'),
            'backendUrl' => $request->setBackendUrl('https://www.apisptix.sabahparks.org.my/public/backend')
            );
        IPay88::make($this->_merchantKey, $this->_data);
    }
    
    public function response_payment()
    {
        $response = (new IPay88Res)->init($this->_merchantCode);
        if($response['status']){
            $billing = BillingDetail::with(['destination', 'destination_detail','billing','visitor'])
                       ->where('billing_id','=',$response['data']['RefNo'])->first();
            $receipt = new Receipt;
    	    $receipt->receipt_id = $response['data']['TransId'];
    	    $receipt->billing_id = $response['data']['RefNo'];
    	    $receipt->bank_name = $response['data']['S_bankname'];
    	    $receipt->bank_no = $response['data']['CCNo'];
    	    $receipt->bank_acc = $response['data']['CCName'];
    	    $receipt->remark = $response['data']['Remark'];
    	    $receipt->amount = $response['data']['Amount'];
    	    $receipt->createdate = date('Y-m-d H:i');
    	    $receipt->status = '1';
    	    if($receipt->save()){
    	        Billing::where('billing_id','=', $response['data']['RefNo'])->update(['status' => 2]);
    	        foreach($billing->visitor as $key => $value)
    	        {
    	            $ticket = new Ticket;
            	    $ticket->ticket_id = $response['data']['TransId'].$value['id'];
            	    $ticket->receipt_id = $response['data']['TransId'];
            	    $ticket->billing_id = $response['data']['RefNo'];
            	    $ticket->buyerid = $billing->buyerid;
            	    $ticket->destid = $value->destid;
            	    $ticket->ticketdatefrom = $value->ticketdatefrom;
            	    $ticket->ticketdateto = $value->ticketdateto;
            	    $ticket->visitor_name = $value->visitor_name;
            	    $ticket->id_number = $value->id_number;
            	    $ticket->email = $value->email;
            	    $ticket->phone = $value->phone;
            	    $ticket->nationality = $value->nationality;
            	    $ticket->gender = $value->gender;
            	    $ticket->age = $value->age;
            	    $ticket->status = '1';
            	    $ticket->save();
    	        }
    	        $billing_receipt = Billing::with(['details','visitors'])->where('billing_id','=',$response['data']['RefNo'])->first();
                foreach($billing_receipt->details as $detail)
                {
                    $detail->prices = DestinationPrice::where('destid','=',$detail->destid)->get();
                    $detail->destination = Destination::where('destid','=',$detail->destid)->first();
                }
                
    	        $invoices = array(
    	            'billing_id' => $billing_receipt->billing_id,
    	            'billing_date'=> date('d F Y, l', strtotime($billing_receipt->details[0]->ticketdatefrom)),
    	            'name' => $billing_receipt->first_name.' '.$billing_receipt->last_name,
    	            'address' => $billing_receipt->address.', '.$billing_receipt->city.', '.$billing_receipt->country.' ('.$billing_receipt->postcode.')',
    	            'phone' => $billing_receipt->phone,
    	            'email' => $billing_receipt->email,
    	            'total_cost' => $billing_receipt->total_cost,
    	            'visitors' => $billing_receipt->visitors,
    	            'data' => $billing_receipt->details
    	            
    	        );
    	        
    	        $ticket = Ticket::leftJoin('trx_billing',function($join){
                        $join->on('trx_ticket.billing_id','=','trx_billing.billing_id');
                    })
                    ->leftJoin('trx_billing_dtl',function($join){
                        $join->on('trx_ticket.billing_id','=','trx_billing_dtl.billing_id');
                    })
                    ->leftJoin('stp_dest',function($join){
                        $join->on('trx_ticket.destid','=','stp_dest.destid');
                    })
                    ->select(
                        'trx_ticket.ticket_id',
                        'trx_billing.total_cost',
                        'stp_dest.destname',
                        'trx_billing_dtl.ticketdatefrom',
                        'trx_ticket.visitor_name',
                        'trx_ticket.email',
                        'trx_ticket.phone'
                    )
                    ->where('trx_ticket.billing_id','=',$response['data']['RefNo'])
                    ->get();
                    $attchs = array();
                    $tickets = array();
                    foreach($ticket as $key => $tick)
                    {
                        QrCode::errorCorrection('H')->size(300)->generate($tick->ticket_id,storage_path().'/qrcode/'.$tick->ticket_id.'.png');
                        $data_ticket = array(
                            'billing_id'=>$tick->ticket_id,
                            'total_cost' =>$tick->total_cost,
                            'destname' => $tick->destname,
                            'datego'=> date('l, d F Y', strtotime($tick->ticketdatefrom)),
                            'nama_visitor' => $tick->visitor_name,
                            'email'  => $tick->email,
                            'phone' => $tick->phone,
                            'ticket' => storage_path().'/qrcode/'.$tick->ticket_id.'.png'
                        );
                        // $pdf = app()->make('dompdf.wrapper');
        
                        $pdf = PDF::loadHtml(view('emails.ticket',$data_ticket));
                        array_push($attchs, $pdf);
                        array_push($tickets, $tick->ticket_id.'.pdf');
                    }
    	        Mail::send('emails.receipt', $invoices, function($message) use ($billing_receipt,$attchs,$tickets){
                    $message->to($billing_receipt->email, $billing_receipt->first_name)
                        ->cc(['e_support@sabahparks.org.my'])
                        ->bcc(['inv_repository@terazglobal.com.my'])
                        ->subject('INVOICE');
                    $message->from('noreply@booking.sabahparks.org.my','SABAH PARKS');
                    foreach ($attchs as $kfile => $file) { 
                        $message->attachData($file->output(),$tickets[$kfile]); // attach each file
                    }
                });
                return view('payment',$invoices);
    	    }else{
    	        return view('failed');
    	    }
        }else{
            return view('failed');
        }
        
    }
    
    public function response_backend()
    {
        $response = (new IPay88Res)->init($this->_merchantCode);
        if($response['status']){
            $data = array();
            $data['PaymentId'] = $response['data']['PaymentId'];
            $data['RefNo'] = $response['data']['RefNo'];
            $data['Amount'] = $response['data']['Amount'];
            $data['Currency'] = $response['data']['Currency'];
            $data['Remark'] = $response['data']['Remark'];
            $data['TransId'] = $response['data']['TransId'];
            $data['AuthCode'] = $response['data']['AuthCode'];
            $data['Status'] = $response['data']['Status'];
            $data['ErrDesc'] = $response['data']['ErrDesc'];
            $data['Signature'] = $response['data']['Signature'];
            $data['CCName'] = $response['data']['CCName'];
            $data['CCNo'] = $response['data']['CCNo'];
            $data['S_bankname'] = $response['data']['S_bankname'];
            $data['S_country'] = $response['data']['S_country'];
            $query = DB::table('trx_responseipay88')->insert($data);
            if($query)
            {
               return "RECEIVEOK";
                
            }
        }
    }

}

<?php
namespace App\Http\Controllers;

use Validator;
use App\User;
use App\Destination;
use App\DestinationPrice;
use App\Billing;
use App\BillingDetail;
use App\Ticket;
use App\Member;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade as PDF;
use App\Helpers\CustomHelper;


class  TestController extends Controller
{
    private $request;
   
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function email()
    {
        try 
        {
            $billing = Billing::with(['details','visitors'])->where('billing_id','=','#MZ8IAJC3')->first();
            foreach($billing->details as $detail)
            {
                $detail->prices = DestinationPrice::where('destid','=',$detail->destid)
                                ->get();
                $detail->destination = Destination::where('destid','=',$detail->destid)->first();
            }
            
	        $invoices = array(
	            'billing_id' => $billing->billing_id,
	            'billing_date'=> date('d F Y, l', strtotime($billing->details[0]->ticketdatefrom)),
	            'name' => $billing->first_name.' '.$billing->last_name,
	            'address' => $billing->address.', '.$billing->city.', '.$billing->country.' ('.$billing->postcode.')',
	            'phone' => $billing->phone,
	            'email' => $billing->email,
	            'total_cost' => $billing->total_cost,
	            'visitors' => $billing->visitors,
	            'data' => $billing->details
	            
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
                    ->where('trx_ticket.billing_id','=','#MZ8IAJC3')
                    ->get();
            $attchs = array();
            $tickets = array();
            foreach($ticket as $key => $value)
            {
                QrCode::errorCorrection('H')->size(300)->generate($value->ticket_id,storage_path().'/qrcode/'.$value->ticket_id.'.png');
                $data = array(
                    'billing_id'=>$value->ticket_id,
                    'total_cost' =>$value->total_cost,
                    'destname' => $value->destname,
                    'datego'=> date('l, d F Y', strtotime($value->ticketdatefrom)),
                    'nama_visitor' => $value->visitor_name,
                    'email'  => $value->email,
                    'phone' => $value->phone,
                    'ticket' => storage_path().'/qrcode/'.$value->ticket_id.'.png'
                );
                // $pdf = app()->make('dompdf.wrapper');

                $pdf = PDF::loadHtml(view('emails.ticket',$data));
                array_push($attchs, $pdf);
                array_push($tickets, $value->ticket_id.'.pdf');
            }
	        
	        Mail::send('emails.receipt', $invoices, function($message) use ($billing,$attchs,$tickets){
                $message->to('prastiyo.beka12@gmail.com', $billing->first_name)
                    ->subject('INVOICE');
                $message->from('noreply@booking.sabahparks.org.my','SABAH PARKS');
                foreach ($attchs as $kfile => $file) { 
                    $message->attachData($file->output(),$tickets[$kfile]); // attach each file
                }
            });
            
            return response()->json($invoices, 200);
            $data = array(
                'name' => 'Prastiyo Beka',
                'token' => '12334343'
            );
            // return view('emails.receipt', $data);

            // Mail::send('emails.receipt', $data, function($message){
            //     $message->to('chandrashibezzo@gmail.com', 'TESTING')
            //         ->subject('INVOICE');
            //     $message->from('noreply@booking.sabahparks.org.my','SABAH PARKS');
            // });

            if (Mail::failures()) {
                $res['code'] = 409;
                $res['message'] = 'Email failed to send';
                $res['data'] = $data['token'];
                return response()->json($res, 409);
            }

            $res['code'] = 201;
            $res['message'] = 'Register success. Please check your email.';
            $res['data'] = $data['token'];
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }

    }

    public function billing()
    {
        try 
        {
             if (!extension_loaded('imagick')){
                echo 'imagick not installed';
            }
             QrCode::format('png')->errorCorrection('H')->size(300)->generate('#384549',storage_path().'/qrcode/#384549.png');
             die();
            $data = array(
                'headers' => array(
                    'No',
                    'Name'
                )
            );

            
            return view('emails.billing',$data);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }

    }
    
    public function report()
    {
        try 
        {   
            // $billing = BillingDetail::with(['destination', 'destination_detail','billing','visitor','destination_price'])
                    //   ->where('billing_id','=','#ZGDIXMWJ')->first();
                       
            $billing = Billing::with(['details','visitors'])->where('billing_id','=','#Z89LCP43')->first();
            
            // $Permit = null;
            // $Acomodation = null;
            // $Insurance = null;
            // $Service = null;
            foreach($billing->details as $detail)
            {
                $detail->prices = DestinationPrice::where('destid','=',$detail->destid)->get();
                $detail->destination = Destination::where('destid','=',$detail->destid)->first();
            }
            

            // return response()->json($billing, 200);
            
	        $invoices = array(
	            'billing_id' => $billing->billing_id,
	            'billing_date'=> date('d F Y, l', strtotime($billing->details[0]->ticketdatefrom)),
	            'name' => $billing->first_name.' '.$billing->last_name,
	            'address' => $billing->address,
	            'phone' => $billing->phone,
	            'email' => $billing->email,
	            'total_cost' => $billing->total_cost,
	            'visitors' => $billing->visitors,
	            'data' => $billing->details
	            
	        );
	        
	        $array = array(
                'billing_id'=>$billing->billing_id,
                'billing_no' => str_replace('#','',$billing->billing_id),
                'total_cost' =>$billing->total_cost
            );
	        return view('emails.billing', $array);
	        Mail::send('emails.receipt', $invoices, function($message) use ($billing){
                $message->to($billing->billing->email, $billing->billing->first_name)
                    ->subject('INVOICE');
                $message->from('noreply@booking.sabahparks.org.my','SABAH PARKS');
            });
            
            return response()->json($Acomodation, 200);
            // $sha        = '#MLTB81VK'.'1'.'MYR';
            // $ipay88     = 'XxCt1kp1ZSM31589';
            // $generator  = $ipay88.$sha;
            // $Signature  = hash("sha256",$ipay88.$sha);
            // return  $Signature; 
            // return  base64_decode("PD9waHAgDQogICAgICAgICAgICAgICAgICAkc3FsX2NhcnQ9bXlzcWxfcXVlcnkoIlNFTEVDVCAqIEZST00gdHJ4X2JpbGxpbmdfdmlzaXRvciB3aGVyZSBiaWxsaW5nX2lkPScjIi4kX0dFVFsnaWQnXS4iJyBhbmQgc3RhdHVzPScxJyIpOwkNCiAgICAgICAgICAgICAgICAgIHdoaWxlKCRyPW15c3FsX2ZldGNoX2Fzc29jKCRzcWxfY2FydCkpew0KICAgICAgICAgICAgICAgICAgZWNobyAiDQogICAgICAgICAgICAgICAgICA8dHI+DQogICAgICAgICAgICAgICAgICAJPHRkPg0KICAgICAgICAgICAgICAgICAgCQk8c3Ryb25nPiAkclt2aXNpdG9yX25hbWVdPC9zdHJvbmc+DQogICAgICAgICAgICAgICAgICAJPC90ZD4NCiAgICAgICAgICAgICAgICAgIAk8dGQ+DQogICAgICAgICAgICAgICAgICAJCTxzdHJvbmc+ICRyW2lkX251bWJlcl08L3N0cm9uZz4NCiAgICAgICAgICAgICAgICAgIAk8L3RkPg0KICAgICAgICAgICAgICAgICAgCTx0ZD4NCiAgICAgICAgICAgICAgICAgIAkJPHN0cm9uZz4gICRyW2VtYWlsXTwvc3Ryb25nPg0KICAgICAgICAgICAgICAgICAgCTwvdGQ+DQogICAgICAgICAgICAgICAgICAJPHRkPg0KICAgICAgICAgICAgICAgICAgCQk8c3Ryb25nPiAgJHJbcGhvbmVdPC9zdHJvbmc+DQogICAgICAgICAgICAgICAgICAJPC90ZD4NCiAgICAgICAgICAgICAgICAgIDwvdHI+IjsNCiAgICAgICAgICAgICAgICAgIH0NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPz4=");
            return base64_decode("PD9waHANCiRzcWxfcGF5bWVudCA9IG15c3FsX3F1ZXJ5KCJzZWxlY3QgDQogICAgICAgICAgICAgICAgICBELmJpbGxpbmdfaWQsRC5maXJzdF9uYW1lLEQubGFzdF9uYW1lLEQucGhvbmUsRC5lbWFpbCwNCiAgICAgICAgICAgICAgICAgIEIuZGVzdG5hbWUsDQogICAgICAgICAgICAgICAgICBDLmRlc3RwaG90byAsDQogICAgICAgICAgICAgICAgICBELnRvdGFsX3BheCxELnRvdGFsX2Nvc3QsRC5zdGF0dXNfYXBwcm92ZWQNCiAgICAgICAgICAgICAgICAgIGZyb20gdHJ4X2JpbGxpbmdfZHRsIEEgDQogICAgICAgICAgICAgICAgICBMRUZUIEpPSU4gc3RwX2Rlc3QgQiBvbiBBLmRlc3RpZD1CLmRlc3RpZCANCiAgICAgICAgICAgICAgICAgIExFRlQgSk9JTiBzdHBfZGVzdF9kdGwgQyBvbiBBLmRlc3RpZD1DLmRlc3RpZCANCiAgICAgICAgICAgICAgICAgIExFRlQgSk9JTiB0cnhfYmlsbGluZyBEIG9uIEEuYmlsbGluZ19pZD1ELmJpbGxpbmdfaWQgIA0KICAgICAgICAgICAgICAgICAgd2hlcmUgQS5iaWxsaW5nX2lkPScjIi4kX0dFVFsnaWQnXS4iJyBhbmQgQS5zdGF0dXM9JzEnDQogICAgICAgICAgICAgICAgICBncm91cCBieSBELmJpbGxpbmdfaWQiKTsNCiAgICAgICAgICAgICAgICAgICAgd2hpbGUgKCRyID0gbXlzcWxfZmV0Y2hfYXNzb2MoJHNxbF9wYXltZW50KSkgew0KICAgICAgICAgICAgICAgICAgICAgICAgJGZpcnN0X25hbWUgPSAkclsnZmlyc3RfbmFtZSddOw0KICAgICAgICAgICAgICAgICAgICAgICAgJGxhc3RfbmFtZSAgPSAkclsnbGFzdF9uYW1lJ107DQogICAgICAgICAgICAgICAgICAgICAgICAkcGhvbmUgICAgICA9ICRyWydwaG9uZSddOw0KICAgICAgICAgICAgICAgICAgICAgICAgJGVtYWlsICAgICAgPSAkclsnZW1haWwnXTsNCiAgICAgICAgICAgICAgICAgICAgICAgICRiaWxsaW5nX2lkID0gJHJbJ2JpbGxpbmdfaWQnXTsNCiAgICAgICAgICAgICAgICAgICAgICAgICRzdGF0dXNfYXBwcm92ZWQgPSAkclsnc3RhdHVzX2FwcHJvdmVkJ107DQogICAgICAgICAgICAgICAgICAgICAgICAkdG90YWwgICAgICA9IHByZWdfcmVwbGFjZSgiL1teMC05XS8iLCAiIiwgJHJbJ3RvdGFsX2Nvc3QnXSk7DQogICAgICAgICAgICAgICAgICAgICAgICAkdG90YWxfMSAgICA9ICRyWyd0b3RhbF9jb3N0J107DQogICAgICAgICAgICAgICAgICAgICAgICAkY3VycmVuY3kgICA9ICdNWVInOw0KICAgICAgICAgICAgICAgICAgICAgICAgJHNoYSAgICAgICAgPSAkYmlsbGluZ19pZC4kdG90YWwuJGN1cnJlbmN5Ow0KICAgICAgICAgICAgICAgICAgICAgICAgJGlwYXk4OCAgICAgPSAnWHhDdDFrcDFaU00zMTU4OSc7DQogICAgICAgICAgICAgICAgICAgICAgICAkZ2VuZXJhdG9yICA9ICRpcGF5ODguJHNoYTsNCiAgICAgICAgICAgICAgICAgICAgICAgICRTaWduYXR1cmUgID0gaGFzaCgic2hhMjU2IiwkaXBheTg4LiRzaGEpOw0KICAgICAgICAgICAgICAgICAgICB9DQogICAgICAgICAgICBlY2hvICcNCiAgICAgICAgICAgIDxoMz48c3Ryb25nPjxpIGNsYXNzPSJpY29uLW9rIj48L2k+PC9zdHJvbmc+SU5WT0lDRSA6ICMnLiRfR0VUWydpZCddLic8L2gzPic7DQogICAgICAgICAgICBpZiAoJHN0YXR1c19hcHByb3ZlZCA8PiAyKQ0KICAgICAgICAgICAgew0KICAgICAgICAgICAgZWNobyAnIDxhIGhyZWY9IiMiIGNsYXNzPSJidG5fZnVsbCIgc3R5bGU9ImJhY2tncm91bmQtY29sb3I6I0E5QTlBOTtjb2xvcjp3aGl0ZTsiID5QYXkgTm93ITwvYT4gJzsgICAgIA0KICAgICAgICAgICAgfQ0KICAgICAgICAgICAgZWxzZSB7DQogICAgICAgICAgIGVjaG8gJyA8SU5QVVQgdHlwZT0ic3VibWl0IiB2YWx1ZT0iUGF5IE5vdyEiIG5hbWU9IlN1Ym1pdCIgY2xhc3M9ImJ0bl9mdWxsIj4gJzsNCiAgICAgICAgICAgIH0NCiAgICAgICAgICAgIGVjaG8gJzwvZGl2Pg0KICAgICAgICAgICAgPElOUFVUIHR5cGU9ImhpZGRlbiIgbmFtZT0iTWVyY2hhbnRDb2RlIiB2YWx1ZT0iTTMxNTg5Ij48YnI+DQogICAgICAgICAgICA8SU5QVVQgdHlwZT0iaGlkZGVuIiBuYW1lPSJQYXltZW50SWQiIHZhbHVlPSInLiRiaWxsaW5nX2lkLiciPjxicj4NCiAgICAgICAgICAgIDxJTlBVVCB0eXBlPSJoaWRkZW4iIG5hbWU9IlJlZk5vIiB2YWx1ZT0iJy4kYmlsbGluZ19pZC4nIj48YnI+DQogICAgICAgICAgICA8SU5QVVQgdHlwZT0iaGlkZGVuIiBuYW1lPSJBbW91bnQiIHZhbHVlPSInLiR0b3RhbF8xLiciPjxicj4NCiAgICAgICAgICAgIDxJTlBVVCB0eXBlPSJoaWRkZW4iIG5hbWU9IkN1cnJlbmN5IiB2YWx1ZT0iJy4kY3VycmVuY3kuJyI+PGJyPg0KICAgICAgICAgICAgPElOUFVUIHR5cGU9ImhpZGRlbiIgbmFtZT0iUHJvZERlc2MiIHZhbHVlPSJUaWNrZXQgU2FiYWggUGFyayAnLiRiaWxsaW5nX2lkLiciPjxicj4NCiAgICAgICAgICAgIDxJTlBVVCB0eXBlPSJoaWRkZW4iIG5hbWU9IlVzZXJOYW1lIiBWYWx1ZT0iJy4kZmlyc3RfbmFtZS4nICcuJGxhc3RfbmFtZS4nIj48YnI+DQogICAgICAgICAgICA8SU5QVVQgdHlwZT0iaGlkZGVuIiBuYW1lPSJVc2VyRW1haWwiIFZhbHVlPSInLiRlbWFpbC4nIj48YnI+DQogICAgICAgICAgICA8SU5QVVQgdHlwZT0iaGlkZGVuIiBuYW1lPSJVc2VyQ29udGFjdCIgVmFsdWU9IicuJHBob25lLiciID48YnI+DQogICAgICAgICAgICA8SU5QVVQgdHlwZT0iaGlkZGVuIiBuYW1lPSJSZW1hcmsiIHZhbHVlPSItIj48YnI+DQogICAgICAgICAgICA8SU5QVVQgdHlwZT0iaGlkZGVuIiBuYW1lPSJMYW5nIiB2YWx1ZT0iVVRGLTgiIHJlcXVpcmVkPjxicj4NCiAgICAgICAgICAgIDxJTlBVVCB0eXBlPSJoaWRkZW4iIG5hbWU9IlNpZ25hdHVyZVR5cGUiIHZhbHVlPSJTSEEyNTYiPjxicj4NCiAgICAgICAgICAgIDxJTlBVVCB0eXBlPSJoaWRkZW4iIG5hbWU9IlNpZ25hdHVyZSIgdmFsdWU9IicuJFNpZ25hdHVyZS4nIiA+PGJyPg0KICAgICAgICAgICAgPElOUFVUIHR5cGU9ImhpZGRlbiIgbmFtZT0iUmVzcG9uc2VVUkwiIHZhbHVlPSJodHRwczovL2Jvb2tpbmcuc2FiYWhwYXJrcy5vcmcubXkvP3M9cGF5bWVudF9jb25maXJtYXRpb24iPg0KCQkJPElOUFVUIHR5cGU9ImhpZGRlbiIgbmFtZT0iQmFja2VuZFVSTCIgdmFsdWU9Imh0dHBzOi8vYm9va2luZy5zYWJhaHBhcmtzLm9yZy5teS9iYWNrZW5kX3Jlc3BvbnNlLnBocCI+DQogICAgICAgICAgICAgICAgJzsNCj8+");
             $data = array(
                'title' => 'REPORT MEMBER',
                'headers' => array(
                    array('header'=>'Nama', 'field' => 'nama'),
                    array('header'=>'Phone', 'field' => 'phone'),
                    array('header'=>'Email', 'field' => 'email'),
                    array('header'=>'Age', 'field' => 'age'),
                    array('header'=>'Gender', 'field' => 'gender')
                ),
                'data' => array(
                    array('nama'=>'ABC', 'phone' => '12321', 'email'=>'tes@gmail.com', 'age'=>'23','gender' => 'Laki-laki'),
                    array('nama'=>'TES', 'phone' => '12323', 'email'=>'tes2@gmail.com', 'age'=>'23','gender' => 'Laki-laki')
                )
            );
            
            $pdf = PDF::loadHtml(view('report',$data));
            $pdf->setPaper('a4', 'potrait');
            $pdf->setOptions(['defaultFont' => 'sans-serif']);
            // return $pdf->download($value->ticket_id.'.pdf');
            return $pdf->stream();
            // return view('report',$data);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }

    }

    public function ticket()
    {
        try 
        {
            $billing_id = $this->request->get('ticket_id');
            $billing = Ticket::leftJoin('trx_billing',function($join){
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
                    ->where('trx_ticket.ticket_id','=',$billing_id)
                    ->get();
            if(!empty($billing))
            {
                foreach($billing as $key => $value)
                {
                    QrCode::errorCorrection('H')->size(300)->generate($value->ticket_id,storage_path().'/qrcode/'.$value->ticket_id.'.png');
                    $data = array(
                        'billing_id'=>$value->ticket_id,
                        'total_cost' =>$value->total_cost,
                        'destname' => $value->destname,
                        'datego'=> date('l, d F Y', strtotime($value->ticketdatefrom)),
                        'nama_visitor' => $value->visitor_name,
                        'email'  => $value->email,
                        'phone' => $value->phone,
                        'ticket' => storage_path().'/qrcode/'.$value->ticket_id.'.png'
                    );
                    // $pdf = app()->make('dompdf.wrapper');

                    $pdf = PDF::loadHtml(view('emails.ticket',$data));
                    return $pdf->download($value->ticket_id.'.pdf');
                }
            }else{
                $res['code'] = 200;
                $res['message'] = 'Billing is unpaid.';
                return response()->json($res, 200);
            }
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }

    }
    
    public function receipt()
    {
        
            $billing_id = '#'.$this->request->get('billing_id');
            $billing_receipt = Billing::with(['details','visitors'])->where('billing_id','=',$billing_id)->first();
    	        
                if(!empty($billing_receipt))
                {
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

                return view('emails.receipt',$invoices);
                
                $pdf = PDF::loadHtml(view('emails.receipt',$invoices));
                return $pdf->stream();
                return $pdf->download($billing_receipt->billing_id.'.pdf');
            }else{
                $res['code'] = 200;
                $res['message'] = 'Billing is unpaid.';
                return response()->json($res, 200);
            }
        try 
        {
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }

    }
    
    public function notification()
    {
        $user = Member::where('id','=',44)->pluck('active_key')->toArray();
        $data = [
              "notif_type"=> 1,
              "data"=>array(
                    "id"=> 1,
                    "id_type"=> 4,
                    "title"=> "Sabah Parks Information",
                    "content"=> "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
                    "date_created "=> "2021-02-18 00:00:00",
                    "path"=> ""
                )
        ];
        $shorttext = substr($data['data']['content'], 0, 25);
        $push = CustomHelper::sendpush('Sabah Parks Information', "Lorem Ipsum is simply dummy text of the", $data, array_filter($user));
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
    }
    
    public function send_notif()
    {
        // $data = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 8, 8);
        // // $data = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 8, 8);
        // return response()->json([
        //         'success' => true,
        //         'message' => 'Notif send',
        //         'data' =>$data
        //     ], 201);
        $daata = Ticket::where('status','=',1)
                    ->select('email')
                    ->groupBy('email')
                    ->pluck('email')
                    ->toArray();
          return response()->json([
                'success' => true,
                'message' => 'Notif send',
                'data' => $daata
            ], 201);
        $config = DB::table('stp_config')->where('type','=','remind-billing')->first();
        $date = date('Y-m-d', strtotime('+'.$config->duration.' days'));
        $user = BillingDetail::leftJoin('stp_member',function($join){
                 $join->on('stp_member.buyerid','=','trx_billing_dtl.buyerid');
             })
             ->whereDate('trx_billing_dtl.ticketdatefrom','=',$date)
             ->whereNotNull('stp_member.active_key')
             ->groupBy('stp_member.active_key')
             ->pluck('stp_member.active_key')->toArray();
         return response()->json([
                'success' => true,
                'message' => 'Notif send',
                'data' => $user
            ], 201);
             
        $user = Member::where('id','=',50)->pluck('active_key')->toArray();
        $data = [
              "notif_type"=> 1,
              "data"=>array(
                    "id"=> 1,
                    "id_type"=> 4,
                    "title"=> "Sabah Parks Information",
                    "content"=> "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
                    "date_created "=> "2021-02-18 00:00:00",
                    "path"=> ""
                )
        ];
        $shorttext = substr($data['data']['content'], 0, 25);
        $push = CustomHelper::sendpush('Sabah Parks Information', "Lorem Ipsum is simply dummy text of the", $data, array_filter($user));
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
    }
}

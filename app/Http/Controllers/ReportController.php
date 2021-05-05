<?php
namespace App\Http\Controllers;

use Validator;
use App\Receipt;
use App\Destination;
use App\DestinationPrice;
use App\Billing;
use App\BillingDetail;
use App\BillingVisitor;
use App\User;
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

class  ReportController extends Controller
{
    private $request;
   
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function download_get()
    {
        $data = Member::leftJoin('stp_country', function($join){
                    $join->on('stp_country.id','=','stp_member.country');
                })
                ->select('stp_member.*','stp_country.country_name')
                ->where('stp_member.status','=',1);

        if($this->request->get('condition')){
            $data->where('stp_member.first_name','like','%'.$this->request->get('condition').'%');
        }
        //ORDER BY
        if($this->request->get('dir')){
            $data->orderBy($this->request->get('dir'), $this->request->get('sort'));
        }else{
            $data->orderBy('stp_member.first_name', 'DESC');
        }
        //PAGINATION'title' => 'REPORT MEMBER',
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
        $data = array(
            'title' => 'REPORT MEMBER',
            'headers' => array(
                array('header'=>'FIRST NAME', 'field' => 'first_name'),
                array('header'=>'LAST NAME', 'field' => 'last_name'),
                array('header'=>'NATIONALITY', 'field' => 'country_name'),
                array('header'=>'EMAIL', 'field' => 'email'),
                array('header'=>'POHNE', 'field' => 'phone')
            ),
            'data' => $grid
        );
        
        $pdf = PDF::loadHtml(view('report',$data));
        $pdf->setPaper('a4', 'potrait');
        $pdf->setOptions(['defaultFont' => 'sans-serif']);
        return $pdf->download('report_member_'.date('Y-m-d').'.pdf');
    }
    
    public function download_order()
    {
        $data = Billing::with(['details','visitors'])
                ->leftJoin('trx_billing_dtl', function($join){
                    $join->on('trx_billing.billing_id','=','trx_billing_dtl.billing_id');
                })
                ->leftJoin('stp_member', function($join){
                    $join->on('trx_billing.buyerid','=','stp_member.buyerid');
                })
                ->leftJoin('stp_dest', function($join){
                    $join->on('trx_billing_dtl.destid','=','stp_dest.destid');
                })
                ->select(
                    'trx_billing.*',
                    'trx_billing_dtl.destid',
                    'trx_billing_dtl.ticketdatefrom',
                    'trx_billing_dtl.ticketdateto',
                    'trx_billing_dtl.loc_qty_18above',
                    'trx_billing_dtl.loc_qty_18below',
                    'trx_billing_dtl.int_qty_18above',
                    'trx_billing_dtl.int_qty_18below',
                    'trx_billing_dtl.createdate',
                    'stp_dest.destname'
                )
                ;

        if($this->request->get('condition')){
            $data->where(function($query){
               $query->where('stp_dest.destname','like', '%'.$this->request->get('condition').'%');
               $query->orWhere('trx_billing.first_name','like','%'.$this->request->get('condition').'%');
               $query->orWhere('trx_billing.last_name','like','%'.$this->request->get('condition').'%');
            });
        }
        
        if($this->request->get('start_date')){
             $data->whereDate('trx_billing_dtl.createdate','>=', $this->request->get('start_date'));
             $data->whereDate('trx_billing_dtl.createdate','<=', $this->request->get('end_date'));
        }
        //ORDER BY
        if($this->request->get('dir')){
            $data->orderBy($this->request->get('dir'), $this->request->get('sort'));
        }else{
            $data->orderBy('stp_member.first_name', 'DESC');
        }
        
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
        $data = array(
            'title' => 'REPORT ORDER',
            'headers' => array(
                array('header'=>'ORDER ID', 'field' => 'billing_id'),
                array('header'=>'ORDER DATE', 'field' => 'createdate'),
                array('header'=>'DESTINATION', 'field' => 'destname'),
                array('header'=>'DATE TO GO', 'field' => 'ticketdatefrom'),
                array('header'=>'QTY', 'field' => 'loc_qty_18above'),
                array('header'=>'FIRSTNAME', 'field' => 'first_name'),
                array('header'=>'LASTNAME', 'field' => 'last_name')
            ),
            'data' => $grid
        );
        ini_set("max_execution_time","-1");
        $pdf = PDF::loadHtml(view('report',$data));
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions(['defaultFont' => 'sans-serif']);
        return $pdf->download('report_order_'.date('Y-m-d').'.pdf');
    }
    
    public function download_booking()
    {
        $data = Billing::with(['details','visitors'])
                ->leftJoin('trx_billing_dtl', function($join){
                    $join->on('trx_billing.billing_id','=','trx_billing_dtl.billing_id');
                })
                ->leftJoin('stp_member', function($join){
                    $join->on('trx_billing.buyerid','=','stp_member.buyerid');
                })
                ->leftJoin('stp_dest', function($join){
                    $join->on('trx_billing_dtl.destid','=','stp_dest.destid');
                })
                ->select('trx_billing.*',
                    'trx_billing_dtl.destid',
                    'trx_billing_dtl.ticketdatefrom',
                    'trx_billing_dtl.ticketdateto',
                    'trx_billing_dtl.loc_qty_18above',
                    'trx_billing_dtl.loc_qty_18below',
                    'trx_billing_dtl.int_qty_18above',
                    'trx_billing_dtl.int_qty_18below',
                    'trx_billing_dtl.createdate',
                    'stp_dest.destname')
                ->where('trx_billing.pr_holder','=',1);

         if($this->request->get('condition')){
            $data->where(function($query){
               $query->where('stp_dest.destname','like', '%'.$this->request->get('condition').'%');
               $query->orWhere('trx_billing.first_name','like','%'.$this->request->get('condition').'%');
               $query->orWhere('trx_billing.last_name','like','%'.$this->request->get('condition').'%');
            });
        }
        
        if($this->request->get('start_date')){
             $data->whereDate('trx_billing_dtl.createdate','>=', $this->request->get('start_date'));
             $data->whereDate('trx_billing_dtl.createdate','<=', $this->request->get('end_date'));
        }
        //ORDER BY
        if($this->request->get('dir')){
            $data->orderBy($this->request->get('dir'), $this->request->get('sort'));
        }else{
            $data->orderBy('stp_member.first_name', 'DESC');
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
        
        $data = array(
            'title' => 'REPORT BOOKING',
            'headers' => array(
                array('header'=>'ORDER ID', 'field' => 'billing_id'),
                array('header'=>'ORDER DATE', 'field' => 'createdate'),
                array('header'=>'DESTINATION', 'field' => 'destname'),
                array('header'=>'DATE TO GO', 'field' => 'ticketdatefrom'),
                array('header'=>'QTY', 'field' => 'loc_qty_18above'),
                array('header'=>'FIRSTNAME', 'field' => 'first_name'),
                array('header'=>'LASTNAME', 'field' => 'last_name')
            ),
            'data' => $grid
        );
        ini_set("max_execution_time","-1");
        $pdf = PDF::loadHtml(view('report',$data));
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions(['defaultFont' => 'sans-serif']);
        return $pdf->download('report_booking_'.date('Y-m-d').'.pdf');
    }
    
    public function download_billing()
    {
        $data = Billing::leftJoin('trx_billing_dtl', function($join){
                    $join->on('trx_billing.billing_id','=','trx_billing_dtl.billing_id');
                })
                ->leftJoin('trx_receipt', function($join){
                    $join->on('trx_billing.billing_id','=','trx_receipt.billing_id');
                })
                ->leftJoin('stp_dest', function($join){
                    $join->on('trx_billing_dtl.destid','=','stp_dest.destid');
                })
                ->select(
                    'trx_billing.*',
                    'stp_dest.destname','trx_receipt.receipt_id', 
                    'trx_receipt.createdate as pay_date',
                    'trx_billing_dtl.destid',
                    'trx_billing_dtl.ticketdatefrom',
                    'trx_billing_dtl.ticketdateto',
                    'trx_billing_dtl.loc_qty_18above',
                    'trx_billing_dtl.loc_qty_18below',
                    'trx_billing_dtl.int_qty_18above',
                    'trx_billing_dtl.int_qty_18below',
                    'trx_billing_dtl.createdate'
                    );

        if($this->request->get('condition')){
            $data->where(function($query){
               $query->where('stp_dest.destname','like', '%'.$this->request->get('condition').'%');
               $query->orWhere('trx_receipt.receipt_id','like','%'.$this->request->get('condition').'%');
               $query->orWhere('trx_billing.billing_id','like','%'.$this->request->get('condition').'%');
            });
        }
        
        if($this->request->get('start_date')){
             $data->whereDate('trx_billing_dtl.createdate','>=', $this->request->get('start_date'));
             $data->whereDate('trx_billing_dtl.createdate','<=', $this->request->get('end_date'));
        }
        //ORDER BY
        if($this->request->get('dir')){
            $data->orderBy($this->request->get('dir'), $this->request->get('sort'));
        }else{
            $data->orderBy('stp_member.first_name', 'DESC');
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
        
        $data = array(
            'title' => 'REPORT BILLING',
            'headers' => array(
                array('header'=>'ORDER ID', 'field' => 'billing_id'),
                array('header'=>'ORDER DATE', 'field' => 'createdate'),
                array('header'=>'DESTINATION', 'field' => 'destname'),
                array('header'=>'DATE TO GO', 'field' => 'ticketdatefrom'),
                array('header'=>'PAYMENT REF.NO', 'field' => 'receipt_id'),
                array('header'=>'PAYMENT DATE', 'field' => 'pay_date')
            ),
            'data' => $grid
        );
        ini_set("max_execution_time","-1");
        $pdf = PDF::loadHtml(view('report',$data));
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions(['defaultFont' => 'sans-serif']);
        return $pdf->download('report_billing_'.date('Y-m-d').'.pdf');
    }
    
    public function download_sales()
    {
        $data = Receipt::leftJoin('trx_billing', function($join){
                    $join->on('trx_receipt.billing_id','=','trx_billing.billing_id');
                })
                ->leftJoin('trx_billing_dtl', function($join){
                    $join->on('trx_receipt.billing_id','=','trx_billing_dtl.billing_id');
                })
                ->leftJoin('stp_dest', function($join){
                    $join->on('trx_billing_dtl.destid','=','stp_dest.destid');
                })
                ->select(
                    'trx_receipt.*', 
                    'stp_dest.destid',
                    'stp_dest.destname',
                    'trx_billing.country',
                    DB::raw('(trx_billing_dtl.loc_qty_18above + trx_billing_dtl.int_qty_18above) as age_18above'),
                    DB::raw('(trx_billing_dtl.loc_qty_18below + trx_billing_dtl.int_qty_18below) as age_18below')
                );

        if($this->request->get('condition')){
            $data->where('stp_dest.destname','like','%'.$this->request->get('condition').'%');
        }
        
        if($this->request->get('start_date')){
             $data->whereDate('trx_receipt.createdate','>=', $this->request->get('start_date'));
             $data->whereDate('trx_receipt.createdate','<=', $this->request->get('end_date'));
        }
        //ORDER BY
        if($this->request->get('dir')){
            $data->orderBy($this->request->get('dir'), $this->request->get('sort'));
        }else{
            $data->orderBy('trx_receipt.receipt_id', 'DESC');
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
        $summary = 0;
        foreach($grid as $key => $value)
        {
            $value->{'detail_price'} = DestinationPrice::where('destid','=',$value->destid)->get();
            $summary += $value->amount;
        }
        
        
        $data = array(
            'title' => 'REPORT SALES',
            'summary' => number_format($summary,2),
            'headers' => array(
                array('header'=>'BILLING ID', 'field' => 'billing_id'),
                array('header'=>'RECEIPT ID', 'field' => 'receipt_id'),
                array('header'=>'COUNTRY', 'field' => 'country'),
                array('header'=>'AGE 18 ABOVE', 'field' => 'age_18above'),
                array('header'=>'AGE 18 BELOW', 'field' => 'age_18below'),
                array('header'=>'DESTINATION', 'field' => 'destname'),
                array('header'=>'BANK', 'field' => 'bank_name'),
                array('header'=>'BANK NO.', 'field' => 'bank_no'),
                array('header'=>'BANK ACC', 'field' => 'bank_acc'),
                array('header'=>'AMOUNT', 'field' => 'amount'),
                array('header'=>'REMARK', 'field' => 'remark')
            ),
            'data' => $grid
        );
        ini_set("max_execution_time","-1");
        $pdf = PDF::loadHtml(view('sales',$data));
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions(['defaultFont' => 'sans-serif']);
        return $pdf->download('report_sales_'.date('Y-m-d').'.pdf');
    }
    
    public function download_payment()
    {
        $filters = array('Amount',
                        'Currency',
                        'Remark',
                        'TransId',
                        'AuthCode',
                        'Status',
                        'ErrDesc',
                        'Signature',
                        'CCName',
                        'CCNo',
                        'S_bankname',
                        'S_country');
        $data = DB::table('trx_responseipay88')
                    ->leftJoin('trx_receipt',function($join){
                        $join->on('trx_responseipay88.TransId','=','trx_receipt.receipt_id');
                    })
                    ->select(
                        DB::raw('MAX(trx_receipt.createdate) as createdate'),
                        'trx_responseipay88.PaymentId',
                        'trx_responseipay88.RefNo',
                        'trx_responseipay88.Amount',
                        'trx_responseipay88.Currency',
                        'trx_responseipay88.Remark',
                        'trx_responseipay88.TransId',
                        'trx_responseipay88.AuthCode',
                        'trx_responseipay88.Status',
                        'trx_responseipay88.ErrDesc',
                        'trx_responseipay88.Signature',
                        'trx_responseipay88.CCName',
                        'trx_responseipay88.CCNo',
                        'trx_responseipay88.S_bankname',
                        'trx_responseipay88.S_country'    
                    )
                    ->groupBy(
                        'trx_responseipay88.PaymentId',
                        'trx_responseipay88.RefNo',
                        'trx_responseipay88.Amount',
                        'trx_responseipay88.Currency',
                        'trx_responseipay88.Remark',
                        'trx_responseipay88.TransId',
                        'trx_responseipay88.AuthCode',
                        'trx_responseipay88.Status',
                        'trx_responseipay88.ErrDesc',
                        'trx_responseipay88.Signature',
                        'trx_responseipay88.CCName',
                        'trx_responseipay88.CCNo',
                        'trx_responseipay88.S_bankname',
                        'trx_responseipay88.S_country'
                    );

        if($this->request->get('condition')){
            $data->where(function($query){
                $query->where('RefNo','like','%'.$this->request->get('condition').'%');
                foreach($filters as $search)
                {
                    $query->orWhere('trx_responseipay88.'.$search,'like', '%'.$this->request->get('condition').'%');
                }
            });
        }
        
        if($this->request->get('start_date')){
             $data->whereDate('trx_receipt.createdate','>=', $this->request->get('start_date'));
             $data->whereDate('trx_receipt.createdate','<=', $this->request->get('end_date'));
        }
        //ORDER BY
        if($this->request->get('dir')){
            $data->orderBy($this->request->get('dir'), $this->request->get('sort'));
        }else{
            $data->orderBy('trx_billing_dtl.billing_id', 'DESC');
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
            $value->Status = ($value->Status == 1)?'SUCCESS':'FAILED'; 
        }
        $data = array(
            'title' => 'REPORT PAYMENT (IPAY88)',
            'headers' => array(
                array('header'=>'REF.NO', 'field' => 'RefNo'),
                array('header'=>'AMOUNT', 'field' => 'Amount'),
                array('header'=>'CURRENCY', 'field' => 'Currency'),
                array('header'=>'REMARK', 'field' => 'Remark'),
                array('header'=>'TRANS. ID', 'field' => 'TransId'),
                array('header'=>'AUTH. CODE', 'field' => 'AuthCode'),
                array('header'=>'ERROR. DESC', 'field' => 'ErrDesc'),
                array('header'=>'CC.NAME', 'field' => 'CCName'),
                array('header'=>'CC.NO', 'field' => 'CCNo'),
                array('header'=>'BANKNAME', 'field' => 'S_bankname'),
                array('header'=>'COUNTRY', 'field' => 'S_country'),
                 array('header'=>'SIGNATURE', 'field' => 'Signature'),
                array('header'=>'STATUS', 'field' => 'Status')
            ),
            'data' => $grid
        );
        ini_set("max_execution_time","-1");
        $pdf = PDF::loadHtml(view('report',$data));
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions(['defaultFont' => 'sans-serif']);
        return $pdf->download('report_payment_'.date('Y-m-d').'.pdf');
    }
}

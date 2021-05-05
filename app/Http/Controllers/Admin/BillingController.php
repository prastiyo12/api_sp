<?php
namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use  App\Billing;
use  App\Receipt;
use  App\Destination;

class  BillingController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        $this->middleware('auth');
        $this->request = $request;
    }

    /**
     * @OA\Get(
     *   path="/api/admin/billing/get",
     *   summary="Get Data Order",
     *   tags={"Setup Billing (BackOffice)"},
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
        $data = Billing::leftJoin('trx_billing_dtl',function($join){ 
                    $join->on('trx_billing.billing_id','=','trx_billing_dtl.billing_id');
                    $join->on('trx_billing.buyerid','=','trx_billing_dtl.buyerid');
                    
                })
                ->leftJoin('trx_receipt', 'trx_billing.billing_id','=','trx_receipt.billing_id')
                ->leftJoin('stp_dest', 'trx_billing_dtl.destid','=','stp_dest.destid')
                ->select(
                    'trx_billing.id',
                    'trx_billing.billing_id',
                    DB::raw('max(trx_billing_dtl.createdate) as createdate'),
                    DB::raw('max(trx_billing.first_name) as first_name'),
                    DB::raw('max(trx_billing.last_name) as last_name'),
                    DB::raw('max(trx_billing.country) as country'),
                    DB::raw('max(trx_billing.address) as address'),
                    DB::raw('max(trx_billing.email) as email'),
                    DB::raw('max(trx_billing.phone) as phone'),
                    'trx_billing.status',
                    DB::raw('max(stp_dest.destname) as destname'),
                    'trx_receipt.receipt_id', 
                    DB::raw('max(trx_receipt.createdate) as pay_date'),
                     DB::raw('max(trx_billing_dtl.ticketdatefrom) as ticketdatefrom')
                    )
                    ->groupBy(
                        'trx_billing.id',
                        'trx_billing.billing_id',
                        'trx_billing.status',
                        'trx_receipt.receipt_id'
                    );

        if($this->request->get('condition')){
            $data->where(function($query){
               $query->where('trx_billing.billing_id','like','%'.$this->request->get('condition').'%');
               $query->orWhere('stp_dest.destname','like', '%'.$this->request->get('condition').'%');
               $query->orWhere('trx_receipt.receipt_id','like','%'.$this->request->get('condition').'%');
              
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
            $value->download_link = 'http://'.$_SERVER['SERVER_NAME'].'/public/download/receipt?billing_id='.str_replace('#','',$value->billing_id);
        }
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['totalRow'] = $total;
        $res['totalPages'] = $pages;
        $res['data'] = $grid;
        return response()->json($res, 200);
    }
}

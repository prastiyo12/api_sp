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

class  IpayController extends Controller
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
            $data->where(function($query) use ($filters){
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
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['totalRow'] = $total;
        $res['totalPages'] = $pages;
        $res['data'] = $grid;
        return response()->json($res, 200);
    }
}

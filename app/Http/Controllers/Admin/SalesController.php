<?php
namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use  App\Receipt;
use  App\Destination;
use  App\DestinationPrice;

class  SalesController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        $this->middleware('auth');
        $this->request = $request;
    }

    /**
     * @OA\Get(
     *   path="/api/admin/sales/get",
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
        $data = Receipt::leftJoin('trx_billing_dtl', function($join){
                    $join->on('trx_receipt.billing_id','=','trx_billing_dtl.billing_id');
                })
                ->leftJoin('stp_dest', function($join){
                    $join->on('trx_billing_dtl.destid','=','stp_dest.destid');
                })
                ->select(
                    'trx_receipt.billing_id',
                    'trx_receipt.receipt_id',
                    'trx_receipt.bank_name',
                    'trx_receipt.bank_no',
                    'trx_receipt.bank_acc',
                    'trx_receipt.amount',
                    'trx_receipt.remark',
                    DB::raw('max(stp_dest.destname) as destname')
                )->groupBy(
                    'trx_receipt.billing_id',
                    'trx_receipt.receipt_id',
                    'trx_receipt.bank_name',
                    'trx_receipt.bank_no',
                    'trx_receipt.bank_acc',
                    'trx_receipt.amount',
                    'trx_receipt.remark'   
                );

        if($this->request->get('condition')){
            $data->where(function($search){
                $search->where('stp_dest.destname','like','%'.$this->request->get('condition').'%');
                $search->orWhere('trx_receipt.billing_id','like','%'.$this->request->get('condition').'%');
                $search->orWhere('trx_receipt.receipt_id','like','%'.$this->request->get('condition').'%');
                $search->orWhere('trx_receipt.bank_name','like','%'.$this->request->get('condition').'%');
                $search->orWhere('trx_receipt.bank_no','like','%'.$this->request->get('condition').'%');
                $search->orWhere('trx_receipt.bank_acc','like','%'.$this->request->get('condition').'%'); 
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
        foreach($grid as $key => $value)
        {
            $value->{'detail_price'} = DestinationPrice::where('destid','=',$value->destid)->get();
        }
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['totalRow'] = $total;
        $res['totalPages'] = $pages;
        $res['data'] = $grid;
        return response()->json($res, 200);
    }
}

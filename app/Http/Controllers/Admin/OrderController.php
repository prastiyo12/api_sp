<?php
namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use  App\Billing;
use  App\BillingDetail;
use  App\BillingVisitor;

class  OrderController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        $this->middleware('auth');
        $this->request = $request;
    }

    /**
     * @OA\Get(
     *   path="/api/admin/order/get",
     *   summary="Get Data Order",
     *   tags={"Setup Order (BackOffice)"},
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
                    'trx_billing.id',
                    'trx_billing.billing_id',
                    'trx_billing.total_pax',
                    'trx_billing.first_name',
                    'trx_billing.last_name',
                    'trx_billing.status',
                   DB::raw('max(trx_billing_dtl.ticketdatefrom) as ticketdatefrom'),
                   DB::raw('max(trx_billing_dtl.createdate) as createdate'),
                   DB::raw('max(stp_dest.destname) as destname')
                )
                ->groupBy(
                    'trx_billing.id',
                    'trx_billing.billing_id',
                    'trx_billing.total_pax',
                    'trx_billing.first_name',
                    'trx_billing.last_name',
                    'trx_billing.status'
                );

        if($this->request->get('condition')){
            $data->where(function($query){
               $query->where('trx_billing.billing_id','like', '%'.$this->request->get('condition').'%');
               $query->orWhere('stp_dest.destname','like', '%'.$this->request->get('condition').'%');
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
            $data->orderBy('trx_billing.first_name', 'DESC');
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

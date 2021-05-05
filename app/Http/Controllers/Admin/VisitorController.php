<?php
namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use  App\BillingVisitor;
use  App\Destination;

class  VisitorController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        $this->middleware('auth');
        $this->request = $request;
    }

    /**
     * @OA\Get(
     *   path="/api/admin/visitor/get",
     *   summary="Get Data Order",
     *   tags={"Setup Ticket (BackOffice)"},
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
        $data = BillingVisitor::with(['visitor_detail'])
                ->leftJoin('trx_billing',function($join){
                    $join->on('trx_billing_visitor.billing_id' , '=','trx_billing.billing_id' );
                })
                ->leftJoin('trx_ticket',function($join){
                    $join->on('trx_billing_visitor.billing_id' , '=','trx_ticket.billing_id' );
                    $join->on('trx_billing_visitor.visitor_name' , '=','trx_ticket.visitor_name' );
                })
                ->select(
                    // DB::raw("IF(trx_billing_visitor.visitor_name != Null, trx_billing_visitor.visitor_name , '-') as visitor_name"),
                    'trx_billing_visitor.visitor_name',
                    'trx_billing_visitor.phone', 
                    'trx_billing_visitor.email',
                    'trx_billing_visitor.nationality',
                    // 'trx_billing_visitor.buyerid',
                    'trx_billing.pr_holder',
                    DB::raw('COUNT(trx_billing_visitor.visitor_name) as total_attend')
                 )
                ->where('trx_ticket.status','=',2)
                ->groupBy(
                    'trx_billing_visitor.visitor_name',
                    'trx_billing_visitor.phone', 
                    'trx_billing_visitor.email',
                    'trx_billing_visitor.nationality',
                    'trx_billing.pr_holder'
                    // 'trx_billing_visitor.buyerid'
                    // 'trx_billing_visitor.phone', 
                    // 'trx_billing_visitor.email',
                    // 'trx_billing_visitor.nationality',
                    // 'trx_billing.pr_holder'
                    )
                ;

        if($this->request->get('condition')){
            $data->where(function($search){
                $search->where('trx_billing_visitor.visitor_name','like','%'.$this->request->get('condition').'%');
                $search->orWhere('trx_billing_visitor.phone','like','%'.$this->request->get('condition').'%');
                $search->orWhere('trx_billing_visitor.email','like','%'.$this->request->get('condition').'%');
                $search->orWhere('trx_billing_visitor.nationality','like','%'.$this->request->get('condition').'%');
            });
        }
        //ORDER BY
        if($this->request->get('dir')){
            $data->orderBy($this->request->get('dir'), $this->request->get('sort'));
        }else{
            $data->orderBy('trx_billing_visitor.visitor_name', 'DESC');
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
            $value->pr_holder = ($value->pr_holder == 1)? 'Y' : 'N'; 
        }
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['totalRow'] = $total;
        $res['totalPages'] = $pages;
        $res['data'] = $grid;
        return response()->json($res, 200);
    }
}

<?php
namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use  App\Member;
use  App\Receipt;
use  App\Ticket;
use  App\Destination;
use  App\DestinationType;
use  App\Billing;
use  App\BillingDetail;

class  DashboardController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        $this->middleware('auth');
        $this->request = $request;
    }

    /**
     * @OA\Get(
     *   path="/api/admin/dashboard/summary",
     *   summary="Get Data Member",
     *   tags={"DASHBOARD"},
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
    public function summary()
    {
        $member = Member::where('stp_member.status','=',1);
        $sales = Receipt::select(DB::raw('SUM(amount) as total_amount'))
                ->first();
        $ticket = Ticket::orderBy('status', 'ASC');
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['member'] = $member->count();
        $res['sales'] = $sales->total_amount;
        $res['ticket'] = $ticket->count();
        return response()->json($res, 200);
    }

    /**
     * @OA\Get(
     *   path="/api/admin/dashboard/diagram",
     *   summary="Get Data DIAGRAM",
     *   tags={"DASHBOARD"},
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
    public function diagram()
    {   
        $destination = DestinationType::get();
        $ticket = Ticket::leftJoin('stp_dest', function($join){
                    $join->on('trx_ticket.destid','=', 'stp_dest.destid');
                })
                ->select('stp_dest.desttype', DB::raw('COUNT(trx_ticket.ticket_id) as total'))
                ->groupBy('stp_dest.desttype')
                ->orderBy('stp_dest.desttype', 'ASC')->get();
        
        $labels = array();
        $data = array();
        foreach($destination as $k => $dest)
        {
            array_push($labels, $dest->desttype);
            foreach($ticket as $key => $value)
            {
                if($value->desttype == $dest->id)
                {
                    array_push($data, $value->total);
                }else{
                    array_push($data, 0);
                }   
                
            }
        }
       
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['labels'] = $labels;
        $res['data'] = $data;
        return response()->json($res, 200);
    }

     /**
     * @OA\Get(
     *   path="/api/admin/dashboard/order",
     *   summary="Get Data DIAGRAM",
     *   tags={"DASHBOARD"},
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
    public function order()
    {   
        $order = Billing::count();
        $receipt = Receipt::count();
        
        $labels = array('Order', 'Payment');
        $data = array($order, $receipt);
       
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['labels'] = $labels;
        $res['data'] = $data;
        return response()->json($res, 200);
    }

    /**
     * @OA\Get(
     *   path="/api/admin/dashboard/visitor",
     *   summary="Get Data DIAGRAM",
     *   tags={"DASHBOARD"},
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
    public function visitor()
    {   
        $ticket = Ticket::leftJoin('trx_billing',function($join){
            $join->on('trx_ticket.billing_id','=','trx_billing.billing_id');
        })
        ->select('trx_billing.country', DB::raw('COUNT(trx_ticket.ticket_id) as total'))
        ->groupBy('trx_billing.country');
        $data_ = $ticket->get();
        $labels = array();
        $data = array();
        foreach($data_ as $key => $value)
        {
            array_push($labels, $value->country);
            array_push($data, $value->total);
        }
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['labels'] = $labels;
        $res['data'] = $data;
        return response()->json($res, 200);
    }

     /**
     * @OA\Get(
     *   path="/api/admin/dashboard/sales",
     *   summary="Get Data DIAGRAM",
     *   tags={"DASHBOARD"},
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
    public function sales_comparasion()
    {   
        $year = strtotime(date('Y-01-01'). ' -1 year');
        $end_year = strtotime(date('Y-12-31'). ' -1 year');
        $before = date('Y-m-d',$year);
        $before_end = date('Y-m-d',$end_year);
        $current = date('Y-01-01'); 
        $current_end = date('Y-12-31'); 
        $before_query = Receipt::whereDate('createdate','>=',$before)
                        ->whereDate('createdate','<=',$before_end)
                        ->select(
                            DB::raw('Month(createdate) as month'),
                            DB::raw('COUNT(receipt_id) as total')
                            )
                        ->orderBy(DB::raw('Month(createdate)'), 'ASC')
                        ->groupBy(DB::raw('MONTH(createdate)'))
                        ;
        $before_data = $before_query->get();
        $current_query = Receipt::whereDate('createdate','>=',$current)
                    ->whereDate('createdate','<=',$current_end)
                    ->select(
                        DB::raw('Month(createdate) as month'),
                        DB::raw('COUNT(receipt_id) as total')
                    )
                    ->orderBy(DB::raw('Month(createdate)'), 'ASC')
                    ->groupBy(DB::raw('Month(createdate)'));
        $current_data =    $current_query->get();
        $labels = array('January', 'February', 'March', 'April', 'May', 'June', 'July','August','September','October','November', 'December');
        $before_result = array();
        $current_result = array();
        foreach($labels as $be => $label)
        {
            array_push($before_result, $this->check_month($before_data, 'month', 'total', ($be + 1)));
            array_push($current_result, $this->check_month($current_data, 'month', 'total', ($be + 1)));
        }

        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['labels'] = $labels;
        $res['before'] = $before_result;
        $res['current'] = $current_result;
        return response()->json($res, 200);
    }

    function check_month($data, $field, $field_result, $val)
    {
        $status = 0;
        foreach($data as $key => $value)
        {
            if($value->{$field}== $val){
                $status = $value->{$field_result};
            }
        }
        return $status;
    }

}

<?php
namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use  App\Ticket;
use  App\Destination;

class  TicketController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        $this->middleware('auth');
        $this->request = $request;
    }

    /**
     * @OA\Get(
     *   path="/api/admin/ticket/get",
     *   summary="Get Data Ticket",
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
        $data = Ticket::leftJoin('stp_dest', function($join){
                    $join->on('stp_dest.destid','=','trx_ticket.destid');
                })
                ->leftJoin('trx_billing', function($join){
                    $join->on('trx_billing.billing_id','=','trx_ticket.billing_id');
                })
                ->leftJoin('trx_billing_dtl', function($join){
                        $join->on('trx_billing_dtl.billing_id','=','trx_ticket.billing_id');
                    })
                ->select(
                    'trx_ticket.*',
                    'stp_dest.destname',
                    'trx_billing.country',
                    'trx_billing.pr_holder',
                    'trx_billing_dtl.ticketdatefrom as date_togo',
                    'trx_billing_dtl.createdate'
                )
                ->distinct()
             ;

        if(!empty($this->request->get('condition'))){
            $data->where('stp_dest.destname','like','%'.$this->request->get('condition').'%');
            $data->orWhere('trx_billing.billing_id','like','%'.$this->request->get('condition').'%');
            $data->orWhere('trx_ticket.ticket_id','like','%'.$this->request->get('condition').'%');
            $data->orWhere('trx_ticket.visitor_name','like','%'.$this->request->get('condition').'%');
            $data->orWhere('trx_ticket.phone','like','%'.$this->request->get('condition').'%');
            $data->orWhere('trx_ticket.email','like','%'.$this->request->get('condition').'%');
            $data->orWhere('trx_ticket.nationality','like','%'.$this->request->get('condition').'%');
        }
        
         if($this->request->get('start_date')){
             $data->whereDate('trx_billing_dtl.ticketdatefrom','>=', $this->request->get('start_date'));
             $data->whereDate('trx_billing_dtl.ticketdatefrom','<=', $this->request->get('end_date'));
        }
        //ORDER BY
        if($this->request->get('dir')){
            $data->orderBy($this->request->get('dir'), $this->request->get('sort'));
        }else{
            $data->orderBy('trx_ticket.ticketdatefrom', 'DESC');
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
            $value->download_link = 'http://'.$_SERVER['SERVER_NAME'].'/public/download/ticket?ticket_id='.$value->ticket_id;
            switch($value->status)
            {
                case 1:
                    $value->status = 'OPEN';
                    break;
                case 2 : 
                    $value->status = 'CHECK IN';
                    break;
                case 3:
                    $value->status = 'EXPIRED';
                    break;
                default:
                    $value->status = 'OPEN';
                    break;
            }
        }
        // DB::select("update trx_ticket set ticketdatefrom = '2021-03-01', ticketdateto='2021-03-01',status=0 where ticket_id = 'T0479941701233' ");
        // DB::select("update trx_ticket set ticket_id = 'T04917024932117', ticketdatefrom = '2021-03-01', ticketdateto='2021-03-01', status=0 where visitor_name = 'huza' ");
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['totalRow'] = $total;
        $res['totalPages'] = $pages;
        $res['data'] = $grid;
        return response()->json($res, 200);
    }
    
    /**
     * @OA\Get(
     *   path="/api/admin/ticket/scan",
     *   summary="Get Data Ticket",
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
    public function scan()
    {
        $data = Ticket::leftJoin('stp_dest', function($join){
                    $join->on('stp_dest.destid','=','trx_ticket.destid');
                })
                ->leftJoin('trx_billing', function($join){
                    $join->on('trx_billing.billing_id','=','trx_ticket.billing_id');
                })
             ->leftJoin('trx_billing_dtl', function($join){
                    $join->on('trx_billing_dtl.billing_id','=','trx_ticket.billing_id');
                })
                ->select(
                    'trx_ticket.*',
                    'stp_dest.destname',
                    'trx_billing.country',
                    'trx_billing.pr_holder'
                    
                    )
                ->whereDate('trx_billing_dtl.ticketdatefrom', '=', date('Y-m-d'))
                ->where('trx_ticket.ticket_id','=',$this->request->get('ticket_id'))
             ;
        
        if($this->request->get('condition')){
            $data->where('stp_dest.destname','like','%'.$this->request->get('condition').'%');
        }
        //ORDER BY
        if($this->request->get('dir')){
            $data->orderBy($this->request->get('dir'), $this->request->get('sort'));
        }else{
            $data->orderBy('trx_ticket.ticketdatefrom', 'DESC');
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
            $value->download_link = 'http://'.$_SERVER['SERVER_NAME'].'/public/download/ticket?ticket_id='.$value->ticket_id;
        }
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['totalRow'] = $total;
        $res['totalPages'] = $pages;
        $res['data'] = $grid;
        return response()->json($res, 200);
    }
    
    /**
     * @OA\Post(
     *   path="/api/admin/ticket/scan",
     *   summary="Scan Ticket",
     *   tags={"Setup Ticket (BackOffice)"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *               @OA\Property(property="ticket_id", type="string")
     *          )
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
    public function update()
    {
        date_default_timezone_set('Asia/Jakarta');
        $validator = Validator::make($this->request->all(), [
            'ticket_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            $fields = '';
            foreach($validator->errors()->all() as $key => $value){
                $fields .= 'The '.$value.', ';
            }
            $res['code'] = 400;
            $res['error'] = $fields;
            return response()->json($res, 400);
        }

        try {
            $check = Ticket::leftJoin('stp_dest', function($join){
                        $join->on('stp_dest.destid','=','trx_ticket.destid');
                    })
                    ->leftJoin('trx_billing', function($join){
                        $join->on('trx_billing.billing_id','=','trx_ticket.billing_id');
                    })
                    ->leftJoin('trx_billing_dtl', function($join){
                        $join->on('trx_billing_dtl.billing_id','=','trx_ticket.billing_id');
                    })
                    ->whereDate('trx_billing_dtl.ticketdatefrom', '=', date('Y-m-d'))
                    ->where('trx_ticket.ticket_id', '=', $this->request->input('ticket_id'))
                    ->where('stp_dest.desttype','=',$this->request->get('gate'))
                    ->where('trx_ticket.status','=',1)
                    ->select(
                        'trx_ticket.*',
                        'stp_dest.destname',
                        'trx_billing.country',
                        'trx_billing_dtl.createdate'
                        )
            ->first();
            if(empty($check)){
                 $check = Ticket::leftJoin('stp_dest', function($join){
                        $join->on('stp_dest.destid','=','trx_ticket.destid');
                    })
                    ->leftJoin('trx_billing', function($join){
                        $join->on('trx_billing.billing_id','=','trx_ticket.billing_id');
                    })
                    ->leftJoin('trx_billing_dtl', function($join){
                        $join->on('trx_billing_dtl.billing_id','=','trx_ticket.billing_id');
                    })
                    ->select(
                        'trx_ticket.*',
                        'stp_dest.destname',
                        'stp_dest.desttype',
                        'trx_billing.country',
                        'trx_billing_dtl.createdate',
                        'trx_billing_dtl.ticketdatefrom as datetogo'
                        )
                    ->where('ticket_id', '=', $this->request->input('ticket_id'))
                    ->first();
                if(empty($check)){
                        $res['code'] = 200;
                        $res['data'] = $check;
                        $res['message'] = 'Ticket Not Valid';
                }else{
                    if(@$check->desttype != $this->request->get('gate')){
                        $res['code'] = 200;
                        $res['data'] = $check;
                        $res['message'] = 'You entered at the wrong gate.';
                    }elseif(@$check->datetogo != date('Y-m-d')){
                        $res['code'] = 200;
                        $res['data'] = $check;
                        $res['message'] = 'Date not Valid.';
                    }else{
                        $res['code'] = 200;
                        $res['data'] = $check;
                        $res['message'] = 'Ticket has scanned.';
                    }
                }
             
                return response()->json($res,200);
            }else{
                 $update = Ticket::where('ticket_id', '=', $this->request->input('ticket_id'))
                    ->where('status','=',1)
                    ->update([
                        'status' => 2,
                        'ticketdatefrom' => date('Y-m-d H:i:s')
                    ]);
                $check = Ticket::leftJoin('stp_dest', function($join){
                        $join->on('stp_dest.destid','=','trx_ticket.destid');
                    })
                    ->leftJoin('trx_billing', function($join){
                        $join->on('trx_billing.billing_id','=','trx_ticket.billing_id');
                    })
                    ->leftJoin('trx_billing_dtl', function($join){
                        $join->on('trx_billing_dtl.billing_id','=','trx_ticket.billing_id');
                    })
                    ->select(
                        'trx_ticket.*',
                        'stp_dest.destname',
                        'stp_dest.desttype',
                        'trx_billing.country',
                        'trx_billing_dtl.createdate',
                        'trx_billing_dtl.ticketdatefrom as datetogo'
                        )
                    ->where('ticket_id', '=', $this->request->input('ticket_id'))
                    ->first();
            }
           
            $res['code'] = 201;
            $res['data'] = $check;
            $res['message'] = 'Data Success Updated.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }
    }
    
    public function history()
    {
        $query = Ticket::leftJoin('stp_dest', function($join){
                    $join->on('stp_dest.destid','=','trx_ticket.destid');
                })
                // ->leftJoin('trx_billing', function($join){
                //         $join->on('trx_billing.billing_id','=','trx_ticket.billing_id');
                // })
                ->leftJoin('trx_billing_dtl', function($join){
                    $join->on('trx_billing_dtl.billing_id','=','trx_ticket.billing_id');
                })
                ->select(
                    'trx_ticket.id',
                    'trx_ticket.ticket_id',
                    'trx_ticket.ticketdatefrom',
                    'trx_ticket.visitor_name',
                    'trx_ticket.nationality as country',
                    //  DB::raw('MAX(stp_dest.destname) as destname'),
                    //  DB::raw('MAX(trx_billing_dtl.createdate) as createdate')
                    'stp_dest.destname',
                    'trx_billing_dtl.createdate'
                )
                ->groupBy(
                    'trx_ticket.ticket_id',
                    'trx_ticket.ticketdatefrom',
                    'trx_ticket.visitor_name',
                    'trx_ticket.nationality',
                    'stp_dest.destname',
                    'trx_billing_dtl.createdate',
                    'trx_ticket.id'
                )
                ->where('trx_ticket.status','=',2)
                ->whereDate("trx_ticket.ticketdatefrom","=",date('Y-m-d',strtotime($this->request->get('date'))))
                ->where('stp_dest.desttype','=',$this->request->get('gate'))
                ->orderBy('trx_ticket.ticketdatefrom', 'DESC')
             ;
        
        if($this->request->get('search')){
            $query->where('stp_dest.destname','like','%'.$this->request->get('search').'%');
        }
       
        //PAGINATION
        if($this->request->get('offset')){
            $start = $this->request->get('offset');
            $limit = $this->request->get('limit');
            $query->offset($start);
            $query->limit($limit);
        }
        $grid = $query->get();
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['data'] = $grid;
        return response()->json($res, 200);
    }
}

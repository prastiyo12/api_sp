<?php
namespace App\Http\Controllers;

use Validator;
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
     * @OA\Post(
     *   path="/api/admin/ticket/scan",
     *   summary="Scan Ticket",
     *   tags={"TICKET"},
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
                ->select(
                    'trx_ticket.*',
                    'stp_dest.destname'
                    )->where('ticket_id', '=', $this->request->input('ticket_id'))
             ->whereDate('ticketdatefrom', '=', date('Y-m-d'))
             ->where('status','=',0)
            ->first();
            if(!$check){
                 $check = Ticket::leftJoin('stp_dest', function($join){
                        $join->on('stp_dest.destid','=','trx_ticket.destid');
                    })
                    ->select(
                        'trx_ticket.*',
                        'stp_dest.destname'
                        )->where('ticket_id', '=', $this->request->input('ticket_id'))
                    ->first();
                $res['code'] = 200;
                $res['data'] = $check;
                $res['message'] = 'Ticket has scanned.';
                return response()->json($res,200);
            }else{
                 $update = Ticket::where('ticket_id', '=', $this->request->input('ticket_id'))
                    ->where('status','=',0);
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
    
    /**
     * @OA\Get(
     *   path="/api/admin/ticket/hitory",
     *   summary="TICKET HISTORY",
     *   tags={"TICKET"},
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
    public function history()
    {
        var_dump($this->request->input('gate'));
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
                    'stp_dest.desttype',
                    'trx_billing.country',
                    'trx_billing_dtl.createdate'
                )
                ->where('trx_ticket.status','=',2)
                ->whereRaw("DATE(trx_ticket.ticketdatefrom) = ".$this->request->get('date'))
                ->where('stp_dest.desttype','=',$this->request->get('gate'))
                ->orderBy('trx_ticket.ticketdatefrom', 'DESC')
             ;
        
        if($this->request->get('search')){
            $data->where('stp_dest.destname','like','%'.$this->request->get('search').'%');
        }
       
        //PAGINATION
        if($this->request->get('offset')){
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
}

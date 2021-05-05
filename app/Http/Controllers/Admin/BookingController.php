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

class  BookingController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        $this->middleware('auth');
        $this->request = $request;
    }

    /**
     * @OA\Get(
     *   path="/api/admin/booking/get",
     *   summary="Get Data Booking",
     *   tags={"Setup Booking (BackOffice)"},
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
                    'trx_billing.status_approved',
                    'trx_billing.status',
                    DB::raw('max(trx_billing_dtl.ticketdatefrom) as ticketdatefrom'),
                    DB::raw('max(trx_billing_dtl.createdate) as createdate'),
                    DB::raw('max(stp_dest.destname) as destname')
                    )
                ->where('trx_billing.pr_holder','=',1)
                ->groupBy(
                    'trx_billing.id',
                    'trx_billing.billing_id',
                    'trx_billing.total_pax',
                    'trx_billing.first_name',
                    'trx_billing.last_name',
                    'trx_billing.status_approved',
                    'trx_billing.status'
                );;

         if($this->request->get('condition')){
            $data->where(function($query){
               $query->where('stp_dest.destname','like', '%'.$this->request->get('condition').'%');
               $query->orWhere('trx_billing.first_name','like','%'.$this->request->get('condition').'%');
               $query->orWhere('trx_billing.last_name','like','%'.$this->request->get('condition').'%');
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
    
    /**
     * @OA\Post(
     *   path="/api/admin/booking/approved",
     *   summary="Booking Approved",
     *   tags={"Setup Booking (BackOffice)"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *               @OA\Property(property="billing_id", type="string")
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
    public function approved()
    {
        date_default_timezone_set('Asia/Jakarta');
        $validator = Validator::make($this->request->all(), [
            'billing_id' => 'required|string'
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
             $update = Billing::where('billing_id', '=', $this->request->input('billing_id'))
            ->where('pr_holder', '=', 1)
            ->update([
                'status_approved' =>$this->request->input('status_approved')
            ]);
           
            $res['code'] = 201;
            $res['message'] = 'Data Success Updated.';
            return response()->json($res, 201);
        } catch (\Exception $e) {
            $res['code'] = 500;
            $res['message'] = $e->getMessage();
            return response()->json($res, 500);
        }
    }
}

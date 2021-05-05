<?php
namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;
use  App\Member;
use  App\Negara;

class  MemberController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        $this->middleware('auth');
        $this->request = $request;
    }

    /**
     * @OA\Get(
     *   path="/api/admin/member/get",
     *   summary="Get Data Member",
     *   tags={"Setup Member (BackOffice)"},
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
        $data = Member::leftJoin('stp_country', function($join){
                    $join->on('stp_country.id','=','stp_member.country');
                })
                ->select('stp_member.*','stp_country.country_name')
                ->where('stp_member.status','=',1);

        if($this->request->get('condition')){
            $data->where('stp_member.first_name','like','%'.$this->request->get('condition').'%');
            $data->orWhere('stp_member.first_name','like','%'.$this->request->get('condition').'%');
            $data->orWhere('stp_member.last_name','like','%'.$this->request->get('condition').'%');
            $data->orWhere('stp_member.country','like','%'.strtoupper($this->request->get('condition')).'%');
            $data->orWhere('stp_member.email','like','%'.$this->request->get('condition').'%');
            $data->orWhere('stp_member.phone','like','%'.$this->request->get('condition').'%');
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
        
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['totalRow'] = $total;
        $res['totalPages'] = $pages;
        $res['data'] = $grid;
        return response()->json($res, 200);
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
}

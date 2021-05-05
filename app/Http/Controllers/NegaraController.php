<?php
namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use  App\Negara;

class  NegaraController extends Controller
{
    private $request;

    public function __construct( Request $request)
    {
        $this->request = $request;
    }

    /**
     * @OA\Get(
     *   path="/public/negara/get",
     *   summary="Get Negara",
     *   tags={"Public"},
     *   @OA\Parameter(
     *       in ="header",
     *       name="Authorization",
     *       required=true,
     *       description="Bearer {access-token}",
     *       @OA\Schema(
     *          type="string"
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
    public function get()
    {
        $data = Negara::get();
        $restuls = array();
        foreach($data as $key => $value){
            $object = new \stdClass;
            $object->id = $value->id;
            $object->kode = $value->country_code;
            $object->negara = $value->country_name;
            array_push($restuls, $object);
        }
        $res['code'] = 200;
        $res['message'] = "Data Stored.";
        $res['data'] = $restuls;
        return response()->json($res, 200);
    }
    
}

<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Billing;
use App\BillingDetail;
use App\Member;
use App\Helpers\CustomHelper;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $schedule->call(function () {
            $config = DB::table('stp_config')->where('type','=','remind-billing')->first();
            $date = date('Y-m-d', strtotime('+'.$config->duration.' days'));
            $user = BillingDetail::leftJoin('stp_member',function($join){
                     $join->on('stp_member.buyerid','=','trx_billing_dtl.buyerid');
                 })
                 ->whereDate('trx_billing_dtl.ticketdatefrom','=',$date)
                 ->whereNotNull('stp_member.active_key')
                 ->groupBy('stp_member.active_key')
                 ->pluck('stp_member.active_key')->toArray();
            $data = [
              "notif_type"=> 3,
              "data"=>array(
                    "id"=> 1,
                    "id_type"=> 1,
                    "title"=> "Sabah Parks Reminder",
                    "content"=> "Visiting time is coming.",
                    "date_created "=> $date,
                    "path"=> ""
                )
            ];
            $push = CustomHelper::sendpush('Sabah Parks Reminder', "Visiting time is coming.", $data, array_filter($user));
        })->dailyAt('07:00');
        
        $schedule->call(function () {
            $config = DB::table('stp_config')->where('type','=','billing')->first();
            $date = date('Y-m-d', strtotime('-'.$config->duration.' days'));
            $user = BillingDetail::leftJoin('trx_billing',function($join){
                        $join->on('trx_billing.billing_id','=','trx_billing_dtl.billing_id');
                    })
                    ->leftJoin('stp_member',function($join){
                         $join->on('stp_member.buyerid','=','trx_billing_dtl.buyerid');
                     })
                    ->whereDate('trx_billing_dtl.createdate','=',$date)
                    ->where('trx_billing.status','=',1)
                    ->whereNotNull('stp_member.active_key')
                    ->groupBy('stp_member.active_key')
                    ->pluck('stp_member.active_key')
                    ->toArray();
            $billing = BillingDetail::leftJoin('trx_billing',function($join){
                    $join->on('trx_billing.billing_id','=','trx_billing_dtl.billing_id');
                })
                 ->whereDate('trx_billing_dtl.createdate','=',$date)
                 ->where('trx_billing.status','=',1)
                 ->groupBy('trx_billing_dtl.billing_id')
                 ->pluck('trx_billing_dtl.billing_id')->toArray();
            $update_billing = Billing::where('pr_holder','=',2)->whereIn('billing_id',$billing)->update(['status'=>3]);
            $update_billing = Billing::where('pr_holder','=',1)->where('status_approved','=',2)->whereIn('billing_id',$billing)->update(['status'=>3]);
            $data = [
              "notif_type"=> 3,
              "data"=>array(
                    "id"=> 1,
                    "id_type"=> 1,
                    "title"=> "Sabah Parks Information",
                    "content"=> "Your Billing Expired.",
                    "date_created "=> $date,
                    "path"=> ""
                )
            ];
            $push = CustomHelper::sendpush('Sabah Parks Information', "Your Billing Expired.", $data, array_filter($user));
        })->dailyAt('07:00');
       
    }
}

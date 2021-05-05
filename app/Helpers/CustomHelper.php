<?php
namespace App\Helpers;
use Carbon\Carbon;
use GuzzleHttp\Client;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Illuminate\Http\Request;
use FCM as FCM;

class CustomHelper
{
    public static function intervalTime($from, $to)
    {
        $d1 = new Carbon($from);
        $d2 = new Carbon($to);
        $interval = $d1->diff($d2);
        return $interval;
    }

    public static function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    public static function formatDate($date)
    {
        date_default_timezone_set('Asia/Jakarta');
        if (strtotime($date) < strtotime(date('Y-m-d H:i'))) {
            $res['success'] = false;
            $res['message'] = 'Time cannot lower than time now';
            return response()->json($res, 500);
        }

        return date('Y-m-d H:i:s', strtotime($date));
    }

    public static function getInfoMap($date, $ori_lat, $ori_long, $dest_lat, $dest_long, $mode)
    {
        $newDateTime = date('Y-m-d h:i:s A', strtotime($date));
        $unixTime = strtotime($newDateTime);

        $url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $ori_lat . "," . $ori_long . "&destination=" . $dest_lat . "," . $dest_long . "&key=AIzaSyCGZRN4xG-6Gh3dNO-kG1m0lBd71wGZBdE&mode=" . $mode . "&departure_time=" . $unixTime;

        $ch = curl_init();
        $timeout = 0;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data_g = curl_exec($ch);
        $response = json_decode($data_g, true);
        curl_close($ch);

        if ($response["status"] == "REQUEST_DENIED") {
            $res['success'] = false;
            $res['message'] = 'You must enable Billing on the Google Cloud';
            return response()->json($res, 401);
        }

        return $response;
    }

    public static function convertTimeMap($mapInfo, $date)
    {
        $arr = [' hour ', ' hours ', ' min'];
        $repl = [':', ':', ''];
        $newStr = str_replace($arr, $repl, $mapInfo["routes"][0]["legs"][0]["duration"]["text"]);
        $fixStr = str_replace('s', '', $newStr);
        $parts = array_map(function ($num) {
            return (int) $num;
        }, explode(':', $fixStr));
        $timeA = new \DateTime($date);
        $timeB = new \DateInterval(sprintf('PT%uM', $parts[0]));
        if (count($parts) != 1) {
            $timeB = new \DateInterval(sprintf('PT%uH%uM', $parts[0], $parts[1]));
        }
        $timeA->add($timeB);
        $timeDest = $timeA->format('Y-m-d H:i:s');
        $arrival = $timeDest;

        return $arrival;
    }

    public static function convertOrigin($origin)
    {
        $ori = (string)$origin;
        $ori = strlen(substr(strrchr($ori, "."), 1));
        $num = '00000000000000';
        $num = substr($num, 0, $ori);
        $val = '1' . $num;
        $val = 1/(int)$val;
        $val_c = ($origin) + $val;

        return $val_c;
    }

    public static function upload_image($image)
    {
        $ori_image = $image->getClientOriginalName();
        $ori_image_arr = explode('.', $ori_image);
        $image_ext = end($ori_image_arr);
        $image_path = base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR;

        return [$image_ext, $image_path];
    }

    public static function tmminConnect($data, $url)
    {
        $client = new Client(['verify' => false, 'debug' => false]);

        $params['headers'] = ['Content-Type' => 'application/x-www-form-urlencoded'];
        $params['form_params'] = array('username' => $data['username'], 'password' => $data['password'], 'mobileno' => $data['phone']);
        $request = $client->post($url, $params);

        $response = $request->getBody()->getContents();
        $resp = json_decode($response);

        return $resp;
    }

    public static function formatColumn($sorting, $filter)
    {
        $field = [];
        $val = [];
        $column = 'updated_at';
        $sort = 'desc';
        if (!empty($sorting)) {
            $arr_sort = preg_replace('/[^A-Za-z0-9\-\,\_]/', '', $sorting);
            $arr_sort = explode(',', $arr_sort);
            $column = $arr_sort[0];
            $sort = $arr_sort[1];
        }

        if (!empty($filter)) {
            $filter = json_decode($filter);
            foreach ($filter as $key => $value) {
                $field[] = $key;
                $val[] = $value;
            }
        }

        if (count($field) == 0) {
            $field[0] = '';
        }

        if (count($val) == 0) {
            $val[0] = '';
        }

        return [$field[0], $val[0], $column, $sort, $filter];
    }

    public static function formatUserColumn($sorting, $filter)
    {
        $column = 'updated_at';
        $sort = 'desc';
        if (!empty($sorting)) {
            $arr_sort = preg_replace('/[^A-Za-z0-9\-\,\_]/', '', $sorting);
            $arr_sort = explode(',', $arr_sort);
            $column = $arr_sort[0];
            $sort = $arr_sort[1];
        }

        if (!empty($filter)) {
            $u = '';
            if ($filter !== '{}') {
                $u = json_decode($filter);
            }
        }

        $d = '';
        if (!empty($u)) {
            $d = $u->fullname;
        }

        return [$d, $column, $sort];
    }

    public static function formatPage($range, $data, $total)
    {
        $perPage = 10;
        $d = $data;
        if (!empty($range)) {
            $arr = preg_replace('/[^A-Za-z0-9\-\,]/', '', $range);
            $arr = explode(',', $arr);
            // if ($arr[1] >= count($cars)) {
            //     $arr[1] = $arr[0];
            // }
            $perPage = ($arr[1] - $arr[0]) + 1;
            $d = [];
            for ($i=$arr[0]; $i <= $arr[1] ; $i++) {
                if ($i >= count($data)) {
                    break;
                } else {
                    $d[] = $data[$i];
                }
            }

            if ($arr[1] > $total) {
                $arr[1] = $total - 1;
            }
        } elseif (count($d) < $perPage) {
            $arr[0] = 0;
            $arr[1] = count($d) - 1;
        } else {
            $arr[0] = 0;
            $arr[1] = 9;
        }

        return [$d, $arr[0], $arr[1]];
    }

    public static function getHeader($start, $end, $total)
    {
        $headers['Content-Range'] = sprintf('items %d-%d/%d', ($start + 1), ($end + 1), $total);
        $headers['Access-Control-Expose-Headers'] = 'Content-Range';

        return $headers;
    }

    public static function sendpush($title, $body, $dataarray, $token)
    {
        $optionBuiler = new OptionsBuilder();
        $optionBuiler->setTimeToLive(60 * 20);

        $notificationBuilder = new PayloadNotificationBuilder($title);
        $notificationBuilder->setBody($body)
            ->setSound('');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData($dataarray);
        
        $option = $optionBuiler->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        $token = $token;
        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
        
        return $downstreamResponse;
    }
}

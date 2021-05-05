<?php

use \Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="Sabah Park Api Documentation",
 *   @OA\Contact(
 *     email="prastiyo.beka12@gmail.com"
 *   )
 * )
 * @OA\Schemes(format="http")
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      in="header",
 *      name="Authorization",
 *      type="apiKey",
 *      scheme="bearer",
 *      bearerFormat="JWT",
 * ),
 */



$router->group(['prefix' => 'public', 'middleware' => 'cors'], function ($router) {
    $router->group(['prefix' => 'negara'], function ($router) {
        $router->get('get', 'NegaraController@get');
    });

    $router->group(['prefix' => 'download'], function ($router) {
        $router->get('ticket', 'TestController@ticket');
        $router->get('receipt', 'TestController@receipt');
        $router->get('member','ReportController@download_get');
        $router->get('order','ReportController@download_order');
        $router->get('booking','ReportController@download_booking');
        $router->get('billing','ReportController@download_billing');
        $router->get('sales','ReportController@download_sales');
        $router->get('payment','ReportController@download_payment');
    });
    $router->get('test-email', 'TestController@email');
    $router->get('send-notif', 'TestController@send_notif');
    $router->get('billing', 'TestController@billing');
    $router->get('ticket', 'TestController@ticket');
    $router->post('notif', 'TestController@notification');
    $router->get('report', 'TestController@report');
    //DASHBOARD
    $router->get('top-destination', 'DashboardController@top_destination');
    $router->get('news-event', 'DashboardController@news_event');
    $router->get('banner-promo', 'DashboardController@banner_promo');
    
    //PAYMENT
    $router->get('payment', 'DestinationTypeController@payment');
    $router->post('response', 'DestinationTypeController@response_payment');
    $router->post('backend', 'DestinationTypeController@response_backend');


    $router->group(['prefix' => 'order'], function ($router) {
        $router->get('destination', 'DestinationTypeController@get');
        $router->get('destination/id', 'DestinationTypeController@by_id');
        $router->get('type', 'DestinationTypeController@type');
        $router->get('get-event', 'DestinationTypeController@get_event');
    });
    $router->get('images/{filename}/{ext}', function ($filename, $ext) {
        // $path = storage_path('images/destination/'.$filename.'.'.$ext);
        $filename = str_replace('%20',' ',$filename);
        $path = env('IMG_PATH').$filename.'.'.$ext;
        if (!File::exists($path)) {
            $path = storage_path('images/default-photo.png');
        }
        $file = File::get($path);
        $type = File::mimeType($path);
    
        return response($file,200)->header('Content-Type', $type) ;
    });
    
    $router->get('logo/{filename}/{ext}', function ($filename, $ext) {
        $path = storage_path('images/'.$filename.'.'.$ext);
        if (!File::exists($path)) {
            $path = storage_path('images/default-photo.png');
        }
        $file = File::get($path);
        $type = File::mimeType($path);
    
        return response($file,200)->header('Content-Type', $type) ;
    });

    $router->get('images/gallery/{filename}/{ext}', function ($filename, $ext) {
        // $path = storage_path('images/gallery/'.$filename.'.'.$ext);
        $filename = str_replace('%20',' ',$filename);
        $path = env('IMG_PATH').$filename.'.'.$ext;
        if (!File::exists($path)) {
            $path = storage_path('images/default-photo.png');
        }
        $file = File::get($path);
        $type = File::mimeType($path);
    
        return response($file,200)->header('Content-Type', $type) ;
    });

    $router->get('default/{filename}/{ext}', function ($filename, $ext) {
        $path = storage_path('images/'.$filename.'.'.$ext);
        if (!File::exists($path)) {
            $path = storage_path('images/default-photo.png');
        }
        $file = File::get($path);
        $type = File::mimeType($path);
    
        return response($file,200)->header('Content-Type', $type) ;
    });
});

$router->group(['prefix' => 'auth'], function ($router) {
    $router->group(['prefix' => 'member'], function ($router) {
        $router->post('login', 'AuthMemberController@login');
        $router->post('register', 'AuthMemberController@register');
        $router->post('forgot-password', 'AuthMemberController@forgot_password');
        $router->get('active', 'AuthMemberController@active');
        $router->get('success', function () {
            return view('success');
        });
    });

    $router->group(['prefix' => 'admin'], function ($router) {
        $router->post('login', 'AuthController@login');
        $router->post('forgot-password', 'AuthController@forgot_password');
    });
    

    $router->get('/icon-success', function () {
        $path = storage_path('img/check-circle.gif');
        if (!File::exists($path)) {
            abort(404);
        }
        $file = File::get($path);
        $type = File::mimeType($path);
    
        return response($file,200)->header('Content-Type', $type) ;
    });
    
    $router->get('/icon-failed', function () {
        $path = storage_path('img/failed.png');
        if (!File::exists($path)) {
            abort(404);
        }
        $file = File::get($path);
        $type = File::mimeType($path);
    
        return response($file,200)->header('Content-Type', $type) ;
    });
});

$router->group(['prefix' => 'api', 'middleware'=>['cors']], function ($router) {
    $router->group(['prefix' => 'member', 'middleware' => ['auth:member','cors']], function ($router) {
        $router->get('profile', 'MemberController@profile');
        $router->post('logout', 'MemberController@logout');
        $router->post('change-password', 'MemberController@change_password');
        $router->post('edit-profile', 'MemberController@edit_profile');
        $router->get('inbox/get', 'InboxController@get');
        $router->post('inbox/delete', 'InboxController@delete');
        $router->post('order/process', 'OrderController@manage');
        $router->get('order/history', 'OrderController@get_history');
        $router->post('cart', 'OrderController@manage_cart');
        $router->get('get-invoice', 'OrderController@get_invoice');
        $router->get('get-cart', 'OrderController@get_cart');
        $router->post('delete-cart', 'OrderController@delete_cart');
        $router->post('payment', 'OrderController@payment');
        $router->post('fcm-token', 'FCMController@update_token');
        $router->post('review', 'ReviewController@review');
        
        
        $router->get('wishlist/get', 'WishlistController@get');
        $router->post('wishlist/add', 'WishlistController@manage');
        $router->post('wishlist/delete', 'WishlistController@delete');
        // $router->get('order/type', 'OrderController@type');
    });

    $router->group(['prefix' => 'admin', 'middleware' => ['auth','cors']], function ($router) {
        $router->get('profile', 'UserController@profile');
        $router->get('menu', 'UserController@menu');
        $router->post('logout', 'UserController@logout');
        $router->post('change-password', 'UserController@change_password');
        $router->post('edit-profile', 'UserController@edit_profile');
        
        $router->group(['prefix' => 'menu'], function ($router) {
            // $router->get('', 'UserController@menu');
            $router->get('get', 'UserController@get_menu');
            $router->get('role', 'UserController@get_role_all');
            $router->post('role-manage', 'UserController@manage_role');
            $router->post('role-delete', 'UserController@delete_role');
            $router->post('role-menu-manage', 'UserController@manage_role_menu');
        });
        
        $router->group(['prefix' => 'destination'], function ($router) {
            $router->get('get', 'DestinationController@get');
            $router->get('type', 'DestinationController@get_type');
            $router->post('manage', 'DestinationController@manage');
            $router->post('delete', 'DestinationController@delete');
            $router->post('delete-image', 'DestinationController@delete_images');
            $router->post('upload', 'DestinationController@upload');
            $router->post('upload-cover', 'DestinationController@upload_cover');
        });

        $router->group(['prefix' => 'gallery'], function ($router) {
            $router->get('get', 'GalleryController@get');
            $router->post('manage', 'GalleryController@manage');
            $router->post('delete', 'GalleryController@delete');
            $router->post('upload', 'GalleryController@upload');
        });

        $router->group(['prefix' => 'member'], function ($router) {
            $router->get('get', 'Admin\MemberController@get');
        });

        $router->group(['prefix' => 'order'], function ($router) {
            $router->get('get', 'Admin\OrderController@get');
        });

        $router->group(['prefix' => 'booking'], function ($router) {
            $router->get('get', 'Admin\BookingController@get');
            $router->post('approved', 'Admin\BookingController@approved');
        });

        $router->group(['prefix' => 'billing'], function ($router) {
            $router->get('get', 'Admin\BillingController@get');
        });
        
        $router->group(['prefix' => 'payment'], function ($router) {
            $router->get('get', 'Admin\IpayController@get');
        });

        $router->group(['prefix' => 'ticket'], function ($router) {
            $router->get('get', 'Admin\TicketController@get');
            $router->get('scan', 'Admin\TicketController@scan');
            $router->post('scan', 'Admin\TicketController@update');
            $router->get('history', 'Admin\TicketController@history');
        });

        $router->group(['prefix' => 'visitor'], function ($router) {
            $router->get('get', 'Admin\VisitorController@get');
        });

        $router->group(['prefix' => 'sales'], function ($router) {
            $router->get('get', 'Admin\SalesController@get');
        });

        $router->group(['prefix' => 'broadcast'], function ($router) {
            $router->get('get', 'Admin\BroadcastController@get');
            $router->post('manage', 'Admin\BroadcastController@manage');
            $router->post('delete', 'Admin\BroadcastController@delete');
            $router->post('send-email', 'Admin\BroadcastController@send_email');
            $router->post('send-inbox', 'Admin\BroadcastController@send_inbox');
        });

        $router->group(['prefix' => 'blog'], function ($router) {
            $router->get('get', 'Admin\NewsController@get');
            $router->post('manage', 'Admin\NewsController@manage');
            $router->post('delete', 'Admin\NewsController@delete');
            $router->post('send-email', 'Admin\NewsController@send_email');
            $router->post('send-inbox', 'Admin\NewsController@send_inbox');
        });

        $router->group(['prefix' => 'user'], function ($router) {
            $router->get('all', 'UserController@all');
            $router->get('role', 'UserController@get_role');
            $router->post('manage', 'UserController@manage');
            $router->post('delete', 'UserController@delete');
        });
        
        $router->group(['prefix' => 'dashboard'], function ($router) {
            $router->get('summary', 'Admin\DashboardController@summary');
            $router->get('diagram', 'Admin\DashboardController@diagram');
            $router->get('order', 'Admin\DashboardController@order');
            $router->get('visitor', 'Admin\DashboardController@visitor');
            $router->get('sales', 'Admin\DashboardController@sales_comparasion');
        });
    });
});


$router->get('/', function () use ($router) {
    return $router->app->version();
});

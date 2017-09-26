<?php

namespace App\Http\Controllers;

use App\Order as Order;
use Mollie_API_Client;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderController extends Controller {
    private $mollie;
    private $webhook;

    function __construct () {
        $this->mollie = $mollie = new Mollie_API_Client;
        $this->mollie->setApiKey("test_WnsRunp5ceShQe4maVaFNxjwQERMMB");
        $this->webhook = "http://test-ttmtax-api.kcps.nl/mollie-webhook";
    }

    function create(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        $amount = $request->input('amount');
        $service = $request->input('description');

        $payment = $this->mollie->payments->create(array(
            "amount"      => $amount, //10.00
            "description" => $service, //"Test Description"
            "redirectUrl" => $request->input('redirectURL'), //"http://test-ttmtax.kcps.nl/paymentSuccessful/"
            "webhookUrl"  => $this->webhook,
            "customerId" => $user->person_id
        ));

        $order = new Order();
        $order->user_id = $user->person_id;
        $order->service_name = $service;
        $order->price = $amount;
        $order->payment_status = $payment->status;
        $order->created = date('Y-m-d H:i:s');
        $order->save();

        return $payment->links->paymentUrl;
    }

    function webhook (Request $request) {
        $payment = $this->mollie->payments->get($request->input('id'));

        $order = Order::where("user_id", "=", $payment->customerId)
            ->update(['payment_status' => $payment->status]);
    }

}

?>
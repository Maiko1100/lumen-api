<?php

namespace App\Http\Controllers;

use App\Order as Order;
use Mollie_API_Client;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\UserYear as UserYear;

class OrderController extends Controller {
    private $mollie;
    private $webhook;

    function __construct () {
        $this->mollie = $mollie = new Mollie_API_Client();
        $this->mollie->setApiKey("test_WnsRunp5ceShQe4maVaFNxjwQERMMB");
        $this->webhook = "http://test-ttmtax-api.kcps.nl/api/mollie-webhook";
    }

    function createOrder(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        $service = $request->input('description');
        
        $amount = $this->getAmount($request->input('paymentString'));
        // echo $request;
        if (!isset($amount)) {            
        }

        $payment = $this->mollie->payments->create(array(
            "amount"      => $amount, //10.00
            "description" => $service, //"Test Description"
            "redirectUrl" => $request->input('redirectURL'), //"http://test-ttmtax.kcps.nl/paymentSuccessful/"
            "webhookUrl"  => $this->webhook
        ));

        $order = new Order();
        $order->user_id = $user->person_id;
        $order->service_name = $service;
        $order->price = $amount;
        $order->payment_id = $payment->id;
        $order->payment_status = $payment->status;
        $order->created = date('Y-m-d H:i:s');
        $order->save();

        return $payment->links->paymentUrl;
    }

    private function getAmount($amountString) {
        switch($amountString) {
            case 'appointment':
                return 50;
            case 'taxReturnWithAppointment':
                return 150;
            case 'taxReturnWithoutAppointment':
                return 100;
            default: 
                return null;
        }
    }

    function webhook (Request $request) {
        $paymentId = $request->input('id');
        $payment = $this->mollie->payments->get($paymentId);

        Order::where("payment_id", "=", $paymentId)
            ->update(
                [
                    'payment_status' => $payment->status,
                    'accepted' => date('Y-m-d H:i:s')
                ]
            );
    }

    public function getPayment(Request $request)
    {
        $id = 'tr_'.$request->input('paymentId');
        $order = Order::where('payment_id', '=',$id)->first();

        if($order->status == 'paid') {
            if ($order->user_year_id != null) {
                $userYear = UserYear::where('id', '=', $order->user_year_id)->first();
                $this->handlePayment($order->service_name,$userYear);
            }else{
                $this->handlePayment($order->service_name,null);
            }
        }else{
            return $order->status;
        }
    }

    public function handlePayment($service,$userYear){
        switch($service) {
            case 'taxReturnWithAppointment':
                $userYear->update(
                    [
                        'status' => 1
                    ]);
                $response = [
                    'status' => 1,
                    'service' => $service
                ];
                return $response;
            case 'taxReturnWithoutAppointment':
                $userYear->update(
                    [
                        'status' => 3
                    ]);
                $response = [
                    'status' => 3,
                    'service' => $service
                ];
                return $response;
            case 'taxAdvice':
                return $service;
            default:
                return false;
        }

    }
}

?>
<?php

namespace App\Http\Controllers;

use App\Appointment;
use App\Order as Order;
use Dingo\Api\Http\Response;
use Mollie_API_Client;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\UserYear as UserYear;
use Log;
use Illuminate\Http\JsonResponse;
use App\Utils\Enums\ProgressState;
use App\Discount as Discount;
use App\UserDiscount as UserDiscount;

class OrderController extends Controller {
    private $mollie;
    private $webhook;

    function __construct () {
        $this->mollie = $mollie = new Mollie_API_Client();
        $this->mollie->setApiKey("test_WnsRunp5ceShQe4maVaFNxjwQERMMB");
        $this->webhook = "http://test-ttmtax-api.kcps.nl/api/mollie-webhook";
    }

    function createOrder(Request $request) {
        $order = new Order();

        if($request->header('Authorization')!= "Bearer null") {

            $user = JWTAuth::parseToken()->authenticate();

            if($request->input('year') != null){
                $userYear = UserYear::where('person_id', '=', $user->person_id)->where('year_id','=',$request->input('year'))->first();
                if($userYear==null){
                    $userYear = new UserYear();
                    $userYear->person_id = $user->person_id;
                    $userYear->year_id = $request->input('year');
                    $userYear->status = ProgressState::questionnaireStartedNotPaid;
                    $userYear->save();
                }
                $order->user_id = $user->person_id;
                $order->user_year_id = $userYear->id;
            }else{
                $userYear = null;
            }



        }
        $service = $request->input('description');

        $amount = $this->getAmount($request->input('paymentString'));
        $package = $request->input('paymentString');
        $discountCode = $request->input("discountCode");



        if (isset($discountCode)) {
            $discount = Discount::where('code', '=', $discountCode)->first();
            if(isset($discount)) {
                $userDiscount = new UserDiscount();
                $userDiscount->user_id = $user->person_id;
                $userDiscount->discount_id = $discount->id;
                $userDiscount->save();
            }else{
                return "not a valid discountCode";
            }

            if($discount->percentage == 100){
                $order->service_name = $package;
                $order->price = $amount;
                $order->payment_id = "null";
                $order->payment_status = "opRek";
                $order->created = date('Y-m-d H:i:s');
                $order->save();

                return $request->input('redirectURL');
            }
            $amount = $amount - ($amount / 100 * $discount->percentage);
        }



        if (!isset($amount)) {            
        }

        $payment = $this->mollie->payments->create(array(
            "amount"      => $amount, //10.00
            "description" => $service, //"Test Description"
            "redirectUrl" => $request->input('redirectURL'), //"http://test-ttmtax.kcps.nl/paymentSuccessful/"
            "webhookUrl"  => $this->webhook
        ));


        $order->service_name = $package;
        $order->price = $amount;
        $order->payment_id = $payment->id;
        $order->payment_status = $payment->status;
        $order->created = date('Y-m-d H:i:s');
        $order->save();

        return $payment->links->paymentUrl;
    }

    private function getAmount($amountString) {
        switch($amountString) {
            case 'taxAdvice':
                return 61;
            case 'taxReturnWithAppointment':
                return 249;
            case 'taxReturnWithoutAppointment':
                return 199;
            case 'taxReturnProvisionalWithAppointment':
                return 171;
            case 'taxReturnProvisionalWithoutAppointment':
                return 121;
            case 'taxReturnPlusFiscal':
                return 324;
            case 'taxReturn':
                return 249;
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

        if($request->header('Authorization')!= "") {
            $user = JWTAuth::parseToken()->authenticate();
        }else{
            $user = null;
        }

        if($order->payment_status == 'paid') {
            if ($order->user_year_id != null) {
                $userYear = UserYear::where('id', '=', $order->user_year_id)->first();
                return $this->handlePayment($order->service_name,$userYear,$request,$user);
            }else{
                return $this->handlePayment($order->service_name,null,$request,$user);
            }
        }else{
            return $order->payment_status;
        }
    }

    public function handlePayment($service,$userYear,$request,$user){
        switch($service) {
            case 'taxReturnWithAppointment':
                $userYear->status = ProgressState::questionnaireStartedPaid;
                $userYear->save();
                return new JsonResponse($service);
            case 'taxReturnWithoutAppointment':
                $userYear->status = ProgressState::questionnaireReadyToReview;
                $userYear->save();
                return new JsonResponse($service);
            case 'taxReturnProvisionalWithAppointment':
                $userYear->status = ProgressState::questionnaireStartedPaid;
                $userYear->save();
                return new JsonResponse($service);
            case 'taxReturnProvisionalWithoutAppointment':
                $userYear->status = ProgressState::questionnaireReadyToReview;
                $userYear->save();
                return new JsonResponse($service);
            case 'taxReturnPlusFiscal':
                return new JsonResponse($service);
            case 'taxAdvice':
                return new JsonResponse($service);
            default:
                return "package not found";
        }

    }


}

?>
<?php

namespace App\Http\Controllers;

use Mollie_API_Client;
use Illuminate\Http\Request;

class Mollie extends Controller {
    private $mollie;
    private $webhook;

    function __construct () {
        $this->mollie = $mollie = new Mollie_API_Client;
        $this->mollie->setApiKey("test_WnsRunp5ceShQe4maVaFNxjwQERMMB");
        $this->webhook = "http://test-ttmtax-api.kcps.nl/mollie-webhook/";
    }

    function payment(Request $request) {
        $payment = $this->mollie->payments->create(array(
            "amount"      => $request->input('amount'), //10.00
            "description" => $request->input('description'), //"Test Description"
            "redirectUrl" => $request->input('redirectURL'), //"http://test-ttmtax.kcps.nl/paymentSuccessful/"
            "webhookUrl"  => $this->webhook
        ));

        return $payment->links->paymentUrl;
    }

}

?>
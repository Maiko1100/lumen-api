<?php

namespace App\Http\Controllers;

use App\Partner;
use App\Question;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;

class Controller extends BaseController
{
    public function test() {

        $data = [
            'hendrik' => 'henk',
            'email' => 'maiko@kcps.nl'

        ];

        Mail::send('mails.testmail', $data, function ($message) use ($data) {
            $message->to($data['email'], '')->subject('Do-not-reply:Afspraak');
            $message->from('maiko@kcps.nl', 'Maiko');
        });
        return 'send';
    }
}

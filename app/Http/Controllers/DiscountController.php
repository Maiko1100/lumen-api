<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Discount as Discount;

class DiscountController extends Controller
{

    public function getDiscount(Request $request)
    {
        $discountCode = $request->input("discountCode");

        $discount = Discount::where('code', '=', $discountCode)->first();
        if(!isset($discount)){
            return new JsonResponse([
                'message' => 'invalid discount code'
            ], Response::HTTP_BAD_REQUEST);
        }
        return JsonResponse::create($discount);

    }
}

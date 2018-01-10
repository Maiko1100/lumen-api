<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Discount as Discount;

class DiscountController extends Controller
{

    public function getDiscount(Request $request)
    {
        $discountCode = $request->input("discountCode");

        $discount = Discount::where('code', '=', $discountCode)->first();

        return JsonResponse::create($discount);

    }
}

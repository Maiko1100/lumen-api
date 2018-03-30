<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Discount as Discount;
use Tymon\JWTAuth\Facades\JWTAuth;

class DiscountController extends Controller
{

    public function getDiscount(Request $request)
    {
        $discountCode = $request->input("discountCode");

        $discount = Discount::where('code', '=', $discountCode)->where('active','=',true)->first();
        if(!isset($discount)){
            return new JsonResponse([
                'message' => 'invalid discount code'
            ], Response::HTTP_BAD_REQUEST);
        }
        return JsonResponse::create($discount);

    }

    public function getDiscounts()
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role == 3) {
            return new Response(Discount::where('active','=',true)->get());
        } else {
            return new Response("unauthorized");
        }
    }

    public function addDiscount(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role == 3) {
            $discount = new Discount();
            $discount->code = $request->input("code");
            $discount->active = true;
            $discount->percentage = $request->input("percentage");
            $discount->company = $request->has("company") ? $request->input("company") : null;

            $discount->save();

            return new Response($discount);
        } else {
            return new Response("unauthorized");
        }
    }

    public function deleteDiscount(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role == 3) {
            $discount = Discount::where('id','=',$request->input("id"))->first();
            $discount->active = false;
            $discount->save();

            return new Response("true");
        } else {
            return new Response("unauthorized");
        }
    }
}

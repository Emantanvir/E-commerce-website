<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderManager extends Controller
{
    function showCheckout()
    {
        return view('checkout');
    }
    function checkoutPost(Request $request){
        $request->validate([
            'address'=>'required',
            'pincode'=>'required',
            'phone'=>'required'
        ]);
         $cartItems = DB::table("cart")
    ->join('products', 'cart.product_id', '=', 'products.id')
    ->select(
        "cart.product_id",
        DB::raw("COUNT(*) as quantity"),
        'products.price',

    )
    ->where("cart.user_id", auth()->user()->id)
    ->groupBy(
        "cart.product_id",
        "products.price",
       
    )
    ->get();
   
    if ($carItems->isEmpty()){
         return redirect(route('cart.show'))->with('error', "Cart is empty");
    }

$productIds  = [];
$quantities =  [];
$totalPrice = 0;

foreach($cartItems as  $cartItem){
    $productIds[] =$cartItem->product_id;
    $quantites[]= $cartItems->quantity;
    $totalPrice += $cartItems->price * $cartItem->quantity;
}


        $order  =new Order();
        $order->user_id = auth()->user()->id ;
        $order->address = $request->address;
        $order->pincode = $request->pincode;
        $order->phone = $request->phone;
        $order->product_id = $json_encode($productIds);
        $order->total_price = $totalPrice;
        $order->quantity =json_encode($quantities);
        if($order->save()){
            DB::table("cart")->where("user_id",auth()->user()->id)->delete();
            return redirect(route('cart.show'))->with('success','Order placed successfully');
        }
        return redirect(route('cart.show'))->with('error', "Order placed Successfully");

    }
}

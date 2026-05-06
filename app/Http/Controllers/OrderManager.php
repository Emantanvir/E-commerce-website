<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;

class OrderManager extends Controller
{
    function showCheckout()
    {
        return view('checkout');
    }

    function checkoutPost(Request $request)
    {
        $request->validate([
            'address' => 'required',
            'pincode' => 'required',
            'phone' => 'required'
        ]);

        $cartItems = DB::table("cart")
            ->join('products', 'cart.product_id', '=', 'products.id')
            ->select(
                "cart.product_id",
                DB::raw("COUNT(*) as quantity"),
                'products.price',
                'products.name'   // ✅ FIXED (was title)
            )
            ->where("cart.user_id", auth()->user()->id)
            ->groupBy(
                "cart.product_id",
                "products.price",
                "products.name"   // ✅ FIXED
            )
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect(route('cart.show'))->with('error', "Cart is empty");
        }

        $productIds = [];
        $quantities = [];
        $totalPrice = 0;
        $lineItems = [];

        foreach ($cartItems as $cartItem) {
            $productIds[] = $cartItem->product_id;
            $quantities[] = $cartItem->quantity;
            $totalPrice += $cartItem->price * $cartItem->quantity;

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $cartItem->name, // ✅ FIXED
                    ],
                    'unit_amount' => $cartItem->price * 100,
                ],
                'quantity' => $cartItem->quantity,
            ];
        }

        $order = new Orders();
        $order->user_id = auth()->user()->id;
        $order->address = $request->address;
        $order->pincode = $request->pincode;
        $order->phone = $request->phone;
        $order->product_id = json_encode($productIds);
        $order->total_price = $totalPrice;
        $order->quantity = json_encode($quantities);

        if ($order->save()) {

            DB::table("cart")
                ->where("user_id", auth()->user()->id)
                ->delete();

          $stripe = new StripeClient(config("app.STRIPE_KEY"));

            $checkoutSession = $stripe->checkout->sessions->create([
                'success_url' => route('payment.success', ['order_id' => $order->id]),
                'cancel_url' => route('payment.error'),
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'customer_email' => auth()->user()->email,
                'metadata' => [
                    'order_id' => $order->id
                ]
            ]);

            return redirect($checkoutSession->url);
        }

        return redirect(route('cart.show'))->with('error', "Order failed");
    }

    function paymentError()
    {
        return "payment failed";
    }

    function paymentSuccess($order_id)
    {
        return "success " . $order_id;
    }
}
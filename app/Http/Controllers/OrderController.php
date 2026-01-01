<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Utils\Str;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = Order::where('user_id', $request->user()->id)->paginate(10);
        return OrderResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $cartIds = collect($request->input('cart_ids', []))->map(function ($_id) {
                return Crypt::decrypt($_id);
            })->toArray();
        } catch (DecryptException) {
            $cartIds = $request->input('cart_ids');
        }

        $validated = Validator::make([
            ...$request->all(),
            'cart_ids' => $cartIds
        ], [
            'cart_ids' => ['required', 'array'],
            'cart_ids.*' => ['required', 'exists:carts,id'],
        ])->validate();

        try {
            $carts = collect($validated['cart_ids'])->map(function ($cartId) {
                return Cart::findOrFail($cartId);
            });

            $total = $carts->reduce(function (?float $carry, Cart $item) {
                return ($carry ?? 0) + $item->subtotal;
            });

            $order = DB::transaction(function () use ($carts, $total, $request) {
                $order = Order::create([
                    'invoice' => Str::invoice(),
                    'payment_code' => Str::paymentCode(),
                    'payment_method' => 'Bank Transfer',
                    'payment_status' => Order::PAYMENT_PENDING,
                    'total' => $total,
                    'user_id' => $request->user()->id,
                ]);

                foreach ($carts as $cart) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cart->product->id,
                        'name' => $cart->product->name,
                        'thumbnail_url' => $cart->product->thumbnail_url,
                        'price' => $cart->product->price,
                        'qty' => $cart->qty,
                        'subtotal' => $cart->subtotal,
                    ]);

                    $cart->delete();
                }

                return $order;
            });

            return new OrderDetailResource($order);
        } catch (Throwable) {
            abort(500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $_id)
    {
        try {
            $id = Crypt::decrypt($_id);
            $order = Order::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
        } catch (DecryptException | ModelNotFoundException) {
            abort(404);
        }

        return new OrderDetailResource($order);
    }
}

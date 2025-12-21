<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Product;
use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Cart::paginate(10);
        return CartResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $productId = Crypt::decrypt($request->input('product_id'));
        } catch (DecryptException) {
            $productId = $request->input('product_id');
        }

        $cart = Cart::where('product_id', $productId)->first();
        $product = Product::find($productId);

        $validated = Validator::make(
            [
                ...$request->all(),
                'product_id' => $productId,
            ],
            [
                'product_id' => ['required', 'exists:products,id'],
                'qty' => [
                    'required',
                    'integer',
                    'min:1',
                    function (string $attribute, mixed $value, Closure $fail) use ($product, $cart) {
                        if (!$product) {
                            return;
                        }

                        $requestedQty = $cart
                            ? $cart->qty + $value
                            : $value;

                        if ($requestedQty > $product->stock) {
                            $fail('Insufficient stock.');
                        }
                    },
                ],
            ]
        )->validate();

        $finalQty = $cart
            ? $cart->qty + $validated['qty']
            : $validated['qty'];

        $payload = [
            'product_id' => $productId,
            'qty'        => $finalQty,
            'subtotal'   => $product->price * $finalQty,
        ];

        try {
            $cart = DB::transaction(function () use ($cart, $payload) {
                if ($cart) {
                    $cart->update($payload);
                } else {
                    $cart = Cart::create($payload);
                }

                return $cart;
            });

            return new CartResource($cart->fresh());
        } catch (Throwable) {
            abort(500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $_id)
    {
        try {
            $id = Crypt::decrypt($_id);
            $cart = Cart::with('product')->findOrFail($id);
        } catch (DecryptException | ModelNotFoundException) {
            abort(404);
        }

        $validated = $request->validate([
            'qty' => [
                'required',
                'integer',
                'min:1',
                function (string $attribute, mixed $value, Closure $fail) use ($cart) {
                    if ($value > $cart->product->stock) {
                        $fail('Insufficient stock.');
                    }
                },
            ],
        ]);

        try {
            $cart = DB::transaction(function () use ($cart, $validated) {
                $cart->update([
                    'qty'      => $validated['qty'],
                    'subtotal' => $validated['qty'] * $cart->product->price,
                ]);

                return $cart;
            });

            return new CartResource($cart->fresh());
        } catch (Throwable) {
            abort(500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $_id)
    {
        try {
            $id = Crypt::decrypt($_id);
            $cart = Cart::findOrFail($id);

            DB::transaction(function () use ($cart) {
                $cart->delete();
            });

            return response()->json(null, 204);
        } catch (DecryptException | ModelNotFoundException) {
            abort(404);
        } catch (Throwable) {
            abort(500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Resources\FavoriteResource;
use App\Models\Favorite;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = Favorite::where('user_id', $request->user()->id)->paginate(10);
        return FavoriteResource::collection($data);
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

        $validated = Validator::make([
            ...$request->all(),
            'product_id' => $productId
        ], [
            'product_id' => ['required', 'exists:products,id'],
        ])->validate();

        $favorite = Favorite::where('product_id', $validated['product_id'])->first();

        try {
            if (!$favorite) {
                $favorite = DB::transaction(function () use ($validated, $request) {
                    return Favorite::create([
                        ...$validated,
                        'user_id' => $request->user()->id,
                    ]);
                });
            }

            return new FavoriteResource($favorite);
        } catch (Throwable $e) {
            abort(500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $_id)
    {
        try {
            $id = Crypt::decrypt($_id);
            $favorite = Favorite::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();

            DB::transaction(function () use ($favorite) {
                $favorite->delete();
            });

            return response()->json(null, 204);
        } catch (DecryptException | ModelNotFoundException) {
            abort(404);
        } catch (Throwable) {
            abort(500);
        }
    }
}

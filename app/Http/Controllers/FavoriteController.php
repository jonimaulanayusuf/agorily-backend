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
    public function index()
    {
        $data = Favorite::paginate(10);
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
                $favorite = DB::transaction(function () use ($validated) {
                    return Favorite::create($validated);
                });
            }

            return new FavoriteResource($favorite->fresh());
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
            $favorite = Favorite::findOrFail($id);

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

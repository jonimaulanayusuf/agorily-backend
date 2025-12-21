<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Utils\Str;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $minPrice = $request->get('min_price', 0);
        $maxPrice = $request->get('max_price', 0);
        $sortBy = $request->get('sort_by', '');

        $builder = Product::where(function ($query) use ($search, $minPrice, $maxPrice) {
            if ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%');
            }
            if ($minPrice) {
                $query->where('price', '>=', $minPrice);
            }
            if ($maxPrice) {
                $query->where('price', '<=', $maxPrice);
            }
        });

        switch ($sortBy) {
            case 'highest_price':
                $builder->orderBy('price', 'desc');
                break;

            case 'lowest_price':
                $builder->orderBy('price', 'asc');
                break;

            case 'latest':
            default:
                $builder->orderBy('created_at', 'desc');
                break;
        }


        $data = $builder->paginate(10);
        return ProductResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'thumbnail_url' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'description' => ['required', 'string', 'max:1500'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $product = DB::transaction(function () use ($validated) {
                return Product::create([
                    ...$validated,
                    'slug' => Str::slug($validated['name']),
                ]);
            });

            return new ProductDetailResource($product);
        } catch (Throwable) {
            abort(500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $_id)
    {
        try {
            try {
                $idOrSlug = Crypt::decrypt($_id);
            } catch (DecryptException) {
                $idOrSlug = $_id;
            }

            $product = Product::where('id', $idOrSlug)
                ->orWhere('slug', $idOrSlug)
                ->firstOrFail();

            return new ProductDetailResource($product);
        } catch (ModelNotFoundException) {
            abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $_id)
    {
        try {
            $id = Crypt::decrypt($_id);
            $product = Product::findOrFail($id);
        } catch (DecryptException | ModelNotFoundException) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'thumbnail_url' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1500'],
            'stock' => ['nullable', 'integer', 'min:0'],
        ]);

        $slug = !empty($validated['name']) && ($validated['name'] != $product->name) ? Str::slug($validated['name']) : $product->slug;

        try {
            $product = DB::transaction(function () use ($product, $validated, $slug) {
                $product->update([
                    'name' => !empty($validated['name']) ? $validated['name'] : $product->name,
                    'thumbnail_url' => !empty($validated['thumbnail_url']) ? $validated['thumbnail_url'] : $product->thumbnail_url,
                    'price' => !empty($validated['price']) ? $validated['price'] : $product->price,
                    'description' => !empty($validated['description']) ? $validated['description'] : $product->description,
                    'stock' => !empty($validated['stock']) ? $validated['stock'] : $product->stock,
                    'slug' => $slug,
                ]);

                return $product;
            });

            return new ProductDetailResource($product);
        } catch (Throwable) {
            abort(404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $_id)
    {
        try {
            $id = Crypt::decrypt($_id);
            $product = Product::findOrFail($id);

            DB::transaction(function () use ($product) {
                $product->delete();
            });

            return response()->json(null, 204);
        } catch (DecryptException | ModelNotFoundException) {
            abort(404);
        } catch (Throwable) {
            abort(500);
        }
    }
}

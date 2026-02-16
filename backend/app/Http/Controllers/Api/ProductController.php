<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        //Filter by Category
        if($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        //Search by Name or SKU
        if($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        //Sort (Price or Stock)
        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');
        
        $query->orderBy($sortBy, $sortOrder);

        //Pagination (10 products per page)
        $products = $query->paginate(10);

        //Return all collection
        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        return new ProductResource($product);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return new ProductResource($product->load('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'sku'            => 'sometimes|string|unique:products,sku,' . $product->id,
            'price'          => 'sometimes|numeric|min:0',
            'stock'          => 'sometimes|integer|min:0',
            'category_id'    => 'sometimes|exists:categories,id',
        ]);

        $product->update($data);

        return new ProductResource($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Producto eliminado correctamente'
        ], 200);
    }
}

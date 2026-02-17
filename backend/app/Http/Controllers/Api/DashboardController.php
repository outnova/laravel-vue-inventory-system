<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index() {
        //Total inventory value
        $totalInventoryValue = Product::selectRaw('SUM(price * stock) as total')->value('total') ?? 0;

        //Count products with low stock (less than 5 units)
        $lowStockCount = Product::where('stock', '<', 5)->count();

        //Total unique products
        $totalProducts = Product::count();

        //More stock category
        $topCategory = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(products.stock) as total_stock'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_stock')
            ->first();

        return response()->json([
            'total_value' => round($totalInventoryValue, 2),
            'low_stock' => $lowStockCount,
            'total_products' => $totalProducts,
            'top_category' => $topCategory ? $topCategory->name : 'N/A'
        ]);
    }
}

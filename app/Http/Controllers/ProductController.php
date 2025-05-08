<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProductRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('welcome');
    }


    public function store(StoreProductRequest $request)
    {
        $product = Product::create([
            'product_name' => $request->product_name,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'total_value' => $request->quantity * $request->price
        ]);

        return response()->json([
            'success' => true,
            'product' => $product
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'product_name' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0.01'
        ]);

        $product->update([
            'product_name' => $request->product_name,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'total_value' => $request->quantity * $request->price
        ]);

        return response()->json(['success' => true]);
    }
    public function getProducts()
    {
        $products = Product::orderBy('created_at', 'desc')->get();
        $grandTotal = Product::sum('total_value');

        return response()->json([
            'products' => $products,
            'grand_total' => $grandTotal
        ]);
    }
}

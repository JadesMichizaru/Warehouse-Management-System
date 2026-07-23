<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductsController extends Controller
{
    public function index()
    {
        return response()->json(Products::all());
    }

    public function show(string $id) {
        $products = Products::findOrFail($id);

        return response()->json($products, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string',
            'category' => 'required|string',
            'min_stock' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product = Products::create([
           'sku' => $request->sku,
           'name' => $request->name,
           'category' => $request->category,
           'min_stock' => $request->min_stock,
           'stock' => $request->stock,
        ]);

        return response()->json([
            'messages' => 'Suppliers berhasil dibuat',
            'data' => $product
        ], 201);
    }
}

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

    public function show(string $sku)
    {
        $products = Products::where('sku', $sku)->first();

        if (!$products || $products->is_hidden || !$products->is_active) {
            return response()->json([
                'data' => 'Product not found or Product is out of stock',
            ]);
        }

        return response()->json(
            [
                'data' => [
                    'name' => $products->name,
                    'sku' => $products->sku,
                    'origin' => $products->origin,
                    'brand' => $products->brand,
                    'gross_weight' => $products->gross_weight,
                    'weight_unit' => $products->weight_unit,
                ],
            ],
            200,
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string',
            'origin' => 'required|string',
            'category' => 'required|string',
            'brand' => 'required|string',
            'gross_weight' => 'required|integer',
            'weight_unit' => 'required|string',
            'stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product = Products::create([
            'sku' => $request->sku,
            'name' => $request->name,
            'origin' => $request->origin,
            'category' => $request->category,
            'brand' => $request->brand,
            'gross_weight' => $request->gross_weight,
            'weight_unit' => $request->weight_unit,
            'stock' => $request->stock,
            'is_active' => false,
        ]);

        return response()->json(
            [
                'messages' => 'Suppliers berhasil dibuat',
                'data' => $product,
            ],
            201,
        );
    }

    public function show5()
    {
        $product = Products::limit(5)->get();

        return response($product);
    }

    public function update(Request $request, string $id)
    {
        $products = Products::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'sku' => 'required|string|unique:products,sku',
            'name' => 'sometimes|string',
            'origin' => 'sometimes|string',
            'category' => 'sometimes|string',
            'brand' => 'sometimes|string',
            'gross_weight' => 'sometimes|integer',
            'weight_unit' => 'sometimes|string',
            'stock' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $dateToUpdate = array_filter(
            $request->only([
                'sku',
                'name',
                'origin',
                'category',
                'brand',
                'gross_weight',
                'weight_unit',
                'stock',
            ]),
        );

        $products->update($dateToUpdate);

        return response()->json([
            'messages' => 'Customers successfully update',
            'data' => $products,
        ]);
    }

    public function destroy(string $id)
    {
        $product = Products::findOrFail($id);

        $product->delete();

        return response()->json([
            'messages' => 'Customer successfully destroy',
        ]);
    }
}

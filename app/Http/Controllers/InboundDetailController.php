<?php

namespace App\Http\Controllers;

use App\Models\InboundDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InboundDetailController extends Controller
{
    public function index() {
        $inboundDetails = InboundDetails::all();

        if($inboundDetails->isEmpty()) {
            return response()->json([
            'message' => 'Inbound Details not found'
            ], 404);
        }

        return response()->json([
            'data' => $inboundDetails
        ], 200);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'quantity' => 'required|integer|min:0'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $inboundDetail = InboundDetails::create([
            'product_id' => $request->product_id,
            'supplier_id' => $request->supplier_id,
            'quantity' => $request->quantity,
        ]);

        return response()->json([
            'messages' => 'Inbound Details successfully create',
            'data' => $inboundDetail
        ], 201);
    }

    public function delete(string $id) {
        $inboundDetails = InboundDetails::findOrFail($id);

        $inboundDetails->delete();

        return response()->json([
            'messages' => 'Inbound Details successfully delete'
        ],200);
    }
}

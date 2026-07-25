<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomersController extends Controller
{
    public function index() {
        return response()->json(Customers::all());
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:customers,code',
            'name' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $customer = Customers::create([
            'code' => $request->code,
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return response()->json([
            'messages' => 'Customer successfully create',
            'data' => $customer
        ]);
    }

    public function show5(){
        $customer = Customers::limit(5)->get();

        return response($customer);
    }

    public function update(Request $request, string $id) {
        $customers = Customers::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code' => 'sometimes|string|unique:customers,code',
            'name' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'address' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $dateToUpdate = array_filter($request->only([
            'code',
            'name',
            'phone',
            'address'
        ])
        );


        $customers->update($dateToUpdate);

        return response()->json([
            'messages' => 'Customers successfully update',
            'data' => $customers,
        ]);
    }


    public function destroy(string $id) {
        $customer = Customers::findOrFail($id);

        $customer->delete();

        return response()->json([
            'messages' => 'Customer successfully destroy'
        ]);
    }

}

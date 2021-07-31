<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * A class to create, read, update and delete products. If product does not exist, return error message following same
 * pattern of Laravel Illuminate\Support\Facades\Validator class where error is an object.
 */
class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Product::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /*
         * Product name must be unique, but not according to DB schema. Schema allows duplicate names, since there might
         * exist soft-deleted products. Also add custom error message for name field.
         */
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:products',
            'description' => 'string|nullable'
        ], [
            'name.required' => 'Missing product name.',
            'name.unique' => 'Product already exists.',
        ]);

        // If product cannot be added due to missing parameters or product exists, return user friendly error message.
        if ($validator->fails()) {
            $errors = $validator->errors();

            if ($errors->has('name')) {
                return response()->json([
                    'message' => 'Cannot create product.',
                    'errors' => $errors
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        return Product::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::find($id);

        // If product is not found, return user friendly error message.
        if ($product === null) {
            return response()->json([
                'message' => 'Cannot find product.',
                'errors' => (object) [
                    'id' => [
                        'Product does not exist.'
                    ]
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        return $product;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        // If product is not found, return user friendly error message.
        if ($product === null) {
            return response()->json([
                'message' => 'Cannot update product.',
                'errors' => (object) [
                    'id' => [
                        'Product does not exist.'
                    ]
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        // Otherwise continue validating the fields.
        $request->validate([
            'name' => 'string',
            'description' => 'string|nullable'
        ]);

        /*
         * If product name is given and product name is updated to already another existing name, return user friendly
         * error message.
         */
        $name = $request->input('name');

        // First check if product name is given.
        if ($name !== null) {
            // Then check if the name given is for the same product. If it is, allow to update it.
            if (trim(Str::lower($product->name)) !== trim(Str::lower($name))) {
                // If name is for different product, check if name already exists. Products are case insensitive.
                $product_exists = Product::where('name', $name)->first();

                if ($product_exists) {
                    return response()->json([
                        'message' => 'Cannot update product.',
                        'errors' => (object) [
                            'name' => [
                                'Product already exists.'
                            ]
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        // Update product if everything was success.
        $product->update($request->all());

        return $product;
    }

    /**
     * Soft remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if ($product === null) {
            return response()->json([
                'message' => 'Cannot delete product.',
                'errors' => (object) [
                    'id' => [
                        'Product does not exist.'
                    ]
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        if (Product::destroy($id)) {
            return response()->json([
                'message' => 'Product deleted.',
            ], Response::HTTP_OK);
        }
    }
}

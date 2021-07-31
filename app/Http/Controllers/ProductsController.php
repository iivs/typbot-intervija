<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductAttribute;
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
     * Display a list of products without attributes.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Product::all();
    }

    /**
     * Store a newly created product and attributes if specified.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /*
         * Product name must be unique, but not according to DB schema. Schema allows duplicate names, since there might
         * exist soft-deleted products. Also add custom error message for name field. If "attribues" parameter is given,
         * make sure it has "key" property. Attribute values are optional. Attribute keys must be distinct.
         */
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:products',
            'description' => 'string|nullable',
            'attributes' => 'array|nullable',
            'attributes.*.key' => 'required_with:attributes|distinct',
            'attributes.*.value' => 'string|nullable'
        ], [
            'name.required' => 'Missing product name.',
            'name.unique' => 'Product already exists.'
        ]);

        // If product cannot be added due to missing parameters or product exists, return user friendly error message.
        if ($validator->fails()) {
            $errors = $validator->errors();

            return response()->json([
                'message' => 'Cannot create product.',
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        // Save product properties.
        $product = Product::create($request->all());

        // Process product attributes.
        $attributes = $request->input('attributes');

        if ($attributes !== null) {
            // Get product "created_at" and apply to each attribute.
            foreach ($attributes as &$attribute) {
                $attribute['created_at'] = $product->created_at;
            }
            unset($attribute);

            // Save product attributes.
            $attributes = $product->attributes()->createMany($attributes);

            // Include attribues in the result response.
            $product->attributes = $attributes;
        }

        return $product;
    }

    /**
     * Display the specified product. Respose does not include the product attributes.
     *
     * @param  int  $id  Product ID.
     *
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
     * Update the specified product and attributes. Attributes can be removed product, not given therefore not updated
     * or rewritten. Meaning that previous attributes will be soft-deleted. If given, attribute keys must be distinct.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id Product ID.
     *
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
            'description' => 'string|nullable',
            'attributes' => 'array|nullable',
            'attributes.*.key' => 'required_with:attributes|distinct',
            'attributes.*.value' => 'string|nullable'
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

        // Process product attributes.
        $attributes = $request->input('attributes');

        if ($attributes !== null) {
            // Delete the old attributes.
            $product->attributes()->delete();

            // Get product "updated_at" and apply to each attribute.
            foreach ($attributes as &$attribute) {
                $attribute['created_at'] = $product->updated_at;
            }
            unset($attribute);
    
            // Save the new product attributes.
            $attributes = $product->attributes()->createMany($attributes);

            // Include attribues in the result response.
            $product->attributes = $attributes;
        }
        // If attributes are null, do not delete them.

        return $product;
    }

    /**
     * Soft remove the specified resource from storage.
     *
     * @param  int  $id  Product ID.
     *
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
            // Also soft-delete the product attributes.
            $product->attributes()->delete();

            return response()->json([
                'message' => 'Product deleted.',
            ], Response::HTTP_OK);
        }
    }

    /**
     * Get one product attributes.
     *
     * @param int $id  Product ID.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function attributes($id) {
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

        return $product->attributes;
    }
}

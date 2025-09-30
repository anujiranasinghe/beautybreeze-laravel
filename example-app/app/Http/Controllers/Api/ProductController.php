<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\UTCDateTime;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = Product::query();
        if ($s = $request->string('search')->toString()) {
            $q->where('Title', 'like', "%$s%");
        }
        if ($cat = $request->input('category')) {
            $q->where('CategoryID', $cat);
        }
        if ($min = $request->input('min_price')) {
            $q->where('Price', '>=', $min);
        }
        if ($max = $request->input('max_price')) {
            $q->where('Price', '<=', $max);
        }
        $products = $q->paginate(12);
        return ProductResource::collection($products);
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        $this->logMongo('view', $product->ProductID, [
            'title' => $product->Title,
        ]);
        return new ProductResource($product);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        // Handle image upload if present as file input 'image'
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['Image'] = 'storage/'.$path; // public URL path via storage:link
        }
        $product = Product::create($data);
        $this->logMongo('create', $product->ProductID, $data);
        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['Image'] = 'storage/'.$path;
        }
        $product->update($data);
        $this->logMongo('update', $product->ProductID, $data);
        return new ProductResource($product);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        // Prevent deleting products that appear on existing order items
        if (OrderItem::where('ProductId', $product->ProductID)->exists()) {
            $this->logMongo('delete_blocked', $id, ['reason' => 'product_has_order_items']);
            return response()->json([
                'message' => 'Cannot delete: product has existing order items.'
            ], 422);
        }
        $product->delete();
        $this->logMongo('delete', $id, []);
        return response()->json(['message' => 'Deleted']);
    }
protected function logMongo(string $action, $productId, array $meta): void
{
    $doc = [
        'action'     => $action,
        'product_id' => $productId,
        'meta'       => $meta,
        'user_id'    => Auth::id(),
        'created_at' => new UTCDateTime(now()->getTimestamp() * 1000),
    ];
    try {
        DB::connection('mongodb')->selectCollection('product_logs_admin')->insertOne($doc);
    } catch (\Throwable $e) {
        Log::warning('Mongo log insert failed', [
            'action'     => $action,
            'product_id' => $productId,
            'error'      => $e->getMessage(),
        ]);
    } finally {
        // Always mirror to a dedicated file for audits/demo
        try {
            Log::channel('product_logs_admin')->info('admin_product_log', [
                'action' => $action,
                'product_id' => $productId,
                'user_id' => Auth::id(),
                'meta' => $meta,
                'at' => now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {}
    }
}

}

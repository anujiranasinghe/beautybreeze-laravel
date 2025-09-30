<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use MongoDB\BSON\UTCDateTime;

class OrderAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = Order::query();
        if ($s = trim((string)$request->query('search', ''))) {
            $like = "%$s%";
            $q->where(function($w) use ($like) {
                $w->where('CustomerName', 'like', $like)
                  ->orWhere('Email', 'like', $like)
                  ->orWhere('PhoneNo', 'like', $like)
                  ->orWhere('OrderId', 'like', $like);
            });
        }
        if ($status = $request->query('status')) {
            $q->where('Status', $status);
        }
        $orders = $q->with('items')->orderByDesc('OrderId')->paginate(15);
        return OrderResource::collection($orders);
    }

    public function show($id)
    {
        $order = Order::with('items')->findOrFail($id);
        return new OrderResource($order);
    }

    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['Pending','Processing','Shipped','Delivered','Cancelled'])],
        ]);
        $order = Order::findOrFail($id);
        $old = $order->Status ?: 'Pending';
        $order->Status = $data['status'];
        $order->save();

        $this->logOrderAudit('status_update', $order->OrderId, [
            'old_status' => $old,
            'new_status' => $data['status'],
            'trigger' => 'admin_api',
        ]);

        return new OrderResource($order->load('items'));
    }

    protected function logOrderAudit(string $action, $orderId, array $meta): void
    {
        try {
            DB::connection('mongodb')
                ->selectCollection('order_logs_admin')
                ->insertOne([
                    'action' => $action,
                    'order_id' => $orderId,
                    'meta' => $meta,
                    'user_id' => Auth::id(),
                    'created_at' => new UTCDateTime(now()->getTimestamp() * 1000),
                ]);
        } catch (\Throwable $e) {
            Log::warning('Mongo order log insert failed', [
                'order_id' => $orderId,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }
}


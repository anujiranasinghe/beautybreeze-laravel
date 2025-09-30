<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout as LivewireLayout;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MongoDB\BSON\UTCDateTime;

#[LivewireLayout('layouts.admin')]
class OrderManagement extends Component
{
    public string $search = '';
    public string $status = '';
    public int $currentPage = 1;
    public int $perPage = 12;

    public array $orders = [];
    public array $pagination = [];

    public ?int $selectedOrderId = null;
    public ?array $selectedOrder = null;
    public array $selectedItems = [];

    public ?string $bannerMessage = null;
    public string $bannerType = 'info';

    public function mount(): void
    {
        $this->loadOrders();
    }

    public function updated($field): void
    {
        if (in_array($field, ['search','status'])) {
            $this->currentPage = 1;
            $this->loadOrders();
        }
    }

    public function gotoPage($page): void
    {
        $this->currentPage = max(1, (int)$page);
        $this->loadOrders();
    }

    public function loadOrders(): void
    {
        $q = Order::query();
        if (trim($this->search) !== '') {
            $s = '%'.$this->search.'%';
            $q->where(function($w) use ($s) {
                $w->where('CustomerName', 'like', $s)
                  ->orWhere('Email', 'like', $s)
                  ->orWhere('PhoneNo', 'like', $s)
                  ->orWhere('OrderId', 'like', $s);
            });
        }
        if ($this->status !== '') {
            $q->where('Status', $this->status);
        }
        $paginator = $q->orderBy('OrderId','desc')->paginate($this->perPage, page: $this->currentPage);

        $this->orders = $paginator->map(function($o){
            $itemsCount = OrderItem::where('OrderId', $o->OrderId)->sum('Quantity');
            $totalAmount = OrderItem::where('OrderId', $o->OrderId)->sum('TotalPrice');
            return [
                'id' => $o->OrderId,
                'customer' => $o->CustomerName,
                'email' => $o->Email,
                'phone' => $o->PhoneNo,
                'status' => $o->Status,
                'payment_method' => $o->PaymentMethod,
                'payment_status' => $o->PaymentStatus,
                'created_at' => optional($o->created_at)->toDateTimeString(),
                'items_count' => (int)$itemsCount,
                'total_amount' => (float)$totalAmount,
            ];
        })->all();

        $this->pagination = [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    public function selectOrder($id): void
    {
        $order = Order::with(['items.product'])->find($id);
        if (!$order) {
            $this->bannerType = 'error';
            $this->bannerMessage = 'Order not found';
            return;
        }
        // Auto-advance to Processing when admin opens the order, if it's still Pending/empty
        $current = strtolower((string)($order->Status ?? ''));
        if ($current === '' || $current === 'pending') {
            $old = $order->Status ?: 'Pending';
            $order->Status = 'Processing';
            $order->save();
            $this->logOrderAudit('status_update', $order->OrderId, [
                'old_status' => $old,
                'new_status' => 'Processing',
                'trigger' => 'admin_view',
            ]);
        }
        // Refresh to reflect any change
        $order->refresh()->load('items.product');
        $this->selectedOrderId = $order->OrderId;
        $this->selectedOrder = [
            'id' => $order->OrderId,
            'customer' => $order->CustomerName,
            'email' => $order->Email,
            'phone' => $order->PhoneNo,
            'address' => $order->Address,
            'payment_method' => $order->PaymentMethod,
            'payment_status' => $order->PaymentStatus,
            'status' => $order->Status,
            'created_at' => optional($order->created_at)->toDateTimeString(),
        ];
        $this->selectedItems = $order->items->map(function($it){
            return [
                'id' => $it->OrderItemId,
                'product_id' => $it->ProductId,
                'product_name' => $it->ProductName,
                'unit_price' => (float)$it->UnitPrice,
                'quantity' => (int)$it->Quantity,
                'total' => (float)$it->TotalPrice,
                'image_url' => optional($it->product)->Image ? asset(optional($it->product)->Image) : null,
            ];
        })->all();
    }

    public function closeDetails(): void
    {
        $this->selectedOrderId = null;
        $this->selectedOrder = null;
        $this->selectedItems = [];
    }

    public function updateStatus(string $status): void
    {
        $allowed = ['Pending','Processing','Shipped','Delivered','Cancelled'];
        if (!in_array($status, $allowed, true)) {
            $this->bannerType = 'error';
            $this->bannerMessage = 'Invalid status';
            return;
        }
        if (!$this->selectedOrderId) {
            $this->bannerType = 'error';
            $this->bannerMessage = 'No order selected';
            return;
        }
        $order = Order::find($this->selectedOrderId);
        if (!$order) {
            $this->bannerType = 'error';
            $this->bannerMessage = 'Order not found';
            return;
        }
        $old = $order->Status ?: 'Pending';
        $order->Status = $status;
        $order->save();
        $this->logOrderAudit('status_update', $order->OrderId, [
            'old_status' => $old,
            'new_status' => $status,
            'trigger' => 'admin_action',
        ]);

        $this->bannerType = 'success';
        $this->bannerMessage = 'Order status updated';
        // Refresh list and details
        $this->loadOrders();
        $this->selectOrder($this->selectedOrderId);
    }

    public function render()
    {
        return view('livewire.admin.order-management', [
            'orders' => $this->orders,
            'pagination' => $this->pagination,
            'selectedOrder' => $this->selectedOrder,
            'selectedItems' => $this->selectedItems,
        ]);
    }

    protected function logOrderAudit(string $action, $orderId, array $meta = []): void
    {
        try {
            DB::connection('mongodb')
                ->selectCollection('order_logs_admin')
                ->insertOne([
                    'action' => $action,
                    'order_id' => $orderId,
                    'meta' => $meta,
                    'user_id' => auth()->id(),
                    'created_at' => new UTCDateTime(now()->getTimestamp() * 1000),
                ]);
        } catch (\Throwable $e) {
            Log::warning('Order audit log (Mongo) failed', [
                'order_id' => $orderId,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

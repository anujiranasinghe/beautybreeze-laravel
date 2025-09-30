<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout as LivewireLayout;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\UTCDateTime;

#[LivewireLayout('layouts.admin')]
class Dashboard extends Component
{
    public array $kpis = [];
    public array $mostOrdered = [];
    public array $topViews = [];

    public function mount(): void
    {
        $todayStart = now()->startOfDay();
        $weekStart = now()->startOfWeek();

        $todayOrders = Order::where('created_at', '>=', $todayStart)->count();
        $weekOrders = Order::where('created_at', '>=', $weekStart)->count();

        $todayRevenue = OrderItem::whereHas('order', fn($q) => $q->where('created_at', '>=', $todayStart))
            ->sum('TotalPrice');

        $weekRevenue = OrderItem::whereHas('order', fn($q) => $q->where('created_at', '>=', $weekStart))
            ->sum('TotalPrice');

        $ordersCount30 = Order::where('created_at', '>=', now()->subDays(30))->count();
        $itemsCount30 = OrderItem::whereHas('order', fn($q) => $q->where('created_at', '>=', now()->subDays(30)))
            ->sum('Quantity');
        $avgBasket = $ordersCount30 > 0 ? round($itemsCount30 / $ordersCount30, 2) : 0;

        $this->kpis = [
            'todayOrders' => $todayOrders,
            'weekOrders' => $weekOrders,
            'todayRevenue' => $todayRevenue,
            'weekRevenue' => $weekRevenue,
            'avgBasket' => $avgBasket,
        ];

        // SQL: Most ordered products (30 days)
        $top = OrderItem::select('ProductId')
            ->selectRaw('SUM(Quantity) as qty')
            ->whereHas('order', fn($q) => $q->where('created_at', '>=', now()->subDays(30)))
            ->groupBy('ProductId')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();
        $productsMap = Product::whereIn('ProductID', $top->pluck('ProductId'))->get()->keyBy('ProductID');
        $this->mostOrdered = [
            'labels' => $top->map(fn($r) => optional($productsMap->get($r->ProductId))->Title ?? 'Unknown')->values()->all(),
            'data' => $top->pluck('qty')->values()->all(),
        ];

        // Mongo: Top viewed products (30 days)
        try {
            $since = new UTCDateTime(now()->subDays(30));
            $cursor = DB::connection('mongodb')
            ->selectCollection('product_views')
            ->aggregate([
                ['$match' => [
                    'action' => 'view',
                    'created_at' => ['$gte' => $since],
                ]],
                ['$group' => [
                    '_id' => '$product_id',
                    'views' => ['$sum' => 1],
                ]],
                ['$sort' => ['views' => -1]],
                ['$limit' => 5],
            ]);

            $docs = collect(iterator_to_array($cursor));
            $ids = $docs->pluck('_id')->filter()->values();
            $map = Product::whereIn('ProductID', $ids)->get()->keyBy('ProductID');
            $this->topViews = [
                'labels' => $ids->map(fn($id) => optional($map->get($id))->Title ?? 'Unknown')->all(),
                'data' => $docs->pluck('views')->all(),
            ];
        } catch (\Throwable $e) {
            $this->topViews = ['labels' => [], 'data' => []];
        }
    }

    public function render()
    {
        return view('livewire.admin.dashboard', [
            'kpis' => $this->kpis,
            'mostOrdered' => $this->mostOrdered,
            'topViews' => $this->topViews,
        ]);
    }
}

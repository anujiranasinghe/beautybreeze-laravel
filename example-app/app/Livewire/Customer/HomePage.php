<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Layout as LivewireLayout;
use Livewire\WithPagination;
use App\Models\Category;
use App\Models\Product;
use App\Models\OrderItem;

#[LivewireLayout('layouts.customer')]
class HomePage extends Component
{
    use WithPagination;

    public $category = '';
    public $search = '';

    protected $queryString = [
        'category' => ['except' => ''],
        'search' => ['except' => '']
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function render()
    {
        $categories = Category::all();
        
        $products = Product::query()
            ->when($this->category, function($query) {
                return $query->where('CategoryID', $this->category);
            })
            ->when($this->search, function($query) {
                return $query->where('Title', 'like', '%' . $this->search . '%')
                    ->orWhere('Description', 'like', '%' . $this->search . '%');
            })
            ->paginate(12);

        // Top 4 most-sold products from order items
        $top = OrderItem::query()
            ->select('ProductId')
            ->selectRaw('SUM(Quantity) as total_qty')
            ->whereNotNull('ProductId')
            ->groupBy('ProductId')
            ->orderByDesc('total_qty')
            ->limit(4)
            ->get();

        $trendingProducts = collect();
        if ($top->isNotEmpty()) {
            $byId = Product::whereIn('ProductID', $top->pluck('ProductId'))
                ->get()
                ->keyBy('ProductID');
            $trendingProducts = $top->map(function($row) use ($byId) {
                return $byId->get($row->ProductId);
            })->filter();
        }

        return view('livewire.customer.home-page', [
            'categories' => $categories,
            'products' => $products,
            'trendingProducts' => $trendingProducts,
        ]);
    }
}

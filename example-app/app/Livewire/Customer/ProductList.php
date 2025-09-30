<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Layout as LivewireLayout;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Category;

#[LivewireLayout('layouts.customer')]
class ProductList extends Component
{
    use WithPagination;

    public $search = '';
    public $category = '';
    public $minPrice = '';
    public $maxPrice = '';
    public $showFilters = false;
    public $suggestions = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => ''],
        'minPrice' => ['except' => ''],
        'maxPrice' => ['except' => ''],
    ];

    public function updating($name)
    {
        if (in_array($name, ['search','category','minPrice','maxPrice'])) {
            $this->resetPage();
        }
    }

    public function updatedSearch()
    {
        $term = trim($this->search);
        if ($term === '') { $this->suggestions = []; return; }
        $this->suggestions = Product::query()
            ->where('Title','like','%'.$term.'%')
            ->orderBy('Title')
            ->limit(8)
            ->get(['ProductID','Title'])
            ->toArray();
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->suggestions = [];
    }

    public function openFilters() { $this->showFilters = true; }
    public function closeFilters() { $this->showFilters = false; }
    public function applyFilters() { $this->showFilters = false; }
    public function clearFilters()
    {
        $this->category = '';
        $this->minPrice = '';
        $this->maxPrice = '';
        $this->resetPage();
    }

    public function render()
    {
        $categories = Category::orderBy('CategoryName')->get();

        $products = Product::query()
            ->when($this->search, fn($q) => $q->where(function($qq){
                $qq->where('Title','like','%'.$this->search.'%')
                   ->orWhere('Description','like','%'.$this->search.'%');
            }))
            ->when($this->category, fn($q) => $q->where('CategoryID', $this->category))
            ->when($this->minPrice !== '' && is_numeric($this->minPrice), fn($q) => $q->where('Price','>=',$this->minPrice))
            ->when($this->maxPrice !== '' && is_numeric($this->maxPrice), fn($q) => $q->where('Price','<=',$this->maxPrice))
            ->paginate(12);

        return view('livewire.customer.product-list', [
            'categories' => $categories,
            'products' => $products,
            'suggestions' => $this->suggestions,
        ]);
    }
}

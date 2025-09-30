<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout as LivewireLayout;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Validation\Rule;

#[LivewireLayout('layouts.admin')]
class ProductManagement extends Component
{
    use WithFileUploads;
    

    // Listing state
    public string $search = '';
    public $category = '';
    public $minPrice = '';
    public $maxPrice = '';
    public int $currentPage = 1;
    public int $perPage = 12;
    public array $products = [];
    public array $pagination = [];
    public string $mode = 'menu'; // menu|list|create|edit
    public bool $useApi = false; // default to direct DB to avoid dev-server deadlocks
    public array $suggestions = [];
    public ?string $bannerMessage = null;
    public string $bannerType = 'info'; // info|success|error
    public bool $showBlockedDelete = false;
    public string $blockedDeleteMessage = '';

    // Modals
    public bool $showCreate = false;
    public bool $showEdit = false;
    public bool $showDelete = false;

    // Form fields
    public $productId = null;
    public string $Title = '';
    public string $Description = '';
    public $CategoryID = '';
    public $Price = '';
    public $StockQuantity = '';
    public $Image = '';
    public $imageUpload = null; // TemporaryUploadedFile

    protected function baseRules(): array
    {
        return [
            'Title' => ['required','string','max:255'],
            'Description' => ['nullable','string'],
            'CategoryID' => ['nullable','integer'],
            'Price' => ['required','numeric','min:0'],
            'StockQuantity' => ['nullable','integer','min:0'],
            'Image' => ['nullable','string','max:1024'],
        ];
    }

    protected function rulesForCreate(): array
    {
        $rules = $this->baseRules();
        $rules['Title'][] = Rule::unique('products','Title');
        return $rules;
    }

    protected function rulesForUpdate(): array
    {
        $rules = $this->baseRules();
        $rules['Title'][] = Rule::unique('products','Title')->ignore($this->productId,'ProductID');
        return $rules;
    }

    protected function apiBase(): string
    {
        // Use app.url if set; fall back to current host
        return rtrim(config('app.url') ?: url('/'), '/');
    }

    protected function apiToken(): ?string
    {
        // Cache a token per admin session to call API from server side
        $token = session('admin_ui_token');
        if (!$token && Auth::check()) {
            $token = Auth::user()->createToken('admin-ui')->plainTextToken;
            session(['admin_ui_token' => $token]);
        }
        return $token;
    }

    public function mount(): void
    {
        // Start at menu. Force DB mode; disable API mode toggle in local dev
        $this->useApi = false;
    }

    public function updated($field): void
    {
        if (in_array($field, ['search','category','minPrice','maxPrice'])) {
            $this->currentPage = 1;
            $this->loadProducts();
        }
    }

    // Prevent enabling API mode from UI
    public function updatedUseApi(): void
    {
        $this->useApi = false;
        $this->bannerType = 'info';
        $this->bannerMessage = 'API mode disabled in this environment. Using database directly.';
    }

    public function updatedSearch(): void
    {
        // Lightweight suggestions from DB
        if (trim($this->search) === '') {
            $this->suggestions = [];
            return;
        }
        $this->suggestions = Product::query()
            ->where('Title', 'like', '%'.$this->search.'%')
            ->orderBy('Title')
            ->limit(5)
            ->pluck('Title')
            ->all();
    }

    public function selectSuggestion(string $title): void
    {
        $this->search = $title;
        $this->suggestions = [];
        $this->currentPage = 1;
        $this->loadProducts();
    }

    // removed duplicate updatedUseApi()

    public function openList(): void
    {
        $this->mode = 'list';
        $this->currentPage = 1;
        $this->suggestions = [];
        $this->loadProducts();
    }

    public function goToPage($page): void
    {
        $this->currentPage = max(1, (int)$page);
        $this->loadProducts();
    }

    public function loadProducts(): void
    {
        if ($this->useApi) {
            $query = [
                'page' => $this->currentPage,
                'search' => $this->search ?: null,
                'category' => $this->category ?: null,
                'min_price' => $this->minPrice ?: null,
                'max_price' => $this->maxPrice ?: null,
            ];
            try {
                $resp = Http::withToken($this->apiToken())
                    ->get($this->apiBase().'/api/products', array_filter($query, fn($v) => $v !== null && $v !== ''));
                if ($resp->successful()) {
                    $json = $resp->json();
                    $this->products = $json['data'] ?? [];
                    $this->pagination = $json['meta'] ?? [];
                    return;
                }
            } catch (\Throwable $e) {
                // Fall through to DB
            }
        }

        // Direct DB fallback (safe in local dev and avoids deadlock)
        $q = Product::query();
        if ($this->search) {
            $q->where('Title', 'like', '%'.$this->search.'%');
        }
        if ($this->category) {
            $q->where('CategoryID', $this->category);
        }
        if ($this->minPrice !== '') {
            $q->where('Price', '>=', $this->minPrice);
        }
        if ($this->maxPrice !== '') {
            $q->where('Price', '<=', $this->maxPrice);
        }
        $paginator = $q->orderBy('ProductID','desc')->paginate($this->perPage, page: $this->currentPage);
        $this->products = $paginator->map(function($p){
            $img = $p->Image;
            $imageUrl = null;
            if ($img) {
                if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
                    $imageUrl = $img;
                } else {
                    $imageUrl = asset($img);
                }
            }
            return [
                'id' => $p->ProductID,
                'title' => $p->Title,
                'description' => $p->Description,
                'category_id' => $p->CategoryID,
                'price' => (float) $p->Price,
                'image' => $p->Image,
                'image_url' => $imageUrl,
                'stock' => $p->StockQuantity,
            ];
        })->all();
        $this->pagination = [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showCreate = true;
        $this->mode = 'create';
    }

    public function openEdit($id): void
    {
        $this->resetForm();
        $this->productId = $id;
        if ($this->useApi) {
            try {
                $resp = Http::withToken($this->apiToken())->get($this->apiBase()."/api/products/{$id}");
                if ($resp->successful()) {
                    $p = $resp->json();
                    $this->Title = $p['title'] ?? '';
                    $this->Description = $p['description'] ?? '';
                    $this->CategoryID = (string)($p['category_id'] ?? '');
                    $this->Price = (string)($p['price'] ?? '');
                    $this->StockQuantity = (string)($p['stock'] ?? '');
                    $this->Image = $p['image'] ?? '';
                }
            } catch (\Throwable $e) {}
        } else {
            $p = Product::find($id);
            if ($p) {
                $this->Title = $p->Title ?? '';
                $this->Description = $p->Description ?? '';
                $this->CategoryID = (string)($p->CategoryID ?? '');
                $this->Price = (string)($p->Price ?? '');
                $this->StockQuantity = (string)($p->StockQuantity ?? '');
                $this->Image = $p->Image ?? '';
            }
        }
        $this->showEdit = true;
        $this->mode = 'edit';
    }

    public function createProduct(): void
    {
        try {
            if ($this->useApi) {
                $req = Http::withToken($this->apiToken());
                if ($this->imageUpload) {
                    $req = $req->asMultipart()
                        ->attach('image', fopen($this->imageUpload->getRealPath(), 'r'), $this->imageUpload->getClientOriginalName());
                }
                $resp = $req->post($this->apiBase().'/api/products', [
                    'Title' => $this->Title,
                    'Description' => $this->Description,
                    'CategoryID' => $this->CategoryID,
                    'Price' => $this->Price,
                    'StockQuantity' => $this->StockQuantity,
                    'Image' => $this->Image,
                ]);
                if (!$resp->successful()) {
                    throw new \RuntimeException('API create failed: '.$resp->status());
                }
            } else {
                $this->validate($this->rulesForCreate());
                $data = [
                    'Title' => $this->Title,
                    'Description' => $this->Description,
                    'CategoryID' => $this->CategoryID ?: null,
                    'Price' => $this->Price ?: 0,
                    'StockQuantity' => $this->StockQuantity ?: 0,
                    'Image' => $this->Image ?: null,
                ];
                if ($this->imageUpload) {
                    $path = $this->imageUpload->store('products', 'public');
                    $data['Image'] = 'storage/'.$path;
                }
                $p = Product::create($data);
                // Audit: admin create via dashboard (non-API path)
                $this->logAdminAudit('create', $p->ProductID, $data);
            }
            $this->showCreate = false;
            $this->mode = 'list';
            $this->resetForm();
            $this->loadProducts();
            $this->bannerType = 'success';
            $this->bannerMessage = 'Product created';
        } catch (\Throwable $e) {
            logger()->error('createProduct failed', ['error' => $e->getMessage()]);
            $this->bannerType = 'error';
            $this->bannerMessage = 'Create failed: '.$e->getMessage();
        }
    }

    public function updateProduct(): void
    {
        try {
            if (!$this->productId) throw new \InvalidArgumentException('Missing product id');
            if ($this->useApi) {
                $req = Http::withToken($this->apiToken());
                if ($this->imageUpload) {
                    $req = $req->asMultipart()
                        ->attach('image', fopen($this->imageUpload->getRealPath(), 'r'), $this->imageUpload->getClientOriginalName());
                }
                $resp = $req->put($this->apiBase()."/api/products/{$this->productId}", [
                    'Title' => $this->Title,
                    'Description' => $this->Description,
                    'CategoryID' => $this->CategoryID,
                    'Price' => $this->Price,
                    'StockQuantity' => $this->StockQuantity,
                    'Image' => $this->Image,
                ]);
                if (!$resp->successful()) {
                    throw new \RuntimeException('API update failed: '.$resp->status());
                }
            } else {
                $this->validate($this->rulesForUpdate());
                $p = Product::find($this->productId);
                if (!$p) throw new \RuntimeException('Product not found');
                $p->Title = $this->Title;
                $p->Description = $this->Description;
                $p->CategoryID = $this->CategoryID ?: null;
                $p->Price = $this->Price !== '' ? $this->Price : 0;
                $p->StockQuantity = $this->StockQuantity !== '' ? $this->StockQuantity : 0;
                if ($this->imageUpload) {
                    $path = $this->imageUpload->store('products', 'public');
                    $p->Image = 'storage/'.$path;
                } elseif ($this->Image) {
                    $p->Image = $this->Image;
                }
                $p->save();
                // Audit: admin update via dashboard (non-API path)
                $this->logAdminAudit('update', $p->ProductID, [
                    'Title' => $p->Title,
                    'CategoryID' => $p->CategoryID,
                    'Price' => $p->Price,
                    'StockQuantity' => $p->StockQuantity,
                    'Image' => $p->Image,
                ]);
            }
            $this->showEdit = false;
            $this->mode = 'list';
            $this->resetForm();
            $this->loadProducts();
            $this->bannerType = 'success';
            $this->bannerMessage = 'Product updated';
        } catch (\Throwable $e) {
            logger()->error('updateProduct failed', ['error' => $e->getMessage()]);
            $this->bannerType = 'error';
            $this->bannerMessage = 'Update failed: '.$e->getMessage();
        }
    }

    public function confirmDelete($id): void
    {
        // If product is referenced by any order items, block deletion upfront
        if (OrderItem::where('ProductId', $id)->exists()) {
            $this->blockedDeleteMessage = 'This product has been ordered by customers and cannot be deleted.';
            $this->showBlockedDelete = true;
            $this->productId = null;
            $this->showDelete = false;
            // Audit: admin attempted delete but blocked
            $this->logAdminAudit('delete_blocked', $id, ['reason' => 'product_has_order_items']);
            return;
        }
        $this->productId = $id;
        $this->showDelete = true;
    }

    public function deleteProduct(): void
    {
        try {
            if (!$this->productId) throw new \InvalidArgumentException('Missing product id');
            if ($this->useApi) {
                $resp = Http::withToken($this->apiToken())->delete($this->apiBase()."/api/products/{$this->productId}");
                if (!$resp->successful()) {
                    throw new \RuntimeException('API delete failed: '.$resp->status());
                }
            } else {
                // Block delete if product is referenced by any order items
                if (OrderItem::where('ProductId', $this->productId)->exists()) {
                    // Audit: admin attempted delete but blocked
                    $this->logAdminAudit('delete_blocked', $this->productId, ['reason' => 'product_has_order_items']);
                    throw new \RuntimeException('Cannot delete: product has existing order items');
                }
                Product::where('ProductID', $this->productId)->delete();
                // Audit: admin delete via dashboard (non-API path)
                $this->logAdminAudit('delete', $this->productId, []);
            }
            $this->showDelete = false;
            $this->productId = null;
            $this->loadProducts();
            $this->bannerType = 'success';
            $this->bannerMessage = 'Product deleted';
        } catch (\Throwable $e) {
            logger()->error('deleteProduct failed', ['error' => $e->getMessage()]);
            $this->bannerType = 'error';
            $this->bannerMessage = 'Delete failed: '.$e->getMessage();
        }
    }

    protected function logAdminAudit(string $action, $productId, array $meta = []): void
    {
        // Only log here for non-API path to avoid duplicates
        if ($this->useApi) return;
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
            Log::warning('Mongo admin UI log insert failed', [
                'action' => $action,
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
        } finally {
            // Mirror to file for audit/demo visibility
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

    protected function resetForm(): void
    {
        $this->productId = null;
        $this->Title = '';
        $this->Description = '';
        $this->CategoryID = '';
        $this->Price = '';
        $this->StockQuantity = '';
        $this->Image = '';
        $this->imageUpload = null;
    }

    public function render()
    {
        return view('livewire.admin.product-management', [
            'products' => $this->products,
            'pagination' => $this->pagination,
            'mode' => $this->mode,
            'useApi' => $this->useApi,
        ]);
    }
}









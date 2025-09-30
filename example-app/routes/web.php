<?php

use App\Livewire\Customer\HomePage;
use App\Livewire\Customer\ProductDetails;
use App\Livewire\Customer\ProductList;
use App\Livewire\Customer\ShoppingCart;
use App\Livewire\Customer\Checkout;
use App\Livewire\Customer\Profile;
use App\Livewire\Customer\OrderConfirmation;
use App\Livewire\Customer\Orders as CustomerOrders;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\ProductManagement;
use App\Livewire\Admin\CategoryManagement;
use App\Livewire\Admin\OrderManagement;
use App\Livewire\Admin\CustomerManagement;
use App\Livewire\Admin\BundleManagement;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use MongoDB\BSON\UTCDateTime;

// Customer Routes
Route::get('/', HomePage::class)->name('home');
Route::get('/products', ProductList::class)->name('products');
Route::get('/categories', HomePage::class)->name('categories');
Route::get('/bundle-offers', HomePage::class)->name('bundle-offers');
Route::get('/product/{id}', ProductDetails::class)->name('product.details');
Route::get('/cart', ShoppingCart::class)->name('cart');
Route::get('/checkout', Checkout::class)->name('checkout')->middleware('auth');
Route::get('/order-confirmation/{id}', OrderConfirmation::class)->name('order.confirmation')->middleware('auth');
Route::get('/orders', CustomerOrders::class)->name('orders')->middleware('auth');
Route::get('/profile', Profile::class)->name('profile')->middleware('auth');
Route::view('/about', 'pages.about')->name('about');

// Jetstream profile layout expects a `dashboard` route.
Route::get('/dashboard', function () {
    if (Auth::check() && (Auth::user()->is_admin ?? false)) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('home');
})->middleware('auth')->name('dashboard');




// Application settings (authenticated)
Route::middleware('auth')->group(function () {
    // Settings routes used by the layout/header and settings components
    Route::get('/settings/profile', \App\Livewire\Settings\Profile::class)->name('settings.profile');
    Route::get('/settings/password', \App\Livewire\Settings\Password::class)->name('settings.password');
    Route::get('/settings/appearance', \App\Livewire\Settings\Appearance::class)->name('settings.appearance');
});

// Admin Routes
Route::get('/admin', function () {
    // Always require a fresh login for admin area
    Auth::logout();
    // Ensure intended points to the admin dashboard after login
    session()->put('url.intended', route('admin.dashboard'));
    session()->put('force_admin_login', true);
    return redirect()->route('login')->with('status', 'Please sign in as admin');
})->name('admin.entry');

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', AdminDashboard::class)->name('admin.dashboard');
    Route::get('/products', ProductManagement::class)->name('admin.products');
    Route::get('/orders', OrderManagement::class)->name('admin.orders');
    Route::get('/bundles', BundleManagement::class)->name('admin.bundles');
});

require __DIR__.'/auth.php';

// ------------------------------------------------------------
// Local-only demo routes to illustrate SQLi and CSRF protections
// These routes are ONLY registered in local environment for testing
// ------------------------------------------------------------
if (app()->environment('local')) {
    // Intentionally vulnerable SQL endpoint (for demo only)
    Route::get('/vuln-sql', function () {
        $email = request('email', '');
        // Vulnerable concatenated SQL (do not use in production)
        $sql = "select id, name, email from users where email = '" . $email . "'";
        $rows = DB::select($sql);
        return response()->json([
            'query' => $sql,
            'count' => count($rows),
            'data' => $rows,
        ]);
    });

    // Safe SQL endpoint using parameter binding + basic validation
    Route::get('/safe-sql', function () {
        $email = request('email', '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Invalid email'], 422);
        }
        $rows = DB::table('users')->select('id', 'name', 'email')->where('email', $email)->get();
        return response()->json([
            'count' => $rows->count(),
            'data' => $rows,
        ]);
    });

    // CSRF protection demo: GET form + POST handler (requires @csrf)
    Route::view('/csrf-demo', 'pages.csrf-demo');
    Route::post('/csrf-demo/submit', function () {
        session(['csrf_demo' => now()->toDateTimeString()]);
        return back()->with('status', 'CSRF-protected action succeeded');
    })->name('csrf.demo.submit');
}

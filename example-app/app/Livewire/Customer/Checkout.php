<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Layout as LivewireLayout;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\PaymentIntent;

#[LivewireLayout('layouts.customer')]
class Checkout extends Component
{
    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $clientSecret = '';
    public $amount = 0;

    public function mount()
    {
        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return redirect()->route('products');
        }
        $this->amount = (int) round(collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']) * 100); // cents
        $user = Auth::user();
        if ($user) {
            $this->name = $user->name ?? '';
            $this->email = $user->email ?? '';
        }
        $this->createPaymentIntent();
    }

    public function rules()
    {
        return [
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255'],
            'phone' => ['required','string','max:20'],
            'address' => ['required','string','max:1000'],
        ];
    }

    public function createPaymentIntent(): void
    {
        if ($this->amount <= 0) { return; }
        if (empty(config('services.stripe.secret'))) { return; }
        Stripe::setApiKey(config('services.stripe.secret'));
        $intent = PaymentIntent::create([
            'amount' => $this->amount,
            'currency' => 'usd',
            'metadata' => [ 'purpose' => 'checkout' ],
            'automatic_payment_methods' => ['enabled' => true],
        ]);
        $this->clientSecret = $intent->client_secret;
    }

    public function finalizeOrder(string $paymentIntentId)
    {
        $this->validate();
        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return redirect()->route('products');
        }

        // Verify intent if possible
        $status = 'pending';
        if (!empty(config('services.stripe.secret'))) {
            Stripe::setApiKey(config('services.stripe.secret'));
            $pi = PaymentIntent::retrieve($paymentIntentId);
            $status = $pi->status === 'succeeded' ? 'paid' : $pi->status;
        }

        $order = Order::create([
            'CustomerID' => null,
            'CustomerName' => $this->name,
            'PhoneNo' => $this->phone,
            'Address' => $this->address,
            'Email' => $this->email,
            'PaymentMethod' => 'card',
            'PaymentStatus' => $status,
            'Status' => 'pending',
        ]);

        try {
            DB::transaction(function () use ($cart, $order) {
                foreach ($cart as $item) {
                    $pid = (int)($item['id'] ?? 0);
                    $qty = (int)($item['quantity'] ?? 1);
                    if ($pid <= 0 || $qty <= 0) {
                        throw new \RuntimeException('Invalid cart item');
                    }
                    $product = Product::where('ProductID', $pid)->lockForUpdate()->firstOrFail();
                    $available = (int)($product->StockQuantity ?? 0);
                    if ($available < $qty) {
                        throw new \RuntimeException("Insufficient stock for {$product->Title}. Available: {$available}");
                    }
                    // Create order item snapshot
                    OrderItem::create([
                        'OrderId' => $order->OrderId,
                        'ProductId' => $pid,
                        'ProductName' => $product->Title ?? ($item['title'] ?? ''),
                        'UnitPrice' => $product->Price ?? ($item['price'] ?? 0),
                        'Quantity' => $qty,
                        'TotalPrice' => ($product->Price ?? ($item['price'] ?? 0)) * $qty,
                    ]);
                    // Deduct stock
                    $product->decrement('StockQuantity', $qty);
                }
            }, 3);
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->route('cart');
        }

        Session::forget('cart');
        return redirect()->route('order.confirmation', ['id' => $order->OrderId]);
    }

    public function render()
    {
        $cart = Session::get('cart', []);
        $subtotal = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);
        return view('livewire.customer.checkout', [
            'cart' => $cart,
            'subtotal' => $subtotal,
        ]);
    }
}

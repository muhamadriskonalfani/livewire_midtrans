<?php

namespace App\Livewire;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Str;
use Midtrans\Snap;
use Midtrans\Config;

class ListProduct extends Component
{
    public $user;
    public $products, $cart, $cartItems;
    public $totalPrice = 0;
    public $isOrder = false;
    public $showCheckoutButton = false;

    public function mount()
    {
        $this->user = Auth::user();
        $this->loadData();
    }

    public function loadData()
    {
        $this->products = Product::all();
        $this->cart = Cart::where('user_id', $this->user->id)->first();
        if ($this->cart) {
            $this->cartItems = CartItem::with('product')->where('cart_id', $this->cart->id)->get();
            $this->totalPrice = $this->cartItems->sum(fn ($item) => $item->product->price * $item->quantity);
            if ($this->totalPrice) {
                $this->showCheckoutButton = true;
            }
        }
    }

    public function checkout()
    {
        // Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.sanitized');
        Config::$is3ds = config('midtrans.3ds');

        $midtransOrderId = 'ORDER-' . uniqid();

        // Data transaksi
        $params = [
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => $this->totalPrice,
            ],
            'customer_details' => [
                'first_name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'callbacks' => [
                'finish' => route('my_orders'),
            ],
        ];

        // Dapatkan Snap URL
        $snapUrl = Snap::createTransaction($params)->redirect_url;

        // Buat Transaksi di Database
        $this->createOrderTransaction($midtransOrderId);

        // Dispatch ke frontend untuk redirect
        $this->dispatch('redirect-to-midtrans', $snapUrl);
    }

    public function createOrderTransaction($midtransOrderId)
    {
        DB::beginTransaction();

        try {
            // Buat order
            $order = new Order();
            $order->user_id = $this->user->id;
            $order->total_price = $this->totalPrice;
            $order->status = 'pending';
            $order->save();

            // Salin semua cart item ke order_items
            foreach ($this->cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
            }

            // Buat payment
            Payment::create([
                'user_id' => $this->user->id,
                'order_id' => $order->id,
                'midtrans_order_id' => $midtransOrderId,
                'bill' => $this->totalPrice,
                'transaction_status' => 'pending',
            ]);

            DB::commit();

            // (Opsional) Kosongkan cart
            CartItem::where('cart_id', $this->cart->id)->delete();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.list-product');
    }

    #[On('addToCart')]
    public function addToCart($productId)
    {
        // Cari atau buat keranjang milik user
        $userCart = Cart::firstOrCreate(
            ['user_id' => $this->user->id],
            ['created_at' => now(), 'updated_at' => now()]
        );

        // Cek apakah produk sudah ada dalam keranjang ini
        $item = CartItem::where('cart_id', $userCart->id)
                        ->where('product_id', $productId)
                        ->first();

        if ($item) {
            // Jika sudah ada, tambahkan quantity
            $item->quantity += 1;
            $item->save();
        } else {
            // Jika belum, buat item baru
            $cartItems = new CartItem();
            $cartItems->cart_id = $userCart->id;
            $cartItems->product_id = $productId;
            $cartItems->quantity = 1;
            $cartItems->save();
        }

        $this->loadData();
    }
}

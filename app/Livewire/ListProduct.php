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
        dd($this->totalPrice);
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

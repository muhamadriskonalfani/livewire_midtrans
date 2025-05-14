<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MyOrder extends Component
{
    public $user, $orders;

    public function mount()
    {
        $this->user = Auth::user();
        $this->loadOrders();
    }

    public function loadOrders()
    {
        $this->orders = Order::with('orderItems')->where('user_id', $this->user->id)->get();
    }

    public function render()
    {
        return view('livewire.my-order');
    }
}

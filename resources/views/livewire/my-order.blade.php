<div>
    <h4 class="mb-4">Pesanan Saya</h4>

    <div class="row">
        @forelse ($orders as $order)
            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow">
                    <div class="card-header bg-dark text-white">
                        <strong>Order #{{ $order->id }}</strong><br>
                        <small class="text-light">Tanggal: {{ $order->created_at->format('d M Y') }}</small><br>
                        <span class="badge bg-{{ $order->status == 'success' ? 'success' : 'secondary' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div class="card-body">
                        @foreach ($order->orderItems as $item)
                            <div class="d-flex align-items-center mb-3">
                                <img src="{{ asset('assets/product/' . $item->product->image) }}" 
                                    alt="{{ $item->product->name }}" 
                                    class="rounded me-3" 
                                    style="width: 60px; height: 60px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-1">{{ $item->product->name }}</h6>
                                    <small>Qty: {{ $item->quantity }}</small><br>
                                    <small>Rp {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="card-footer bg-light">
                        <strong>Total: </strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    Belum ada pesanan.
                </div>
            </div>
        @endforelse
    </div>
</div>

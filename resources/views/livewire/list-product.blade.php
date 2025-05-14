@push('styles')
<style>
    .drag-ghost {
        opacity: 0.8;
        background-color: #f8f9fa !important;
        height: 195px !important;
        width: 160px !important;
        overflow: hidden;
        pointer-events: none;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        border-radius: 5px;
    }
</style>
@endpush

<div>
    <div class="row">
        <!-- Daftar Produk -->
        <div class="col-md-8">
            <h5>Daftar Produk</h5>
            <div id="product-list" class="row">
                @foreach ($products as $product)
                    <div class="col-md-3 mb-4 product-item" data-id="{{ $product->id }}">
                        <div class="card bg-secondary text-white shadow">
                            <img src="{{ asset('assets/product/' . $product->image) }}" class="card-img-top" style="height: 150px; object-fit: cover;">
                            <div class="card-body">
                                <h6 class="card-title">{{ $product->name }}</h6>
                                <p class="card-text">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Keranjang -->
        <div class="col-md-4">
            <h5>Keranjang</h5>
            <div class="card bg-dark shadow overflow-hidden h-100">
                <div id="cart-zone" class="card-body drop-zone">
                    @if ($cart)
                        @forelse ($cartItems as $item)
                            <div class="product-card mb-3 bg-light text-dark rounded p-2 shadow-sm">
                                <div class="d-flex align-items-center">
                                    <img src="{{ asset('assets/product/' . $item->product->image) }}" class="me-3 rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-1">{{ $item->product->name }}</h6>
                                        <small>Rp {{ number_format($item->product->price, 0, ',', '.') }}</small><br>
                                        <small>Qty: {{ $item->quantity }}</small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-white">"Your cart is empty. Drag products here to start shopping!"</div>
                        @endforelse
                    @else
                        <div class="text-white">"Your cart is empty. Drag products here to start shopping!"</div>
                    @endif
                </div>
                @if ($showCheckoutButton)
                    <div class="p-3 bg-secondary text-white">
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Total:</strong>
                            <strong>Rp {{ number_format($totalPrice, 0, ',', '.') }}</strong>
                        </div>
                        <div>
                            <button wire:loading wire:target="checkout" type="button" class="btn btn-warning w-100 disabled">Loading...</button>
                            <button wire:loading.remove wire:target="checkout" wire:click="checkout" wire:confirm="Apakah anda yakin?" type="button" class="btn btn-warning w-100">Checkout</button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Init Sortable for product list (just to allow dragging)
        Sortable.create(document.getElementById('product-list'), {
            group: {
                name: 'products',
                pull: 'clone',
                put: false,
            },
            sort: false,
            animation: 150,
            // ghostClass: 'drag-ghost'
        });

        // Init Sortable for cart zone
        Sortable.create(document.getElementById('cart-zone'), {
            group: {
                name: 'products',
                pull: false,
                put: true,
            },
            animation: 150,
            ghostClass: 'drag-ghost',
            onAdd: function (evt) {
                const item = evt.item;
                const productId = item.dataset.id;

                if (productId) {
                    Livewire.dispatch('addToCart', { productId: productId });

                    // Remove the DOM element after drop to prevent clone display
                    item.remove();
                }
            }
        });

        // Snap Redirect Midtrans
        Livewire.on('redirect-to-midtrans', url => {
            window.location.href = url;
        });
    });
</script>
@endpush

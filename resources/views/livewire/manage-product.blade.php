<div>
    <!-- Form Tambah Produk -->
    @if ($openForm)
    <div class="mb-5">
        <h5>Form Tambah Produk</h5>
        <div class="mt-3" style="max-width: 400px;">
            <form wire:submit.prevent="saveProduct" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Produk</label>
                    <input wire:model="name" type="text" class="form-control" id="name" placeholder="Nama Produk" required>
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Harga</label>
                    <input wire:model="price" type="number" class="form-control" id="price" placeholder="Harga Produk" required>
                    @error('price') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                
                <div class="mb-3">
                    <label for="stock" class="form-label">Stok</label>
                    <input wire:model="stock" type="number" class="form-control" id="stock" placeholder="Stok Produk" required>
                    @error('stock') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-4">
                    <label for="image" class="form-label">Gambar</label>
                    <input wire:model="image" type="file" class="form-control" id="image">
                    @error('image') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div>
                    <button type="button" wire:loading class="btn btn-primary" disabled>Memproses</button>
                    <button type="submit" wire:loading.remove class="btn btn-primary">Tambahkan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Tabel Daftar Produk -->
    @if (!$openForm)
    <div class="">
        <h5>Daftar Barang</h5>
        <div class="mt-4">
            <button type="button" class="btn btn-primary" wire:click="$set('openForm', true)">Tambah Produk</button>
        </div>
        <div class="table-responsive mt-3">
            <table class="table table-hover table-dark align-middle">
                <thead class="">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Waktu Dibuat</th>
                        <th>Pilihan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $index => $product)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $product->name }}</td>
                            <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                            <td>{{ $product->stock }}</td>
                            <td>{{ $product->created_at->format('d-m-Y H:i') }}</td>
                            <td>
                                <a href="#" wire:click.prevent="deleteProduct({{ $product->id }})" wire:confirm="Apakah anda yakin?">Hapus</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada barang</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageProduct extends Component
{
    use WithFileUploads;
    public $products;
    public $name, $price, $stock, $image;
    public $openForm = false;

    public function mount()
    {
        $this->loadProducts();
    }

    public function loadProducts()
    {
        $this->products = Product::all();
    }

    public function saveProduct()
    {
        $this->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'image' => 'required|image|max:2048'
        ]);

        $productImage = null;

        if ($this->image) {
            $random = Str::random(10);
            $imgIdentity = 'product' . $random . '.webp';
            $location = 'assets/product/';
            $path = public_path($location . $imgIdentity);

            $image = ImageManager::imagick()
                ->read($this->image->path())
                ->resize(600, 600)
                ->toWebp(90);

            file_put_contents($path, $image);
            $productImage = $imgIdentity;
        }

        $product = new Product();
        $product->name = $this->name;
        $product->price = $this->price;
        $product->stock = $this->stock;
        $product->image = $productImage;
        $product->save();

        $this->reset(['name', 'price', 'stock', 'image']);
        $this->openForm = false;
        $this->loadProducts();
    }

    public function deleteProduct($id)
    {
        $product = Product::find($id);

        if ($product) {
            if ($product->image) {
                $imagePath = public_path('assets/product/' . $product->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $product->delete();
            
            $this->loadProducts();
        }
    }

    public function render()
    {
        return view('livewire.manage-product');
    }
}

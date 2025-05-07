<?php

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new class extends Component {
    
    use Toast, WithFileUploads;
    
    public $productId;

    public $name;
    public $price;
    public $category_id;
    public $image;
    public $image_upload;
    public $is_available = true;

    public $categories;

    public function mount($productId)
    {
        $this->productId = $productId;
        $this->categories = \App\Models\Category::all();
        if ($productId) {
            $product = Product::find($productId);
            $this->name = $product->name;
            $this->price = $product->price;
            $this->image = $product->image;
            $this->category_id = $product->category_id;
            $this->is_available = $product->is_available;
        }
    }

    public function submit()
    {
        $this->validate([
            'name' => 'required',
            'price' =>'required',
            'category_id' =>'required',
            'image_upload' => 'nullable|image|max:2024',
        ]);
        if ($this->productId) {
            $product = Product::findOrFail($this->productId);
            $product->name = $this->name;
            $product->price = $this->price;
            $product->category_id = $this->category_id;
            $product->is_available = $this->is_available ?? false;
            if ($this->image_upload) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $image = $this->image_upload->store('products', 'public');
                $product->image = $image;
            }
            $product->save();
            $this->success('Product updated successfully', position: 'bottom-right');
        } else {
            $product = Product::create([
                'name' => $this->name,
                'price' => $this->price,
                'category_id' => $this->category_id,
                'is_available' => $this->is_available ?? false,
            ]);

            if ($this->image_upload) {
                $image = $this->image_upload->store('products', 'public');
                $product->image = $image;
                $product->save();
            }

            $this->success('Product created successfully', position: 'bottom-right');
        }
        $this->dispatch('close-modal');
    }

    public function cancel()
    {
        $this->dispatch('close-modal');
    }
}; ?>

<div>
    <x-form wire:submit.prevent="submit" no-separator>
        <x-file label="Image" wire:model="image_upload" accept="image/png, image/jpeg" crop-after-change> 
            <img src="{{ $image ? url('storage', $image) : asset('images/no_image.jpg') }}" class="h-46 rounded-lg" />
        </x-file>
        <x-input label="Name" placeholder="Name" wire:model="name" />
        <x-input label="Price" placeholder="Price" wire:model="price" />
        <x-select label="Category" wire:model="category_id" :options="$categories" placeholder="Select a category" placeholder-value="0"/>
        <x-checkbox label="Is Available" wire:model="is_available" />
        <x-slot:actions>
            <x-button label="Cancel" wire:click="cancel" />
            <x-button label="{{ $productId ? 'Update' : 'Submit' }}" class="btn-primary" type="submit" spinner="save"/>
        </x-slot:actions>
    </x-form>
</div>

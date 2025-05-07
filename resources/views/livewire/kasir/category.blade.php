<?php

use App\Helpers\CartManagement;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\On;

new 
#[Layout('components.layouts.frontend')]
#[Title('Category')]
class extends Component {
    
    public int $categoryId;

    public bool $drawer = false;

    public string $search = '';

    public int $perPage = 12;

    public array $sortBy = ['column' => 'created_at', 'direction' => 'asc'];

    public $count_cart = 0;

    public $drawerKey;

    public function mount(): void
    {
        $this->count_cart = count(CartManagement::getCartItemsFromCookie());
    }

    // get data collection
    public function products(): LengthAwarePaginator
    {
        return Product::query()
            ->withAggregate('category', 'name')
            ->where('category_id', $this->categoryId)
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    public function with(): array
    {
        return [
            'products' => $this->products(),
            'category' => Product::find($this->categoryId)->category->name,
        ];
    } 

    public function open_cart(): void
    {
        $this->drawerKey = uniqid();
        $this->drawer = true;
    }

    public function addToCart(int $productId): void
    {
        $cart = CartManagement::addItemToCart($productId);
        $this->dispatch('cart-count-update', count_cart: $cart);
    }

    #[On('cart-count-update')]
    public function cart_count($count_cart): void
    {
        $this->count_cart = $count_cart;
    }

    #[On('close-drawer')]
    public function close_drawer(): void
    {
        $this->drawerKey = null;
        $this->drawer = false;
    }
}; ?>

<div>
    <x-header title="Category {{ $category }}" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input class="lg:w-120" icon="o-bolt" placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Cart" icon="o-shopping-cart" responsive @click="$wire.open_cart" :badge="$count_cart" badge-classes="badge-primary badge-soft" />
        </x-slot:actions>
    </x-header>

    <x-drawer :key="$drawerKey" wire:model="drawer" class="w-11/12 lg:w-1/3" right without-trap-focus>
        @livewire('kasir.cart', key($drawerKey))
    </x-drawer>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach ($products as $product)
            <x-card title="{{ $product->name }}" separator shadow>
                <div class="flex justify-between">
                    <div class="text-sm text-gray-500">Rp. {{ number_format($product->price, 0, ',', '.') }}</div>
                    <div class="text-sm text-gray-500">{{ $product->category_name }}</div>
                </div>
                <x-slot:figure>
                    <img src="{{ $product->image? url('storage', $product->image) : asset('images/no_image.jpg') }}" />
                </x-slot:figure>
                <x-slot:menu>
                    <x-button wire:click.prevent="addToCart({{ $product->id }})" icon="o-plus" class="btn-error btn-sm btn-soft btn-circle"/>
                </x-slot:menu>
            </x-card>
        @endforeach
    </div>
</div>

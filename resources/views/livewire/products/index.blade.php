<?php

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new class extends Component {
    
    use Toast, WithPagination;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public int $perPage = 10;

    public $productId = null;

    public $formModal = false;
    
    public $modalKey;

    #[On('close-modal')]
    public function closeModal(): void
    {
        $this->reset(['productId', 'formModal', 'modalKey']);
        $this->formModal = false;
    }

    // open add user modal
    public function add(): void
    {
        $this->modalKey = uniqid();
        $this->productId = null;
        $this->formModal = true;
    }

    // open edit user modal
    public function edit($id): void
    {
        $this->productId = $id;
        $this->modalKey = uniqid();
        $this->formModal = true;
    }

    // delete user
    public function delete(Product $user): void
    {
        $user->delete();
        $this->success("User $user->name success deleted", position: 'bottom-right');
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'image', 'label' => 'Image', 'class' => 'w-10', 'sortable' => false],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'category_name', 'label' => 'Category'],
            ['key' => 'price', 'label' => 'Price'],
            ['key' => 'is_available', 'label' => 'Status'],
            ['key' => 'created_at', 'label' => 'Created At', 'format' => ['date', 'd/m/Y'], 'class' => 'w-42'],
            ['key' => 'updated_at', 'label' => 'Updated At', 'format' => ['date', 'd/m/Y'], 'class' => 'w-42'],
        ];
    }

    // Reset pagination when any component property changes
    public function updated($property): void
    {
        if (!is_array($property) && $property != "") {
            $this->resetPage();
        }
    }

    // get data collection
    public function products(): LengthAwarePaginator
    {
        return Product::query()
            ->withAggregate('category', 'name')
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    public function with(): array
    {
        return [
            'products' => $this->products(),
            'headers' => $this->headers()
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Products" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Create" @click="$wire.add" responsive icon="o-plus" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card shadow>
        <x-table 
            :headers="$headers" 
            :rows="$products" 
            :sort-by="$sortBy"
            @row-click="$wire.edit($event.detail.id)"
            per-page="perPage"
            :per-page-values="[5, 10, 20]"
            with-pagination>
            @scope('cell_image', $item)                                                    
                <img src="{{ $item->image ? url('storage', $item->image) : asset('images/no_image.jpg') }}" class="w-24 rounded" />
            @endscope
            @scope('cell_price', $item)
                {{ number_format($item->price, 0, ',', '.') }}
            @endscope
            @scope('cell_is_available', $item)
                <x-icon :name="$item->is_available ? 'o-check-circle' : 'o-x-mark'" :class="$item->is_available ? 'text-success' : 'text-error'" />
            @endscope
            @scope('actions', $item)
                <x-button icon="o-trash" wire:click="delete({{ $item['id'] }})" wire:confirm="Are you sure?" spinner class="btn-ghost btn-sm text-error" />
            @endscope
        </x-table>
    </x-card>

    <!-- FORM MODAL -->
    <x-modal :key="$modalKey" wire:model="formModal"
        title="{{ $productId ? 'Form Edit Product' : 'Form Product' }}"
        class="backdrop-blur">
        @livewire('products.form', ['productId' => $productId], key($modalKey))
    </x-modal>
</div>

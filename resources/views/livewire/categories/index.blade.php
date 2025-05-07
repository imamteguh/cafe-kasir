<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new
#[Title('Categories')] 
class extends Component {
    
    use Toast, WithPagination;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public $categoryId = null;

    public $formModal = false;
    
    public $modalKey;

    #[On('close-modal')]
    public function closeModal(): void
    {
        $this->reset(['categoryId', 'formModal', 'modalKey']);
        $this->formModal = false;
    }

    // open add modal
    public function add(): void
    {
        $this->modalKey = uniqid();
        $this->categoryId = null;
        $this->formModal = true;
    }

    // open edit modal
    public function edit($id): void
    {
        $this->categoryId = $id;
        $this->modalKey = uniqid();
        $this->formModal = true;
    }

    // delete
    public function delete(Category $category): void
    {
        $category->delete();
        $this->success("Category $category->name success deleted", position: 'bottom-right');
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'created_at', 'label' => 'Created At', 'format' => ['date', 'd/m/Y'], 'class' => 'w-42'],
            ['key' => 'updated_at', 'label' => 'Updated At', 'format' => ['date', 'd/m/Y'], 'class' => 'w-42'],
        ];
    }

    // get data collection
    public function categories(): LengthAwarePaginator
    {
        return Category::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'categories' => $this->categories(),
            'headers' => $this->headers()
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Categories" separator progress-indicator>
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
            :rows="$categories" 
            :sort-by="$sortBy"
            @row-click="$wire.edit($event.detail.id)"
            with-pagination>
            @scope('actions', $item)
                <x-button icon="o-trash" wire:click="delete({{ $item['id'] }})" wire:confirm="Are you sure?" spinner class="btn-ghost btn-sm text-error" />
            @endscope
        </x-table>
    </x-card>

    <!-- FORM MODAL -->
    <x-modal :key="$modalKey" wire:model="formModal"
        title="{{ $categoryId ? 'Form Edit Category' : 'Form Category' }}"
        class="backdrop-blur">
        @livewire('categories.form', ['categoryId' => $categoryId], key($modalKey))
    </x-modal>
</div>

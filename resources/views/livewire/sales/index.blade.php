<?php

use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new class extends Component {
    
    use Toast, WithPagination;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public int $perPage = 10;

    public function delete(Sale $sale): void
    {
        $sale->delete();
        $this->success("Data $sale->invoice_number success deleted", position: 'bottom-right');
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'invoice_number', 'label' => 'Invoice Number'],
            ['key' => 'payment_method', 'label' => 'Payment Method'],
            ['key' => 'total_amount', 'label' => 'Total Amount'],
            ['key' => 'total_paid', 'label' => 'Total Paid'],
            ['key' => 'change', 'label' => 'Change'],
            ['key' => 'created_at', 'label' => 'Created At', 'format' => ['date', 'd/m/Y H:i:s'], 'class' => 'w-52'],
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
    public function sales(): LengthAwarePaginator
    {
        return Sale::query()
            ->when($this->search, fn(Builder $q) => $q->where('invoice_number', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    public function with(): array
    {
        return [
            'sales' => $this->sales(),
            'headers' => $this->headers()
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Sales" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
    </x-header>

    <!-- TABLE  -->
    <x-card shadow>
        <x-table 
            :headers="$headers" 
            :rows="$sales" 
            :sort-by="$sortBy"
            per-page="perPage"
            :per-page-values="[5, 10, 20]"
            with-pagination
            link="/sales/{id}">
            @scope('cell_id', $item, $sales)
                {{ $loop->iteration + ($sales->currentPage() - 1) * $sales->perPage() }}
            @endscope
            @scope('cell_payment_method', $item)
                @if ($item->payment_method == 'cash')
                    <x-badge value="{{ ucfirst($item->payment_method) }}" class="badge-success" />
                @elseif ($item->payment_method == 'debit')
                    <x-badge value="{{ ucfirst($item->payment_method) }}" class="badge-primary" />
                @else
                    <x-badge value="{{ ucfirst($item->payment_method) }}" class="badge-warning" />
                @endif
            @endscope
            @scope('cell_total_amount', $item)
                Rp. {{ number_format($item->total_amount, 0, ',', '.') }}
            @endscope
            @scope('cell_total_paid', $item)
                Rp. {{ number_format($item->total_paid, 0, ',', '.') }}
            @endscope
            @scope('cell_change', $item)
                Rp. {{ number_format($item->change, 0, ',', '.') }}
            @endscope
            @scope('actions', $item)
                <x-button icon="o-trash" wire:click="delete({{ $item['id'] }})" wire:confirm="Are you sure?" spinner class="btn-ghost btn-sm text-error" />
            @endscope
        </x-table>
    </x-card>

</div>

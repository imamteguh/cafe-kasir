<?php

use Livewire\Volt\Component;
use App\Models\Sale;
use Livewire\Attributes\Title;

new 
#[Title('Order Detail')]
class extends Component {
    
    public Sale $sale;

    public function headers(): array
    {
        return [
            ['key' => 'image', 'label' => 'Image', 'class' => 'w-24', 'sortable' => false],
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-64', 'sortable' => false],
            ['key' => 'quantity', 'label' => 'Qty', 'sortable' => false],
            ['key' => 'price', 'label' => 'Price', 'sortable' => false],
            ['key' => 'subtotal', 'label' => 'Subtotal', 'sortable' => false],
        ];
    }

    public function details()
    {
        return $this->sale->details;
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'details' => $this->details(),
        ];
    }
}; ?>

<div>
    <x-header title="Order #{{ $sale->id }}" separator progress-indicator />

    <div class="lg:grid grid-cols-2 gap-8">
        <x-card title="{{ $sale->invoice_number }}" separator shadow>
            <div class="flex justify-between font-medium">
                <p>Kasir</p>
                <p>{{ $sale->user->name }}</p>
            </div>
            <div class="flex justify-between font-medium">
                <p>Date</p>
                <p>{{ $sale->created_at->format('d-m-Y H:i:s') }}</p>
            </div>
            <div class="flex justify-between font-medium">
                <p>Payment Method</p>
                <p>{{ ucfirst($sale->payment_method) }}</p>
            </div>
        </x-card>

        <x-card title="Summary" separator shadow>
            <div class="flex justify-between font-medium">
                <p>Total Items</p>
                <p>{{ $sale->details->count() }} (Items)</p>
            </div>
            <div class="flex justify-between font-medium">
                <p>Total Amount</p>
                <p>{{ 'Rp. '. number_format($sale->total_amount, 0, ',', '.') }}</p>
            </div>
            <div class="flex justify-between font-medium">
                <p>Pay Amount</p>
                <p>{{ 'Rp. '. number_format($sale->total_paid, 0, ',', '.') }}</p>
            </div>
            <div class="flex justify-between font-medium">
                <p>Change</p>
                <p>{{ 'Rp. '. number_format($sale->change, 0, ',', '.') }}</p>
            </div>
        </x-card>

        <x-card class="col-span-2" shadow>
            <x-table
                :headers="$headers"
                :rows="$details">
                @scope('cell_image', $item)                                                    
                    <img src="{{ $item->product->image ? url('storage', $item->product->image) : asset('images/no_image.jpg') }}" class="w-24 rounded" />
                @endscope
                @scope('cell_name', $item)
                    {{ $item->product->name }}
                @endscope
                @scope('cell_quantity', $item)
                    {{ $item->quantity }}
                @endscope
                @scope('cell_price', $item)
                    {{ 'Rp. '. number_format($item->price, 0, ',', '.') }}
                @endscope
                @scope('cell_subtotal', $item)
                    {{ 'Rp. '. number_format($item->subtotal, 0, ',', '.') }}
                @endscope
            </x-table>
        </x-card>
    </div>
</div>
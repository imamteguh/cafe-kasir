<?php

use App\Models\Sale;
use Livewire\Volt\Component;

new class extends Component {

    public $invoice;
    public $sale;
    public $saleDetail;

    public function mount($invoice)
    {
        $this->invoice = $invoice;
        if ($this->invoice) {
            $this->sale = Sale::where('invoice_number', $this->invoice)->first();
            $this->saleDetail = $this->sale ? $this->sale->details : collect();
        }
    }

    public function with(): array
    {
        return [
            'order' => $this->sale,
            'details' => $this->saleDetail,
        ];
    }

    public function close(): void
    {
        $this->dispatch('close-struk-modal');
    }
}; ?>

<div>
    <!-- Logo & Header -->
    <div class="text-center mb-4">
        <h1 class="text-xl font-bold">POS CAFE</h1>
        <p class="text-sm text-gray-600">Jl. H. Juanda No. 14</p>
    </div>

    <!-- Info -->
    <div class="text-sm text-gray-700 flex justify-between py-3 border-b border-dashed border-gray-400">
        <span>No: {{ $order ? $order->invoice_number : '-' }}</span>
        <span>{{ $order ? $order->created_at->format('d/m/y H.i') : '-' }}</span>
    </div>

    <!-- Items Table -->
    <div class="mb-4 mt-4">
        <table class="w-full text-sm text-left table-auto">
            <thead class="font-semibold">
                <tr>
                    <th class="w-4 p-2">#</th>
                    <th class="p-2">Item</th>
                    <th class="text-center p-2">Qty</th>
                    <th class="text-right p-2">Subtotal</th>
                </tr>
            </thead>
            <tbody class="text-gray-800">
                @if($details && count($details))
                    @foreach($details as $i => $detail)
                        <tr>
                            <td class="p-2">{{ $i+1 }}</td>
                            <td class="p-2">
                                <div>{{ $detail->product->name }}</div>
                                <div class="text-xs text-gray-500">Rp. {{ number_format($detail->price, 0, ',', '.') }}</div>
                            </td>
                            <td class="text-center p-2">{{ $detail->quantity }}</td>
                            <td class="text-right p-2">Rp. {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" class="text-center p-2 text-gray-400">Tidak ada data</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Totals -->
    <div class="border-t border-dashed border-gray-400 py-3">
        <div class="flex justify-between font-medium">
            <span>TOTAL AMOUNT</span>
            <span>Rp. {{ $order ? number_format($order->total_amount, 0, ',', '.') : '0' }}</span>
        </div>
        <div class="flex justify-between font-medium">
            <span>PAY AMOUNT</span>
            <span>Rp. {{ $order ? number_format($order->total_paid, 0, ',', '.') : '0' }}</span>
        </div>
    </div>

    <div class="flex justify-between pt-2 mb-6 border-t border-dashed text-sm">
        <span>CHANGE</span>
        <span>Rp. {{ $order ? number_format($order->change, 0, ',', '.') : '0' }}</span>
    </div>
    <!-- Proceed Button -->
    <div class="mt-6 flex justify-between gap-4">
        <x-button label="Done" icon="o-check" class="flex-1/2 btn-secondary btn-lg" wire:click="close" />
        <x-button label="Print" icon="o-printer" class="flex-1/2 btn-primary btn-lg" />
    </div>
</div>
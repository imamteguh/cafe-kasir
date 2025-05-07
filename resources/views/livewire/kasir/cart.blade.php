<?php

use Livewire\Volt\Component;
use App\Helpers\CartManagement;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Mary\Traits\Toast;

new class extends Component {
    
    use Toast;

    public $cart_items = [];
    public int $total_price = 0;
    public int $cash = 0;
    public int $change = 0;
    public $payment = 'cash';

    public function mount(): void
    {
        $this->cart_items = CartManagement::getCartItemsFromCookie();
        $this->total_price = CartManagement::calculateTotalPrice($this->cart_items);
    }

    public function close(): void
    {
        $this->dispatch('close-drawer');
    }

    public function process(): void
    {
        if ($this->payment == 'cash' && $this->cash < $this->total_price) {
            $this->error('Cash is not enough');
            return;
        }

        if ($this->payment == 'debit' || $this->payment == 'qris') {
            $this->cash = $this->total_price;
            $this->change = 0;
        }

        $invoice_number = 'INV-'.date('YmdHis');
        $data = [
            'invoice_number' => $invoice_number,
            'user_id' => Auth::user()->id,
            'payment_method' => $this->payment,
            'total_amount' => $this->total_price,
            'total_paid' => $this->cash,
            'change' => $this->change,
        ];
        $sale = Sale::create($data);
        $sale->details()->createMany($this->cart_items);
        CartManagement::clearCartItems();

        $this->dispatch('cart-count-update', count_cart: 0);
        $this->dispatch('close-drawer');

        $this->success('Order processed successfully', position: 'bottom-end');
        $this->dispatch('open-struk-modal', $invoice_number);
    }

    public function updatedPayment($value = null): void
    {
        if ($value == 'cash') {
            $this->cash = 0;
            $this->change = 0;
        }
    }

    public function updatedCash($value = null): void
    {
        if (empty($value)) {
            $this->change = 0;
            return;
        }
        $this->change = $value - $this->total_price;
    }

    public function addCash(int $amount): void
    {
        $this->cash += $amount;
        $this->change = $this->cash - $this->total_price;
    }

    public function incrementQuantity($product_id): void
    {
        $this->cart_items = CartManagement::incrementQuantityToCartItem($product_id, $this->cart_items);
        $this->total_price = CartManagement::calculateTotalPrice($this->cart_items);
        $this->change = $this->cash - $this->total_price;
    }

    public function decrementQuantity($product_id): void
    {
        $item = collect($this->cart_items)->firstWhere('product_id', $product_id);
        if ($item['quantity'] == 1) {
            $this->cart_items = CartManagement::removeCartItem($product_id);
            $this->total_price = CartManagement::calculateTotalPrice($this->cart_items);
            $this->change = $this->cash - $this->total_price;
            $this->dispatch('cart-count-update', count_cart: count($this->cart_items));
            return;
        }
        $this->cart_items = CartManagement::decrementQuantityToCartItem($product_id, $this->cart_items);
        $this->total_price = CartManagement::calculateTotalPrice($this->cart_items);
        $this->change = $this->cash - $this->total_price;
    }
}; ?>

<div>
    <x-header title="Order List" size="text-xl" separator progress-indicator>
        <x-slot:actions>
            <x-button icon="o-x-mark" responsive @click="$wire.close" class="btn-soft btn-error btn-sm"/>
        </x-slot:actions>
    </x-header>
    <div class="mt-5 space-y-3">
        @forelse ($cart_items as $item)
            <div class="flex items-center gap-x-4 bg-base-200 p-2 rounded-md">
                <div class="flex-shrink-0">
                    <img src="{{ $item['image'] ? url('storage', $item['image']) : asset('images/no_image.jpg') }}" alt="{{ $item['name'] }}" class="w-15 h-15 rounded-md">
                </div>
                <div class="flex-auto">
                    <div class="flex justify-between text-sm">
                        <h4 class="font-bold text-gray-900 dark:text-gray-100">{{ $item['name'] }}</h4>
                        <p class="text-gray-500 dark:text-gray-200">{{ 'Rp. '. number_format($item['subtotal'], 0, ',', '.') }}</p>
                    </div>
                    <div class="flex items-center gap-x-2 mt-1">
                        <x-button label="-" wire:click="decrementQuantity({{ $item['product_id'] }})" class="btn-sm btn-soft btn-primary" />
                        <p class="text-gray-500 btn btn-sm cursor-default">{{ $item['quantity'] }}</p>
                        <x-button label="+" wire:click="incrementQuantity({{ $item['product_id'] }})" class="btn-sm btn-soft btn-primary" />
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500">
                <x-icon name="o-cube" class="w-10 h-10 mx-auto text-gray-400" />
                <p class="mt-2">No items in the cart</p>
            </div>
        @endforelse
    </div>

    @if ($cart_items)
    <div class="pt-5 mt-5 border-dashed">
        <div class="flex justify-between text-sm text-gray-900 dark:text-gray-100 pb-3">
            <p>Items</p>
            <p class="font-medium">{{ count($cart_items) }} (Items)</p>
        </div>
        <div class="flex justify-between text-sm text-gray-900 dark:text-gray-100 pb-3">
            <p>Subtotal</p>
            <p class="font-medium">{{ 'Rp. ' . number_format($total_price, 0, ',', '.') }}</p>
        </div>
        <div class="flex justify-between text-sm text-gray-900 dark:text-gray-100 pb-3">
            <p>Tax (12%)</p>
            <p class="font-medium">Rp. 0</p>
        </div>
    </div>
    <div class="border-dashed mt-2 border-t pt-3">
        <div class="flex justify-between text-md text-gray-900 dark:text-gray-100 pb-3">
            <p class="font-medium">Total</p>
            <p class="font-medium">{{ 'Rp. '. number_format($total_price, 0, ',', '.') }}</p>
        </div>
    </div>
    <form wire:submit.prevent="process">
        <div class="mt-2 pt-5 border-t border-gray-500">
            <h2 class="text-lg font-semibold mb-4">Payment Method</h2>
            <div class="grid grid-cols-3 gap-3">
                <!-- Cash -->
                <label class="cursor-pointer">
                    <input type="radio" wire:model.live="payment" value="cash" class="peer hidden" checked>
                    <div class="flex flex-col items-center justify-center p-4 border-1 rounded-xl peer-checked:border-orange-500 peer-checked:bg-orange-50 text-gray-700 peer-checked:text-orange-600 hover:bg-gray-50">
                        <!-- Banknotes Icon -->
                        <x-icon name="o-banknotes" class="h-6 w-6 mb-1" />
                        <span class="text-sm font-medium">Cash</span>
                    </div>
                </label>
    
                <!-- Debit -->
                <label class="cursor-pointer">
                    <input type="radio" wire:model.live="payment" value="debit" class="peer hidden">
                    <div class="flex flex-col items-center justify-center p-4 border-1 rounded-xl peer-checked:border-orange-500 peer-checked:bg-orange-50 text-gray-700 peer-checked:text-orange-600 hover:bg-gray-50">
                        <!-- Credit Card Icon -->
                        <x-icon name="o-credit-card" class="h-6 w-6 mb-1" />
                        <span class="text-sm font-medium">Debit</span>
                    </div>
                </label>
    
                <!-- QRIS -->
                <label class="cursor-pointer">
                    <input type="radio" wire:model.live="payment" value="qris" class="peer hidden">
                    <div class="flex flex-col items-center justify-center p-4 border-1 rounded-xl peer-checked:border-orange-500 peer-checked:bg-orange-50 text-gray-700 peer-checked:text-orange-600 hover:bg-gray-50">
                        <!-- QR Code Icon -->
                        <x-icon name="o-qr-code" class="h-6 w-6 mb-1" />
                        <span class="text-sm font-medium">QRIS</span>
                    </div>
                </label>
            </div>
        </div>
        @if ($payment == 'cash')
            <div class="mt-2 pt-5">
                <div class="flex justify-between items-center">
                    <label for="cash" class="text-sm font-medium text-gray-900 dark:text-gray-100">Cash</label>
                    <div class="flex items-center gap-x-2">
                        <span class="text-sm font-medium text-grey-900">Rp.</span>
                        <input type="number" min="0" wire:model.live="cash" placeholder="Enter cash" class="input input-bordered w-full max-w-xs" />
                    </div>
                </div>
            </div>
            <div class="bg-base-200 mt-3 p-4 rounded-md">
                <div class="grid grid-cols-3 gap-3">
                    <x-button label="+2.000" wire:click="addCash(2000)" class="btn-outline btn-sm" />
                    <x-button label="+5.000" wire:click="addCash(5000)" class="btn-outline btn-sm" />
                    <x-button label="+10.000" wire:click="addCash(10000)" class="btn-outline btn-sm" />
                    <x-button label="+20.000" wire:click="addCash(20000)" class="btn-outline btn-sm" />
                    <x-button label="+50.000" wire:click="addCash(50000)" class="btn-outline btn-sm" />
                    <x-button label="+100.000" wire:click="addCash(100000)" class="btn-outline btn-sm" />
                </div>
            </div>
            <div class="bg-primary-content dark:bg-base-200 mt-3 p-4 rounded-md">
                <div class="flex justify-between items-center">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Change</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ 'Rp. '. number_format($change, 0, ',', '.') }}</p>
                </div>
            </div>
        @endif
        <div class="mt-5">
            <x-button label="Process" type="submit" class="btn-primary btn-block lg:btn-lg" spinner="process" />
        </div>
    </form>
    @endif
</div>


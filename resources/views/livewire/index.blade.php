<?php

use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;

new class extends Component {

    public array $areaChart = [
        'type' => 'line',
        'data' => [
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            'datasets' => [
                [
                    'label' => 'Sale amount',
                    'data' => [12, 19, 3, 5, 2, 3, 12],
                    'fill' => true,
                    'borderColor' => '#b39ddb',
                    'backgroundColor' => 'rgba(179, 157, 219, 0.3)',
                    'tension' => 0.3,
                    'pointBackgroundColor' => '#b39ddb',
                    'pointBorderColor' => '#fff',
                    'pointRadius' => 4,
                    'pointHoverRadius' => 5
                ]
            ]
        ],
        'options' => [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ]
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ]
                ],
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => false,
                    ]
                ]
            ]
        ]
    ];

    public array $pieChart = [
        'type' => 'doughnut',
        'data' => [
            'labels' => ['Ayam Goreng', 'Mie Goreng', 'Kopi Gula Aren'],
            'datasets' => [
                [
                    'label' => 'Sale amount',
                    'data' => [12, 19, 3],
                ]
            ]
        ],
        'options' => [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'left',
                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                    ]
                ],
            ]
        ]
    ];
    
    public function with(): array
    {
        return [
            'gross' => App\Models\Sale::sum('total_amount'),
            'sales' => App\Models\Sale::count(),
            'bestSellers' => App\Models\SaleDetail::with('product')
                ->select('product_id', DB::raw('sum(quantity) as total'))
                ->groupBy('product_id')
                ->orderBy('total', 'desc')
                ->take(5)
                ->get(),
            'latestOrders' => App\Models\Sale::orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
        ];
    }
}; ?>

<div class="space-y-8">
    <x-header title="Dashboard" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Filter" responsive icon="o-calendar" />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <x-stat title="Gross" value="Rp. {{ number_format($gross, 0, ',', '.') }}" icon="o-banknotes"
            color="text-success" />
        <x-stat title="Sales" value="{{ $sales }}" icon="o-gift" color="text-primary" />
        <x-stat title="Consumer" value="120" icon="o-user-plus" color="text-secondary" />
        <x-stat title="User" value="10" icon="o-user-group" color="text-accent" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-9 gap-8">
        <x-card title="Gross" class="lg:col-span-6" shadow separator>
            <x-chart wire:model="areaChart" class="w-full h-50" />
        </x-card>

        <x-card title="Category" class="lg:col-span-3" shadow separator>
            <div class="flex justify-center">
                <x-chart wire:model="pieChart" class="h-50" />
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <x-card title="Best sellers" shadow separator class="col-span-2">
            @foreach ($bestSellers as $item)
            <x-list-item :item="$item" no-separator>
                <x-slot:avatar>
                    <img src="{{ $item->product->image ? url('storage', $item->product->image) : asset('images/no_image.jpg') }}" class="w-10 h-10 rounded-md" />
                </x-slot:avatar>
                <x-slot:value>
                    {{ $item->product->name }}
                </x-slot:value>
                <x-slot:sub-value>
                    {{ $item->product->category->name }}
                </x-slot:sub-value>
                <x-slot:actions>
                    <x-badge value="{{ $item->total }}" class="badge-primary badge-soft" />
                </x-slot:actions>
            </x-list-item>
            @endforeach
        </x-card>

        <x-card title="Latest order" shadow separator class="col-span-2">
            @foreach ($latestOrders as $item)
            <x-list-item :item="$item" no-separator link="/sales/{{ $item->id }}">
                <x-slot:value>
                    {{ $item->invoice_number }}
                </x-slot:value>
                <x-slot:sub-value class="flex items-center">
                    <x-icon name="o-clock" class="me-2"/>{{ $item->created_at->diffForHumans() }}
                </x-slot:sub-value>
                <x-slot:actions>
                    <x-badge value="{{ 'Rp. ' . number_format($item->total_amount, 0, ',', '.') }}" class="badge-soft" />
                </x-slot:actions>
            </x-list-item>
            @endforeach
        </x-card>
    </div>
</div>
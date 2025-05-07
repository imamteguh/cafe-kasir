<?php

use Livewire\Volt\Component;

new class extends Component {
    
    public $categories;

    public function mount()
    {
        $this->categories = \App\Models\Category::all();
    }
}; ?>

<div>
    @foreach ($categories as $item)
    <x-menu-item title="{{ $item->name }}" icon="o-chevron-double-right" link="/category/{{ $item->id }}" />
    @endforeach
</div>

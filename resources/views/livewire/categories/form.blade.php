<?php

use App\Models\User;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use App\Models\Category;

new class extends Component {
    
    use Toast;
    
    public $categoryId;

    public string $name = '';

    public function mount($categoryId)
    {
        $this->categoryId = $categoryId;
        if ($categoryId) {
            $user = Category::find($categoryId);
            $this->name = $user->name;
        }
    }

    public function submit()
    {
        $this->validate([
            'name' => 'required',
        ]);
        if ($this->categoryId) {
            $category = Category::findOrFail($this->categoryId);
            $category->name = $this->name;
            $category->save();
            $this->success('Category updated successfully', position: 'bottom-right');
        } else {
            Category::create([
                'name' => $this->name
            ]);
            $this->success('Category created successfully', position: 'bottom-right');
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
        <x-input label="Name" placeholder="Name" wire:model="name" />
        <x-slot:actions>
            <x-button label="Cancel" wire:click="cancel" />
            <x-button label="{{ $categoryId ? 'Update' : 'Submit' }}" class="btn-primary" type="submit" spinner="save"/>
        </x-slot:actions>
    </x-form>
</div>
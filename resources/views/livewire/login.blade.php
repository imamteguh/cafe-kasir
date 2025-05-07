<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new
#[Layout('components.layouts.empty')]
#[Title('Login')]
class extends Component {

    use Toast;
 
    #[Rule('required')]
    public string $username = '';
 
    #[Rule('required')]
    public string $password = '';
 
    public function mount()
    {
        if (Auth::user() && Auth::user()->role == 'admin') {
            return redirect('/dashboard');
        } elseif (Auth::user()) {
            return redirect('/');
        }
    }
 
    public function login()
    {
        $credentials = $this->validate();
        if (Auth::attempt($credentials)) {            
            request()->session()->regenerate();
            $user = Auth::user();
            if ($user->role == 'admin') {
                $redirect = redirect()->route('dashboard');
            } else {
                $redirect = redirect('/');
            }
            return $redirect;
        }
        $this->addError('username', 'The provided credentials do not match our records.');
    }
}; ?>

<div class="md:w-96 mx-auto mt-20">
    <div class="mb-10">
        <x-app-brand />
    </div>
 
    <x-form wire:submit="login">
        <x-input placeholder="Username" wire:model="username" icon="o-user" />
        <x-input placeholder="Password" wire:model="password" type="password" icon="o-key" />
 
        <x-slot:actions>
            <x-button label="Login" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="login" />
        </x-slot:actions>
    </x-form>
</div>

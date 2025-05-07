<?php

use App\Models\User;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    
    use Toast;
    
    public $userId;

    public string $name = '';
    public string $username = '';
    public string $password = '';
    public string $role = '';

    public array $roles = [
        ['id' => 'admin', 'name' => 'Admin'],
        ['id' => 'kasir', 'name' => 'Kasir']
    ];

    public function mount($userId)
    {
        $this->userId = $userId;
        if ($userId) {
            $user = User::find($userId);
            $this->name = $user->name;
            $this->username = $user->username;
            $this->role = $user->role;
        }
    }

    public function submit()
    {
        if ($this->userId) {
            $user = User::findOrFail($this->userId);
            $this->validate([
                'name' => 'required',
                'username' => 'required|unique:users,username,' . $this->userId,
                'password' => 'nullable|min:8',
                'role' => 'required',
            ]);
            $user->name = $this->name;
            $user->username = $this->username;
            $user->role = $this->role;
            if (!empty($this->password)) {
                $user->password = bcrypt($this->password);
            }
            $user->save();
            $this->success('User updated successfully', position: 'bottom-right');
        } else {
            $this->validate([
                'name' => 'required',
                'username' => 'required|unique:users,username',
                'password' => 'required|min:8',
                'role' => 'required',
            ]);
            $user = User::create([
                'name' => $this->name,
                'username' => $this->username,
                'password' => bcrypt($this->password),
                'role' => $this->role,
            ]);
            $this->success('User created successfully', position: 'bottom-right');
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
        <x-input label="Username" placeholder="Username" wire:model="username" />
        <x-password label="Password" placeholder="Password" right wire:model="password" />
        <x-radio label="Role" :options="$roles" wire:model="role" inline/>
        <x-slot:actions>
            <x-button label="Cancel" wire:click="cancel" />
            <x-button label="{{ $userId ? 'Update' : 'Submit' }}" class="btn-primary" type="submit" spinner="save"/>
        </x-slot:actions>
    </x-form>
</div>

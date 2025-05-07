<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen font-sans antialiased bg-base-200">

    {{-- NAVBAR --}}
    <x-nav sticky full-width>
        <x-slot:brand>
            <label for="main-drawer" class="lg:hidden mr-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
            <x-app-brand />
        </x-slot:brand>

        <x-slot:actions>
            <x-button label="Messages" icon="o-envelope" link="###" class="btn-ghost btn-sm" responsive />
            <x-button label="Notifications" icon="o-bell" link="###" class="btn-ghost btn-sm" responsive />
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main with-nav full-width>
        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100">

            {{-- MENU --}}
            <x-menu activate-by-route>

                {{-- User --}}
                @if($user = auth()->user())
                <x-menu-separator />
                @php
                    $avatar = Avatar::create(strtoupper(auth()->user()->name))->toBase64();
                @endphp
                <x-list-item :item="$user" value="name" sub-value="username" no-separator no-hover class="-mx-2 !-my-2 rounded">
                    <x-slot:avatar>
                        <x-avatar :image="$avatar" class="!w-10" />
                    </x-slot:avatar>
                    <x-slot:actions>
                        <x-dropdown>
                            <x-slot:trigger>
                                <x-button icon="o-cog-6-tooth" class="btn-circle" />
                            </x-slot:trigger>
                            <x-menu-item title="Logout" icon="o-power" no-wire-navigate link="/logout"/>
                            <x-menu-item title="Switch Theme" icon="o-swatch" @click="$dispatch('mary-toggle-theme')" />
                            <x-theme-toggle class="hidden"/>
                        </x-dropdown>
                    </x-slot:actions>
                </x-list-item>
                <x-menu-separator />
                @endif

                {{-- Sidemenu --}}
                @livewire('partials.sidemenu')
            </x-menu>

        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>

    </x-main>

    {{--  TOAST area --}}
    <x-toast />

    {{-- Cropper.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    @stack('scripts')
    @livewireScripts
</body>
</html>

<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component {
    public bool $isOpen = false;
    public bool $isDashboard;
    public ?User $user;

    public function toggleMenu()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function closeMenu()
    {
        $this->isOpen = false;
    }

    public function mount()
    {
        $this->user = auth()->user();
        $this->isDashboard = request()->routeIs('dashboard.*');
    }
};
?>

<div>
    <!-- Pulsante hamburger -->
    <div class="cursor-pointer z-50 relative w-10 h-10" wire:click="toggleMenu">
        <img src="/images/hamburger-menu.png" alt=""
            class="absolute top-1 left-0 w-11 transition-opacity duration-200 ease-in-out {{ $isOpen ? 'opacity-0' : 'opacity-100' }}">
        <img src="/images/hamburger-menu-closed.png" alt=""
            class="absolute top-1 left-0 w-8 transition-opacity duration-200 ease-in-out {{ $isOpen ? 'opacity-100' : 'opacity-0' }}">
    </div>

    @if ($isOpen)
        <!-- Menu mobile -->
        <div class="fixed top-[72px] left-0 w-full h-[calc(100vh-72px)] bg-[#2B2B2B] flex flex-col z-40 p-6">
            @if (auth()->check())
                <div class="flex flex-col space-y-2 mt-6 border-b-1 border-stone-600 pb-3">
                    <img src="{{ $user->avatar_img }}" alt="" class="rounded-full w-10 h-10">
                    <span class="user-info mt-1">{{ $user->name }}</span>
                </div>
            @endif
            @if (!$isDashboard)
                <ul class="flex flex-col mt-6 space-y-2">
                    <li>
                        <x-menu-item icon="home-white" label="home" url="/" />
                    </li>
                    @if (auth()->check())
                        <li>
                            <x-menu-item icon="dashboard-white" label="dashboard"
                                url="{{ route('dashboard.index') }}" />
                        </li>
                        @if (auth()->user()->has_active_community)
                            <li>
                                <x-menu-item icon="users-white" label="community"
                                    url="{{ route('dashboard.communities.index') }}" />
                            </li>
                            <li>
                                <x-menu-item icon="calendar-white" label="i miei eventi"
                                    url="{{ route('dashboard.communities.events') }}" />
                            </li>
                            <li>
                                <x-menu-item icon="plus-white" label="nuovo evento"
                                    url="{{ route('dashboard.events.create') }}" />
                            </li>
                        @endif
                    @endif
                    <li>
                        <x-menu-item icon="heart-white" label="preferiti"
                            url="{{ auth()->check() ? route('dashboard.bookmarks') : route('login') }}" />
                    </li>
                </ul>
            @else
                <ul class="flex flex-col space-y-2 mt-6">
                    <li>
                        <x-menu-item icon="home-white" label="home" url="/" />
                    </li>
                    <li>
                        <x-menu-item icon="dashboard-white" label="dashboard" url="{{ route('dashboard.index') }}" />
                    </li>
                    <li>
                        <x-menu-item icon="heart-white" label="preferiti"
                            url="{{ auth()->check() ? route('dashboard.bookmarks') : route('login') }}" />
                    </li>
                    <li>
                        <x-menu-item icon="users-white" label="community"
                            url="{{ route('dashboard.communities.index') }}" />
                    </li>
                    <li>
                        <x-menu-item icon="calendar-white" label="i miei eventi"
                            url="{{ route('dashboard.communities.events') }}" />
                    </li>
                    <li>
                        <x-menu-item icon="plus-white" label="nuovo evento"
                            url="{{ route('dashboard.events.create') }}" />
                    </li>
                </ul>
            @endif
            <!-- Menu per utenti autenticati -->
            @if (auth()->check())
                <div>
                    <ul class="mt-9">
                        <li class="flex space-x-2 items-center">
                            {{-- <img src="{{ $user->avatar_img }}" alt="" class="rounded-full w-7 h-7">
                            <span class="user-info text-pink">
                                {{ $user->name }}
                            </span> --}}
                            <form method="POST" action="/logout">
                                @csrf
                                <button class="flex space-x-2 items-center">
                                    <img src="/icons/logout-2-white.svg" alt="" class="!w-4">
                                    <span>esci</span>
                                </button>

                            </form>
                        </li>

                    </ul>
                </div>
            @else
                <!-- Menu per utenti non autenticati -->
                <div>
                    <ul class="mt-9">
                        <li class="mt-2">
                            <x-menu-item icon="login-white" label="login" url="/login" />
                        </li>
                        <li class="mt-2">
                            <x-menu-item icon="register-white" label="registrati" url="/register" />
                        </li>
                    </ul>
                </div>
            @endif

        </div>
    @endif
</div>

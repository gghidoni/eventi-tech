<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-background antialiased">
    <nav class="bg-background text-lg md:px-6 py-6 fixed top-0 left-0 w-full z-50">
        <div class="container mx-auto flex items-center justify-between px-6">
            <a href="/">
                <img src="/images/logo.png" alt="Logo" class="w-24">
            </a>
            <livewire:hamburger-menu isDashboard="{{$isDashboard}}" />
        </div>
    </nav>

    <section class="main-content pt-[72px]">
        <livewire:messages />
        {{ $slot }}
    </section>

    @fluxScripts
    @stack('scripts')
</body>

</html>

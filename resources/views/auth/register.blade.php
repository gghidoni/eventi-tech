<x-layouts.base :title="__('Register')">
    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h2 class="mt-10 text-center text-2xl/9 font-bold">Registrati</h2>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm" x-data="{ showPassword: false, showConfirm: false }">

            {{-- Mostra errori generali --}}
            @if (session('error'))
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
                @csrf

                {{-- Nome --}}
                <div>
                    <label for="name" class="block text-sm font-medium mb-1">Nome</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" class="input-et"
                        required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium mb-1">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" class="input-et"
                        required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium mb-1">Password</label>
                    <div class="relative">
                        <input id="password" name="password" :type="showPassword ? 'text' : 'password'"
                            class="input-et" required>
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500"
                            @click="showPassword = !showPassword"
                            :aria-label="showPassword ? 'Nascondi password' : 'Mostra password'">
                            <img x-show="!showPassword" src="/icons/eye-cyan.svg" alt="" class="!w-5">
                            <img x-show="showPassword" src="/icons/no-eye-cyan.svg" alt="" class="!w-5">
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Conferma Password --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium mb-1">Conferma Password</label>
                    <div class="relative">
                        <input id="password_confirmation" name="password_confirmation"
                            :type="showConfirm ? 'text' : 'password'" class="input-et" required>
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500"
                            @click="showConfirm = !showConfirm"
                            :aria-label="showConfirm ? 'Nascondi password' : 'Mostra password'">
                            <img x-show="!showConfirm" src="/icons/eye-cyan.svg" alt="" class="!w-5">
                            <img x-show="showConfirm" src="/icons/no-eye-cyan.svg" alt="" class="!w-5">
                        </button>
                    </div>
                    @error('password_confirmation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <div class="mt-10">
                    <button type="submit"
                        class="w-full flex items-center justify-center bg-accent text-background px-4 py-2 rounded-md hover:bg-accent/90 mt-4">
                        registrati
                        <img class="ml-3 w-3" src="/icons/right-black.svg" alt="">
                    </button>
                </div>
            </form>

            {{-- Link a login --}}
            <p class="mt-4 text-center text-sm text-gray-600">
                Hai già un account?
                <a href="/login" class="text-accent hover:underline">accedi</a>
            </p>

        </div>
    </div>
</x-layouts.base>

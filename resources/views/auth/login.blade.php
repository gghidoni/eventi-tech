<x-layouts.base :title="__('Login')" isDashboard="false">
    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h2 class="mt-10 text-center text-2xl/9 font-bold">Accedi</h2>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                        class="input-et" />
                    @error('email')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                        class="input-et" />
                    @error('password')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" class="rounded border-gray-300" />
                    <label for="remember" class="ml-2 text-sm">Ricordami</label>
                </div>

                <button type="submit"
                    class="w-full flex items-center justify-center bg-accent text-background px-4 py-2 rounded-md hover:bg-accent/90 mt-8">
                    login
                    <img class="ml-3 w-3" src="/icons/right-black.svg" alt="">
                </button>
            </form>

            {{-- Link a login --}}
            <p class="mt-4 text-center text-sm text-gray-600">
                Non sei ancora registrato?
                <a href="/register" class="text-accent hover:underline">registrati</a>
            </p>

            {{-- <div class="mt-4 text-center">
                <a href="{{ route('password.request') }}" class="text-sm text-accent hover:underline">
                    Password dimenticata?
                </a>
            </div> --}}

        </div>
    </div>
</x-layouts.base>

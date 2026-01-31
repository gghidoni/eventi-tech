<x-layouts.base :title="__('auth.forgot_password.title')">
    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h2 class="mt-10 text-center text-2xl/9 font-bold">{{ __('auth.forgot_password.title') }}</h2>
            <p class="mt-2 text-center text-sm text-gray-400">{{ __('auth.forgot_password.intro') }}</p>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">

            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded text-sm">
                    {{ __('auth.forgot_password.sent') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium mb-1">{{ __('auth.fields.email') }}</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="input-et" required autofocus />
                    @error('email')
                        <span class="text-pink text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full flex items-center justify-center bg-accent text-background px-4 py-2 rounded-md hover:bg-accent/90 mt-8">
                    {{ __('auth.forgot_password.button') }}
                </button>
            </form>

            <p class="mt-4 text-center text-sm text-gray-600">
                <a href="{{ route('login') }}" class="text-accent hover:underline">{{ __('auth.forgot_password.back_to_login') }}</a>
            </p>
        </div>
    </div>
</x-layouts.base>

<x-layouts.base :title="__('auth.reset_password.title')">
    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h2 class="mt-10 text-center text-2xl/9 font-bold">{{ __('auth.reset_password.title') }}</h2>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf

                <input type="hidden" name="token" value="{{ request()->route('token') }}">

                <div>
                    <label for="email" class="block text-sm font-medium mb-1">{{ __('auth.fields.email') }}</label>
                    <input type="email" id="email" name="email" value="{{ old('email', request()->query('email')) }}" class="input-et" required autofocus />
                    @error('email')
                        <span class="text-pink text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium mb-1">{{ __('auth.reset_password.new_password') }}</label>
                    <input type="password" id="password" name="password" class="input-et" required />
                    @error('password')
                        <span class="text-pink text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium mb-1">{{ __('auth.reset_password.confirm_password') }}</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="input-et" required />
                </div>

                <button type="submit"
                    class="w-full flex items-center justify-center bg-accent text-background px-4 py-2 rounded-md hover:bg-accent/90 mt-8">
                    {{ __('auth.reset_password.button') }}
                </button>
            </form>
        </div>
    </div>
</x-layouts.base>

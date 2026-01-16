<x-layouts.base :title="__('Confirm Password')">
    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h2 class="mt-10 text-center text-2xl/9 font-bold">{{ __('Confirm Password') }}</h2>
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

            <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="password" class="block text-sm font-medium mb-1">{{ __('Password') }}</label>
                    <input type="password" id="password" name="password" required
                        class="input-et" />
                    @error('password')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full flex items-center justify-center bg-accent text-background px-4 py-2 rounded-md hover:bg-accent/90 mt-8">
                    {{ __('Confirm') }}
                    <img class="ml-3 w-3" src="/icons/right-black.svg" alt="">
                </button>
            </form>
        </div>
    </div>
</x-layouts.base>
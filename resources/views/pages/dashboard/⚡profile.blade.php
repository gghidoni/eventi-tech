<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use App\Actions\ProcessAvatar;
use App\Actions\Fortify\UpdateUserPassword;

new class extends Component {
    use WithFileUploads;

    #[Validate(['required', 'string', 'max:255', 'min:2'])]
    public string $name = '';

    #[Validate(['nullable', 'image', 'max:1024'])]
    public $avatar;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount()
    {
        $this->name = auth()->user()->name;
    }

    public function rendering($view)
    {
        $view->layout('components.layouts.base', ['title' => __('profile.title')]);
    }

    public function saveProfile(ProcessAvatar $processAvatar)
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'min:2'],
            'avatar' => ['nullable', 'image', 'max:1024'],
        ]);

        try {
            $user = auth()->user();
            $user->name = $this->name;

            if ($this->avatar) {
                $user->avatar = $processAvatar->execute($this->avatar);
            }

            $user->save();

            return redirect()->route('dashboard.profile')->with('success', __('profile.success_profile'));
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            $this->dispatch('messageSent', message: __('profile.error_profile'), success: false);
        }
    }

    public function savePassword(UpdateUserPassword $updateUserPassword)
    {
        try {
            $updateUserPassword->update(auth()->user(), [
                'current_password' => $this->current_password,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
            ]);

            $this->reset(['current_password', 'password', 'password_confirmation']);

            return redirect()->route('dashboard.profile')->with('success', __('profile.success_password'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }
        }
    }
}; ?>

<div class="page">
    <h1 class="text-xl">{{ __('profile.heading') }}</h1>

    {{-- Sezione Profilo --}}
    <form wire:submit="saveProfile" class="mt-5">
        <h2 class="text-lg text-cyan mb-3">{{ __('profile.section_profile') }}</h2>

        {{-- Nome --}}
        <div class="mb-3">
            <label for="name" class="block text-sm font-medium mb-1 text-gray-500">{{ __('profile.fields.name') }}</label>
            <input type="text" id="name" class="input-et" wire:model="name" />
            @error('name')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- Avatar con Anteprima --}}
        <div class="mb-3">
            <label for="avatar" class="block text-sm font-medium mb-[-5px] text-gray-500">{{ __('profile.fields.avatar') }}</label>

            <div class="flex items-center space-x-4">
                <div class="flex-1">
                    <label for="avatar"
                        class="input-et flex items-center justify-center cursor-pointer hover:border-gray-400 transition-colors">
                        <span class="text-gray-400">
                            {{ $avatar ? __('profile.fields.change_image') : __('profile.fields.select_file') }}
                        </span>
                        <input type="file" id="avatar" wire:model="avatar" class="hidden" accept="image/*" />
                    </label>
                </div>

                <div class="shrink-0">
                    @if ($avatar)
                        <img src="{{ $avatar->temporaryUrl() }}"
                            class="size-16 rounded-full object-cover border border-gray-600">
                    @else
                        <img src="{{ auth()->user()->avatar_img }}"
                            class="size-16 rounded-full object-cover border border-gray-600">
                    @endif
                </div>
            </div>

            <div wire:loading wire:target="avatar" class="text-xs text-cyan mt-1">
                {{ __('profile.fields.uploading') }}
            </div>

            @error('avatar')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="flex items-center space-x-2 text-cyan underline mt-6">
            <span>{{ __('profile.actions.save_profile') }}</span>
            <img src="/icons/right-cyan.svg" alt="">
        </button>
    </form>

    {{-- Sezione Password --}}
    <form wire:submit="savePassword" class="mt-10 border-t border-stone-600 pt-6">
        <h2 class="text-lg text-cyan mb-3">{{ __('profile.section_password') }}</h2>

        <div class="mb-3">
            <label for="current_password" class="block text-sm font-medium mb-1 text-gray-500">{{ __('profile.fields.current_password') }}</label>
            <input type="password" id="current_password" class="input-et" wire:model="current_password" />
            @error('current_password')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="block text-sm font-medium mb-1 text-gray-500">{{ __('profile.fields.new_password') }}</label>
            <input type="password" id="password" class="input-et" wire:model="password" />
            @error('password')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="block text-sm font-medium mb-1 text-gray-500">{{ __('profile.fields.confirm_password') }}</label>
            <input type="password" id="password_confirmation" class="input-et" wire:model="password_confirmation" />
            @error('password_confirmation')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="flex items-center space-x-2 text-cyan underline mt-6">
            <span>{{ __('profile.actions.save_password') }}</span>
            <img src="/icons/right-cyan.svg" alt="">
        </button>
    </form>
</div>

<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Profile Picture and Basic Info -->
        <div class="">
            <!-- Profile Picture Section -->
            <div class="bg-white rounded-lg shadow p-6 dark:bg-gray-800">
                <div class="flex flex-col items-center">
                    @if($data['image'] ?? false)
                        <img src="{{ $user->image ? asset('storage/' . $user->image) : asset('images/default-user.png') }}"
                             class="w-32 h-32 rounded-full object-cover mb-4"
                             alt="Profile Picture">
                    @else
                        <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center mb-4">
                            <svg class="w-16 h-16 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    @endif

                    {{ $this->form->getComponent('image') }}

                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mt-2">
                        {{ $data['name'] ?? '' }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        NPM. {{ $data['srn'] ?? '' }}
                    </p>
                   <p class="text-gray-600 dark:text-gray-400">
                        {{ $user->prody->name ?? '' }} - {{ $data['year'] ?? ' ' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Password Section -->
        <div class="md:col-span-2 bg-white rounded-lg shadow p-6 dark:bg-gray-800 space-y-4">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Edit Infomasi Akun Anda</h3>

            <div class="">
                {{ $this->form }}
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit" wire:click="edit"
                    class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                Simpan Perubahan
            </button>
        </div>
    </div>
</x-filament-panels::page>
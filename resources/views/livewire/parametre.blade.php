<div class="flex items-center justify-center h-full bg-gray-100">
    <div class="w-full max-w-md p-6 bg-white rounded-2xl shadow-lg">
        <h2 class="text-2xl font-bold text-center mb-6">Changer le mot de passe</h2>

        @if (session('success'))
            <div class="mb-4 p-3 text-green-700 bg-green-100 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <form wire:submit.prevent="updatePassword" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                <input type="password" wire:model="password"
                       class="w-full mt-1 p-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-300" required>
                @error('password') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
                <input type="password" wire:model="password_confirmation"
                       class="w-full mt-1 p-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-300" required>
            </div>

            <button type="submit"
                    class="w-full py-2 px-4 bg-[#77BC23] text-white font-semibold rounded-lg hover:bg-green-600 transition cursor-pointer">
                Mettre à jour
            </button>
        </form>
    </div>
</div>

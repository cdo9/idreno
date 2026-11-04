<div class="flex items-center justify-center min-h-[60vh]">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-8 sm:p-10 mx-auto">
        <h1 class="text-3xl font-bold text-primary mb-8 text-center">Connexion</h1>

        @if(session('success'))
            <div class="mb-4 text-green-600 text-center">{{ session('success') }}</div>
        @endif

        {{-- Livewire form --}}
        <form wire:submit.prevent="authenticate" class="flex flex-col gap-6">
            <div class="flex flex-col gap-2">
                <label for="email" class="text-primary font-bold">Email</label>
                <input type="email" id="email" wire:model.defer="email" required
                       class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] text-[#7A7A7A] focus:outline-none focus:ring-2 focus:ring-[#77BC23] transition" />
                @error('email') <div class="text-red-500 text-sm">{{ $message }}</div> @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="password" class="text-primary font-bold">Mot de passe</label>
                <input type="password" id="password" wire:model.defer="password" required
                       class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] text-[#7A7A7A] focus:outline-none focus:ring-2 focus:ring-[#77BC23] transition" />
                @error('password') <div class="text-red-500 text-sm">{{ $message }}</div> @enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="remember" wire:model="remember"
                       class="mr-2 rounded border-[#DCDADA] focus:ring-[#77BC23]" />
                <label for="remember" class="text-[#7A7A7A]">Se souvenir de moi</label>
            </div>

            <button type="submit"
                    class="cursor-pointer bg-lightprimary text-white font-bold py-3 px-8 rounded-full hover:bg-primary transition duration-300 shadow-md w-full">
                Se connecter
            </button>
        </form>
    </div>
</div>

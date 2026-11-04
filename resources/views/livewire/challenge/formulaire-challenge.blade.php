<div>
    <h1 class="text-2xl font-bold text-primary text-center mb-6">Modification Challenge</h1>
<div class="max-w-lg mx-auto bg-white shadow-sm rounded-md border border-gray-200 p-6 mt-6">
    <form wire:submit.prevent="save" class="space-y-6">
        <div>
            <label for="challenge" class="text-xl font-bold text-primary py-2">Challenge :</label>
            <input type="text" id="challenge" wire:model="challenge"
                   class="border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey w-full"
                   placeholder="Description du challenge">
            @error('challenge') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="date" class="text-xl font-bold text-primary py-2">Date :</label>
            <input type="date" id="date" wire:model="date"
                   class="border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey w-full">
            @error('date') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="flex justify-around">
            <button type="submit"
                    class="bg-lightprimary  cursor-pointer hover:bg-primary text-white font-semibold rounded-md px-4 py-2 transition-colors duration-200">
                Modifier
            </button>
            <a href="#" wire:click="cancel"
               class="bg-grey cursor-pointer hover:bg-darkgrey text-white font-semibold rounded-md px-4 py-2 transition-colors duration-200">
                Annuler
            </a>
        </div>
    </form>
</div>
</div>

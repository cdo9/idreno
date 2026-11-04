@props(['message' => ''])

<div wire:loading class="fixed inset-0 bg-opacity-25 flex items-center justify-center z-40">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
        <span class="text-gray-700">{{ $message ?: 'Chargement...' }}</span>
    </div>
</div>

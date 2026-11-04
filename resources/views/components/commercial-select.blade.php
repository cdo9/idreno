@props([
    'commerciaux' => [],
    'selected' => null,
    'wireModel' => null,
    'id' => '',
    'label' => 'Commercial',
    'showLabel' => true,
    'showStatus' => true,
    'showLegend' => true,
    'showDeactivated' => true,
    'hide'  => false,
    'required' => false,
    'placeholder' => '-- Sélectionner un commercial --',
    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md',
])

@php
    if($hide)
        $class .= " hidden";
@endphp

@if ($showLabel && !$hide)
    <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 mb-2">
        {{ $label }}
        @if ($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
@endif

<select id="{{ $id }}" @if ($wireModel) wire:model.live="{{ $wireModel }}" @endif
    @if ($required) required @endif {{ $attributes->merge(['class' => $class]) }}>
    @if ($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif

    @foreach ($commerciaux as $commercial)
        @if(($commercial->active == 0 && $showDeactivated) || $commercial->active == 1)
            <option @class([
                'font-bold' => $commercial->id == $selected,
                'bg-red-100' => !$commercial->active && $showStatus,
                'text-gray-500' => !$commercial->active && $showStatus,
            ]) value="{{ $commercial->id }}"
                @if ($commercial->id == $selected) selected @endif>
                {{ $commercial->display_name }}
                @if ($showStatus)
                    - {{ $commercial->active ? 'Actif' : 'Inactif' }}
                @endif
            </option>
        @endif
    @endforeach
</select>

@if ($showLegend && !$hide)
    <p class="mt-1 text-xs text-gray-500">
        <i class="fa-solid fa-info-circle mr-1"></i>
        Les commerciaux inactifs ont un fond rouge clair.
    </p>
@endif

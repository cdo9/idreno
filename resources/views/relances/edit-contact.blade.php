@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center min-h-screen bg-background-grey gap-8 pb-10">
    <livewire:edit-contact :clientId="$clientId" />
</div>
@endsection

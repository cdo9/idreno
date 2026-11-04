@extends('layouts.app')

@section('content')
    <livewire:rendez-vous-form :rendez-vous-id="$rendezVous->id" :is-create="false" />
@endsection

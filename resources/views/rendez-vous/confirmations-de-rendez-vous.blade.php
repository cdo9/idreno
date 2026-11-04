@extends('layouts.app')
@section('content')

<livewire:rendez-vous-listing :day="request('day', 'today')" />

@endsection
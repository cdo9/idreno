@extends('layouts.app')

@section('content')
    <livewire:nouvelle-pose :poseId="$id ?? null" />
@endsection

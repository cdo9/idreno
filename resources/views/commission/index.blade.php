@extends('layouts.app')
@section('content')

    <livewire:ventes-commissions.commissions
        :commercial="$commercial"
        :mois="$mois"
        :annee="$annee"
    />

@endsection

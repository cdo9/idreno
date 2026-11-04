@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Détails du client</div>

                <div class="card-body">
                    <h2>{{ $client->nom }} {{ $client->prenom }}</h2>

                    <div class="row">
                        <div class="col-md-6">
                            <h4>Informations personnelles</h4>
                            <p><strong>Adresse:</strong> {{ $client->adresse }}</p>
                            <p><strong>Code postal:</strong> {{ $client->cp }}</p>
                            <p><strong>Ville:</strong> {{ $client->ville }}</p>
                            <p><strong>Téléphone:</strong> {{ $client->telephone }}</p>
                            <p><strong>Portable:</strong> {{ $client->portable }}</p>
                            <p><strong>Email:</strong> {{ $client->mail }}</p>
                        </div>

                        <div class="col-md-6">
                            <h4>Informations commerciales</h4>
                            <p><strong>Type:</strong>
                                @switch($client->type)
                                    @case(0)
                                        Précontact
                                        @break
                                    @case(1)
                                        Prospect
                                        @break
                                    @case(2)
                                        Client
                                        @break
                                    @case(3)
                                        Client (annulé)
                                        @break
                                    @case(5)
                                        En pose / posé
                                        @break
                                    @case(6)
                                        Refus
                                        @break
                                    @case(7)
                                        Absent
                                        @break
                                    @case(8)
                                        Relance
                                        @break
                                    @default
                                        Inconnu
                                @endswitch
                            </p>
                            <p><strong>Source:</strong> {{ $client->source }}</p>
                            <p><strong>Projet:</strong> {{ $client->projet }}</p>
                            <p><strong>Commercial:</strong> {{ $commercial ? $commercial->prenom . ' ' . $commercial->nom : 'Inconnu' }}</p>
                            <p><strong>Ajouté le:</strong> {{ $client->rentre_le ? $client->rentre_le->format('d/m/Y') : 'Inconnue' }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('map') }}" class="btn btn-primary">Retour à la carte</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<div class="flex flex-col items-center  min-h-screen bg-background-grey gap-8 pb-10">
    <div class="flex flex-col gap-6 p-10 bg-background rounded-xl w-full max-w-4xl shadow-md">
        <h1 class="text-2xl font-bold text-primary mb-4">Anniversaires</h1>

        @if (count($clients))
            <div class="grid grid-cols-1 gap-4">
                @foreach ($clients as $client)
                    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow transition-shadow">
                        <a href="/nouveau-contact?client_id=/{{$client->id}}">
                        <div class="flex justify-between items-center">
                            <div>
                                <!-- Client Name -->
                                <h2 class="text-lg font-semibold text-gray-800">
                                    {{ $client->nom }} {{ $client->prenom }}
                                </h2>
                                <!-- Client Adress-->
                                <p class="text-sm text-gray-500">
                                    {{ $client->adresse }} — {{ $client->ville }}
                                </p>
                                <!-- Birthday date-->
                                <p class="text-sm text-gray-400 mt-1">
                                    📅  {{ \Carbon\Carbon::parse($client->date_anniversaire)->format('d/m') }}
                                </p>
                            </div>
                            <!-- Phone number of the client -->
                            <div class="text-sm text-gray-500 text-right">
                                @if($client->telephone)
                                    <div>Tél 1: {{ $client->telephone }}</div>
                                @endif
                                @if($client->telephone2)
                                    <div>Tél 2 : {{ $client->portable }}</div>
                                @endif
                            </div>
                        </div>
                    </a>
                    </div>
                @endforeach
            </div>
            <!-- No Birthday -->
        @else
            <p class="text-center text-gray-500">Aucun anniversaire aujourd'hui</p>
        @endif
    </div>
</div>

@extends('layouts.app')
@section('content')
    <style>
        .pac-container {
            z-index: 10000;
            width: auto !important;
            position: absolute !important;
            font-family: inherit;
        }
        .pac-item {
            padding: 8px;
            font-size: 14px;
        }
        .pac-item:hover {
            background-color: #f0f0f0;
        }
        
        .filters-container {
            padding: 10px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        .control-ui {
            border-radius: 3px;
            cursor: pointer;
            margin-top: 8px;
            margin-bottom: 22px;
            text-align: center;
            padding: 0;
            display: inline-block;
        }

        .control-location-ui-button,.control-ui-button {
            display: inline-block;
            color: rgb(25, 25, 25);
            box-shadow: 0 1px 2px rgba(0, 0, 0, .1);
            font-family: Roboto, Arial, sans-serif;
            font-size: 16px;
            line-height: 38px;
            padding-left: 15px;
            padding-right: 15px;
            font-weight: bold;
            user-select: none;
            border-radius: 2px;
            cursor: pointer;
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .control-ui-button-date {
            border : 1px solid;
            border-color : grey;
        }

        .control-location-ui-button,.control-ui-button:hover {
            background-color: #eaeaea;
        }

        .control-location-ui-button.disabled,.control-ui-button.disabled {
            background-color : lightgray !important;
            font-weight: normal;
        }

        a:focus-visible {
            outline: none;
        }

        /* Loading spinner styles */
        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        .loading-text {
            margin-top: 10px;
            text-align: center;
            font-family: Roboto, Arial, sans-serif;
            font-size: 14px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-container {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            background: rgba(255,255,255,0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            text-align: center;
        }

        .retry-btn { 
            background: #4285F4; color: white; padding: 14px 28px; 
            border: none; border-radius: 8px; font-size: 16px; font-weight: 600; 
            cursor: pointer; box-shadow: 0 4px 12px rgba(66,133,244,0.3);
        }
        .retry-btn:hover { background: #3367D6; }

    </style>
    <!-- Filters container -->
    <div onclick="toggleCollapse()"  class="cursor-pointer flex items-center h-12 justify-center w-full bg-gray-600 text-white">
        <span id="toggleFilterText"> ▼ Filtres ▼ </span>
    </div>
    <div id="filters-container" class="filters-container hidden">
        <!-- Filters will be placed here by JavaScript -->
    </div>

    <div class="loading-container" id="map-loading">
        <div class="loading-spinner"></div>
        <div class="loading-text">Vérification de votre position GPS...</div>
    </div>

    <div id="map" style="height: 100%; width: 100%; margin: auto"></div>

    <input id="pac-input"
        class="controls"
        type="text"
        placeholder="Rechercher...">

    <div style="display: none">
        @include('map.map-contact-form', [
            'sources' => $sources,
            'priorities' => $priorities
        ])
    </div>    

    <span style="display: none" id="commerciauxSelect">
        <select name="commercial" id="commercial">
            <option value="0">Tous les commerciaux</option>
            @foreach ($commercials as $commercial)
                <option value="{{ $commercial->id }}" {{ $commercial->id == $selectedCommercial ? 'selected' : '' }}>
                    {{ $commercial->display_name }}
                </option>
            @endforeach
        </select>
    </span>

    <span style="display: none" id="prioritiesSelect">
        <select name="priority" id="priority">
            <option value="">Tous les projets</option>
            @foreach ($priorities as $categorie => $priorites)
                <optgroup label="{{ $categorie }}">
                    @foreach ($priorites as $slug => $priorite)
                        <option value="{{ $priorite }}" {{ $priorite == $selectedPriority ? 'selected' : '' }}>
                        {{ $priorite }}
                    </option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
    </span>

    <span style="display: none" >
        <input type="date"  id="date1" name="date1" value="<?= $_GET['date1']??null ?>">
    </span>
    <span style="display: none" >
        <input type="date" id="date2" name="date2" value="<?= $_GET['date2']??null ?>">
    </span>
    <span style="display: none" >
        <input type="text" id="clientId" style="display: none" name="clientId">
    </span>

    <script>
        let map = null;
        let markers = [];
        let infowindow = null;
        let userMarker = null;
        
        let originalFilterState = null;
        let isMarkerClicked = false;
        let markerClicked = null;

        const defaultPref = {
            mapType: 'hybrid',
            mapPos: null,
            mapZoom: null,
            showColors: {
                0: false,
                1: true,
                2: false,
                3: false,
                5: false,
                6: true,
                7: true,
                8: true,
                9: true,
                99: false,
            },
        };

        let userPref = JSON.parse(localStorage.getItem('userPref')) || defaultPref;
        userPref.mapType ='hybrid';
        saveUserPref();

        const API_POINTS_URL = "{{ route('map.points') }}";

        // Rouen
        //let initialLocation = { lat: 49.44, lng: 1.10 };
        const initialPosition = {!! json_encode(["lat" => 49.44, "lng" => 1.10]) !!};

        // https://kml4earth.appspot.com/icons.html
        const iconsUrl = "https://maps.google.com/mapfiles/kml/paddle/";
        const colors = {
            //4: 'purple', // autre (refus rdv, absent...)
            0: {img:'blu-circle.png', hexa:"#5680FC"},   // précontact
            1: {img:'grn-circle.png', hexa:"#5BFD7D"},  // prospect
            2: {img:'ylw-circle.png', hexa:"#FCF358"}, // client
            3: {img:'orange-circle.png', hexa:"#FFC45A"}, // client (annulé)
            5: {img:'ylw-circle.png', hexa:"#FCF358"},    // en pose / posé
            6: {img:'red-circle.png', hexa:"#FC6457"}, // Autre : refus
            7: {img:'ltblu-circle.png', hexa:"#8EFFEA"}, // Autre : absent
            8: {img:'purple-circle.png', hexa:"#7D54FB"}, // Autre : relance
            9: {img:'pink-circle.png', hexa:"#FC669E"}, // Orphelin
            99: {img:'cab', hexa:""},    // commerciaux
            98: {img:'wht-blank.png', hexa:""}, // new pin
            // ltblue
            // pink
        };

        /*
        Refus    => Autre
        Absent   => Autre
        Relance  => Précontact
        Rdv (lien formulaire contact) => prospect
        */

        const createUserMarker = ({ map, position }) => {
            return userMarker = new google.maps.Marker({
                map: map,
                position: position,
                title: "Votre position",
                icon: {
                    path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z M -2,-30 a 2,2 0 1,1 4,0 2,2 0 1,1 -4,0',
                    fillColor: '#ffffff',
                    strokeColor: '#000',
                    fillOpacity: 1,
                    strokeWeight: 1,
                    scale: 1,
                },
                zIndex: 1000
            });
        };

        const createSearchMarker = ({ map, position }) => {
            // Initialize search marker
            let marker = new google.maps.Marker({
                map: null,
                title: "Recherche",
                icon: {url: iconsUrl + 'wht-circle.png', scaledSize: new google.maps.Size(35, 35)},
            });
            // on click, hide the marker
            google.maps.event.addListener(marker, "click", () => {
                marker.setMap(null);
            });
            return marker;
        };

        const getPositionErrorMessage = code => {
            switch (code) {
                case 1:
                    return 'Permission denied.';
                case 2:
                    return 'Position unavailable.';
                case 3:
                    return 'Timeout reached.';
                default:
                    return null;
            }
        }

        const createMap = ({ lat, lng }) => {
            return new google.maps.Map(document.getElementById('map'), {
                zoom: userPref.mapZoom || 14,
                center: userPref.mapPos || { lat, lng },
                gestureHandling: 'greedy',
                //mapId: "DEMO_MAP_ID", // Map ID is required for advanced markers.

                // https://developers.google.com/maps/documentation/javascript/style-reference
                styles: [
                    {
                        featureType: "poi",
                        stylers: [{ visibility: "off" }],
                    },
                    {
                        featureType: "transit",
                        elementType: "labels.icon",
                        stylers: [{ visibility: "off" }],
                    },
                ],
                // https://developers.google.com/maps/documentation/javascript/controls
                streetViewControl: false,
                mapTypeControl: true,
                mapTypeControlOptions: {
                    position: google.maps.ControlPosition.BOTTOM_LEFT
                },
                mapTypeId: userPref.mapType,
            });
        };

        let isFetchingPoints = false;
        const loadPoints = ({ map, topLeft, bottomRight, onSuccess, onError = () => { } }) => {
            if (isFetchingPoints) {
                return;
            }
            const zoomLevel = map.getZoom();
            const commercialElement = document.getElementById('commercial');
            const priorityElement = document.getElementById('priority');
            const date1Element = document.getElementById('date1');
            const date2Element = document.getElementById('date2');
            const clientElement = document.getElementById('clientId');

            const commercialId = commercialElement ? commercialElement.value : '';
            const priority = priorityElement ? priorityElement.value : '';
            const date1 = date1Element ? date1Element.value : '';
            const date2 = date2Element ? date2Element.value : '';
            const client = clientElement ? clientElement.value : '';

            
            const secteurId = {{session('selectedSecteur')}};
            let params = new URLSearchParams({
                topLeftLat: topLeft.lat(),
                topLeftLng: topLeft.lng(),
                bottomRightLat: bottomRight.lat(),
                bottomRightLng: bottomRight.lng(),
                zoom : zoomLevel,
                secteur : secteurId
            });

            if (commercialId) {
                params.append('commercial', commercialId);
            }

            if (priority) {
                params.append('priority', priority);
            }

            if (date1) {
                params.append('date1', date1);
            }

            if (date2) {
                params.append('date2', date2);
            }

            if(client){
                params.append('client', client);
            }
            
            isFetchingPoints = true;

            //if (map.zoom < 12) return;
            fetch(API_POINTS_URL + '?' + params.toString())
                .then(res => res.json())
                .then(res => {
                    isFetchingPoints = false;
                    onSuccess(res)
                })
                .catch(err => {
                    isFetchingPoints = false;
                    onError(err)
                });
        }

        function displayPoints(map, points) {
            //console.log(points);
            points.forEach(point => {
                //console.log(point);
                createPoint({
                    map: map,
                    point: point
                });
            })
        }

        function hidePoints(map, points){
            const visibleIds = new Set(points.map(p => String(p.id)));
            for (const id in markers) {
                if (!markers.hasOwnProperty(id)) continue;
                const marker = markers[id];

                // Si l’ID du marqueur est dans la liste actuelle, le laisser tel quel
                // Sinon, le cacher
                if (!visibleIds.has(String(id))) {
                    if (marker && marker.setMap) {
                        marker.setMap(null);
                    }
                } 
            }
        }

        const createPoint = ({ map, point }) => {
            //console.log(point)
            const isCommercial = false; // point.id[0] === 'C';
            const showOnMap = isCommercial || userPref.showColors[point.type];
            
            const iconsUrl = "https://maps.google.com/mapfiles/kml/paddle/";

            if (markers[point.id] === undefined || isCommercial) {
                // https://developers.google.com/maps/documentation/javascript/markers
                const iconUrl = isCommercial
                    ? "https://maps.gstatic.com/mapfiles/ms2/micons/cabs.png"
                    : iconsUrl + colors[point.type].img;
                const position = {
                    lat: parseFloat(point.lat),
                    lng: parseFloat(point.lng)
                };
                markers[point.id] = new google.maps.Marker({
                    map: showOnMap ? map : null,
                    position: position,
                    icon: { url: iconUrl, scaledSize: new google.maps.Size(35, 35) },
                    // You can add any data to the marker
                    content: point.content,
                    type: point.type // Pour le filtre d'affichage clients/poses
                })

                let hoverTimeout = null;
                let isHovering = false;

                // Some help here : https://gist.github.com/grandmanitou/8863248
                const marker = markers[point.id]
                google.maps.event.addListener(marker, 'click', (function(marker) {
                    // Funcionception !
                    return async function() {
                        // Set the flag to indicate a marker was clicked
                        isMarkerClicked = true;

                        // Reset the flag after 1 second to allow future map movements to fetch markers
                        setTimeout(() => {
                            isMarkerClicked = false;
                        }, 1000);

                        markerClicked = point.id;

                        const content = await getMarkerDetailContent(markerClicked);
                        infowindow.setContent(content);
                        //infowindow.setOptions({maxWidth: 200});
                        infowindow.open(map, marker);

                        // Add event listeners after the info window is opened
                        google.maps.event.addListenerOnce(infowindow, 'domready', function() {
                            // Event listener for type select

                            // Event listener for comment form
                            const commentForm = document.querySelector('.comment-form');
                            if (commentForm && !commentForm.dataset.listenerAdded) {
                                commentForm.addEventListener('submit', function(e) {
                                    e.preventDefault();

                                    const clientId = this.querySelector('input[name="client_id"]').value;
                                    const commentaire = this.querySelector('textarea[name="commentaire"]').value;
                                    const select = document.querySelector('.type-select');
                                    const statut = select ? select.value : null;

                                    /*if (!commentaire.trim()) {
                                        alert('Veuillez saisir un commentaire');
                                        return;
                                    }*/

                                    // Send AJAX request to store the comment
                                    fetch('{{ route('comments.store') }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                                            'Accept': 'application/json',
                                            'X-XSRF-TOKEN': document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] 
                                        },
                                        body: JSON.stringify({
                                            client_id: clientId,
                                            commentaire: commentaire,
                                            type : statut
                                        })
                                    })
                                    .then(response => {
                                        if (!response.ok) {
                                            throw new Error('Network response was not ok');
                                        }
                                        return response.json();
                                    })
                                    .then(data => {
                                        // Clear the textarea
                                        this.querySelector('textarea[name="commentaire"]').value = '';

                                        if(statut){
                                            marker.setIcon({
                                                url: iconsUrl + colors[statut].img,
                                                scaledSize: new google.maps.Size(35, 35)
                                            });

                                            // Update the marker's colorId
                                            marker.colorId = parseInt(statut);

                                            // Update the point type
                                            point.type = parseInt(statut);
                                        }

                                        // Close the info window
                                        infowindow.close();

                                        // Refresh the map to get updated data including the new comment
                                        refreshMap(map, (points) => {
                                            // Find the updated marker in the markers array
                                            const updatedMarker = markers[markerClicked];
                                            if (updatedMarker) {
                                                google.maps.event.trigger(updatedMarker, 'click');
                                            }
                                        });
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        alert('Une erreur est survenue lors de l\'envoi du commentaire');
                                    });
                                });
                                commentForm.dataset.listenerAdded = "true";
                            }
                        });
                    }
                }) (marker));

                google.maps.event.addListener(marker, 'mouseover', function () {
                    isHovering = true;

                    hoverTimeout = setTimeout(() => {
                        if (isHovering) {
                            google.maps.event.trigger(marker, 'click');
                        }
                    }, 500);
                });

                google.maps.event.addListener(marker, 'mouseout', function () {
                    isHovering = false;

                    if (hoverTimeout) {
                        clearTimeout(hoverTimeout);
                        hoverTimeout = null;
                    }
                });
            }else if(markers[point.id]){
                markers[point.id].content = point.content;
                markers[point.id].setMap(showOnMap ? map : null);
            }
            return markers[point.id]
        }

        const addUiButton = ({controlUI, label, colorId}) => {
            const control = document.createElement("span")
            control.className = "control-ui-button"
            control.setAttribute('data-color-id', colorId) 
            control.style.backgroundColor = colors[colorId].hexa;
            controlUI.appendChild(control)
            control.innerHTML = label
            if (!userPref.showColors[colorId]) {
                control.classList.add("disabled")
            }
            control.addEventListener("click", (e) => {
                e.target.classList.toggle("disabled")
                userPref.showColors[colorId] = !userPref.showColors[colorId]
                saveUserPref()
                markers
                    .filter((m) => m.type === colorId)
                    .forEach((m) => m.setMap(userPref.showColors[colorId] ? map : null))
					
				
            })
        }

        function saveUserPref() {
            localStorage.setItem('userPref', JSON.stringify(userPref))
        }

        // Function to add a button to center the map on user's location
        const addUiLocationButton = ({controlUI, map}) => {
            const control = document.createElement("span")
            control.className = "control-location-ui-button"
            control.style.backgroundColor = "#4285F4"
            control.style.color = "white"
            controlUI.appendChild(control)
            control.innerHTML = "Ma position"

            // Add a location icon to the button
            const icon = document.createElement("i")
            icon.innerHTML = " 📍" // Simple location emoji
            control.appendChild(icon)

            control.addEventListener("click", () => {
                // Check if we have a user marker with a position
                if (userMarker && userMarker.getPosition()) {
                    // Center the map on the user's position
                    map.setCenter(userMarker.getPosition())
                    // Zoom in to a reasonable level if we're zoomed out too far
                    if (map.getZoom() < 14) {
                        map.setZoom(14)
                    }
                } else {
                    // If we don't have a user marker yet, try to get the user's location
                    alert("Localisation en cours... Veuillez autoriser l'accès à votre position.")

                    // Try to get the user's location again
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const userPos = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude
                            }

                            // If we don't have a user marker yet, create one
                            if (!userMarker) {
                                userMarker = new google.maps.Marker({
                                    map: map,
                                    position: userPos,
                                    title: "Votre position",
                                    icon: {
                                        url: 'https://maps.google.com/mapfiles/ms/icons/white-dot.png',
                                        scaledSize: new google.maps.Size(40, 40)
                                    },
                                    zIndex: 1000
                                })
                            } else {
                                // Update the marker position and make it visible
                                userMarker.setPosition(userPos)
                                userMarker.setMap(map)
                            }

                            // Center the map on the user's position
                            map.setCenter(userPos)
                            if (map.getZoom() < 14) {
                                map.setZoom(14)
                            }
                        },
                        (error) => {
                            console.error("Error getting user location:", error)
                            alert("Impossible de déterminer votre position. Veuillez vérifier que la géolocalisation est activée dans votre navigateur.")
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        }
                    )
                }
            })

            return control
        }

        const addUiAdmin = ({controlUI}) => {
            const commerciauxSelectElement = document.getElementById("commerciauxSelect");
            if (commerciauxSelectElement) {
                const control = commerciauxSelectElement.cloneNode(true);
                control.className = "control-ui-button"
                controlUI.appendChild(control)
                control.style.display = "inline-block";
                control.addEventListener("change", (e) => {
                    refreshMap(map);
                })
            }

            const prioritiesSelectElement = document.getElementById("prioritiesSelect");
            if (prioritiesSelectElement) {
                const control = prioritiesSelectElement.cloneNode(true);
                control.className = "control-ui-button"
                controlUI.appendChild(control)
                control.style.display = "inline-block";
                control.addEventListener("change", (e) => {
                    refreshMap(map);
                })
            }

            const date1 = document.getElementById("date1").cloneNode(true);
            date1.className = "control-ui-button control-ui-button-date"
            controlUI.appendChild(date1)
            date1.style.display = "inline-block";
            date1.addEventListener("focusout", (e) => {
                refreshMap(map);
            })

            const date2 = document.getElementById("date2").cloneNode(true);
            date2.className = "control-ui-button control-ui-button-date"
            controlUI.appendChild(date2)
            date2.style.display = "inline-block";
            date2.addEventListener("focusout", (e) => {
                refreshMap(map);
            })

            const client = document.getElementById("clientId").cloneNode(true);
            controlUI.appendChild(client);
        }

        // Function to add search box to the map
        const addUiSearchbox = ({ controlUI, map }) => {
            // Create the search box and link it to the UI element
            const input = document.createElement('input');
            input.type = 'text';
            input.placeholder = 'Rechercher une adresse';
            input.className = 'control-ui-button';
            controlUI.appendChild(input);

            const searchBox = new google.maps.places.SearchBox(input);

            // Bias the SearchBox results towards current map's viewport
            /*map.addListener('bounds_changed', () => {
                console.log("ici");
                searchBox.setBounds(map.getBounds());
            });*/

            // Listen for the event fired when the user selects a prediction and retrieve
            // more details for that place
            searchBox.addListener('places_changed', () => {
                const places = searchBox.getPlaces();

                if (places.length === 0) {
                    return;
                }

                // For each place, get the icon, name and location
                const bounds = new google.maps.LatLngBounds();

                places.forEach(place => {
                    if (!place.geometry || !place.geometry.location) {
                        console.log('Returned place contains no geometry');
                        return;
                    }

                    // Create a marker for the place
                    searchMarker.setPosition(place.geometry.location);
                    searchMarker.setMap(map);

                    if (place.geometry.viewport) {
                        // Only geocodes have viewport
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });

                map.fitBounds(bounds);
            });
        };

        // Function to add client search box to the map
        const addUiClientSearchbox = ({ controlUI, map }) => {
            // Create a container for the search box and suggestions
            const searchContainer = document.createElement('div');
            searchContainer.style.position = 'relative';
            searchContainer.style.display = 'inline-block';
            controlUI.appendChild(searchContainer);

            // Create the search box and link it to the UI element
            const input = document.createElement('input');
            input.type = 'text';
            input.placeholder = 'Rechercher un client (nom, tél, email)';
            input.className = 'control-ui-button';
            input.id = 'client-search';
            input.style.minWidth = '250px';
            searchContainer.appendChild(input);

            // Create a dropdown for suggestions
            const suggestionsDropdown = document.createElement('div');
            suggestionsDropdown.className = 'suggestions-dropdown';
            suggestionsDropdown.style.display = 'none';
            suggestionsDropdown.style.position = 'absolute';
            suggestionsDropdown.style.backgroundColor = '#fff';
            suggestionsDropdown.style.width = '100%';
            suggestionsDropdown.style.maxHeight = '200px';
            suggestionsDropdown.style.overflowY = 'auto';
            suggestionsDropdown.style.boxShadow = '0 2px 4px rgba(0,0,0,0.2)';
            suggestionsDropdown.style.zIndex = '1000';
            suggestionsDropdown.style.borderRadius = '0 0 4px 4px';
            searchContainer.appendChild(suggestionsDropdown);

            // Create a container for search results
            let clientSearchMarkers = [];

            // Function to display a client on the map
            const displayClientOnMap = (clientId) => {
                // If we already have a stored filter state, restore it first
                // This handles the case when a user selects a different client without clearing the search
                if (originalFilterState) {
                    // We need to restore the filters but not fetch markers yet
                    userPref.showColors = JSON.parse(JSON.stringify(originalFilterState));

                    // Update button UI
                    document.querySelectorAll('.control-ui-button').forEach(button => {
                        if (!button.id && !button.type) { // Only target filter buttons
                            const colorId = parseInt(button.getAttribute('data-color-id'));
                            if (!isNaN(colorId)) {
                                if (userPref.showColors[colorId]) {
                                    button.classList.remove('disabled');
                                } else {
                                    button.classList.add('disabled');
                                }
                            }
                        }
                    });

                    // Reset the stored state
                    originalFilterState = null;
                }

                // Store the current filter state
                originalFilterState = JSON.parse(JSON.stringify(userPref.showColors));

                // Clear previous search markers
                clientSearchMarkers.forEach(marker => {
                    marker.setMap(null);
                });
                clientSearchMarkers = [];


                // Disable all filter buttons
                document.querySelectorAll('.control-ui-button').forEach(button => {
                    if (!button.id && !button.type) { // Only target filter buttons, not inputs or other controls
                        button.classList.add('disabled');
                    }
                });

                // Disable all filters in user preferences
                Object.keys(userPref.showColors).forEach(key => {
                    userPref.showColors[key] = false;
                });

                // Show loading indicator
                input.style.backgroundColor = '#f0f0f0';

                let clientElement = document.getElementById("clientId");
                clientElement.value= clientId;
                
                refreshMap(map, (points) =>{
                    // Remove loading indicator
                    input.style.backgroundColor = '#fff';
                    
                    clientElement.value= '';
                    
                    // Clear all existing markers
                    //clearMarkers();

                    // Create bounds for the search results
                    const bounds = new google.maps.LatLngBounds();

                    // Create marker for the client
                    const point = points[0];

                    // Enable this marker type (keep the filter for this client type enabled)
                    userPref.showColors[point.type] = true;

                    // Update the UI to reflect the enabled filter
                    document.querySelectorAll('.control-ui-button').forEach(button => {
                        if (!button.id && !button.type) { // Only target filter buttons
                            const colorId = parseInt(button.getAttribute('data-color-id'));
                            if (!isNaN(colorId) && colorId === point.type) {
                                button.classList.remove('disabled');
                            }
                        }
                    });

                    const marker = createPoint({
                        map: map,
                        point: point
                    });

                    if (marker) {
                        marker.setMap(map);
                        clientSearchMarkers.push(marker);

                        // Add to bounds
                        bounds.extend(new google.maps.LatLng(
                            parseFloat(point.lat),
                            parseFloat(point.lng)
                        ));

                        // Fit the map to the bounds and zoom in
                        map.fitBounds(bounds);
                        if (map.getZoom() > 15) map.setZoom(15);

                        // Open the info window
                        google.maps.event.trigger(marker, 'click');
                        refreshMap(map);
                    }
                })
            };

            // Function to restore original filter state
            const restoreFilters = () => {
                if (originalFilterState) {
                    // Restore user preferences
                    userPref.showColors = JSON.parse(JSON.stringify(originalFilterState));
                    saveUserPref();

                    // Update button UI
                    document.querySelectorAll('.control-ui-button').forEach(button => {
                        if (!button.id && !button.type) { // Only target filter buttons
                            const colorId = parseInt(button.getAttribute('data-color-id'));
                            if (!isNaN(colorId)) {
                                if (userPref.showColors[colorId]) {
                                    button.classList.remove('disabled');
                                } else {
                                    button.classList.add('disabled');
                                }
                            }
                        }
                    });

                    // Reset the stored state
                    originalFilterState = null;

                    // Refresh markers
                    refreshMap(map);
                }
            };

            // Add event listener for input changes (with debounce)
            let debounceTimeout;
            input.addEventListener('input', (e) => {
                clearTimeout(debounceTimeout);

                const query = e.target.value.trim();

                // If query is empty and we have stored filter state, restore filters
                if (query.length === 0 && originalFilterState) {
                    restoreFilters();
                }

                // Hide suggestions if query is too short
                if (query.length < 2) {
                    suggestionsDropdown.style.display = 'none';
                    return;
                }

                // Debounce the autocomplete to avoid too many requests
                debounceTimeout = setTimeout(() => {
                    // Show loading indicator
                    input.style.backgroundColor = '#f0f0f0';

                    // Fetch autocomplete suggestions
                    fetch(`{{ route('clients.autocomplete') }}?query=${encodeURIComponent(query)}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(suggestions => {
                            // Remove loading indicator
                            input.style.backgroundColor = '#fff';

                            // Clear previous suggestions
                            suggestionsDropdown.innerHTML = '';

                            if (suggestions.length === 0) {
                                // No suggestions found
                                suggestionsDropdown.style.display = 'none';
                                return;
                            }

                            // Add suggestions to dropdown
                            suggestions.forEach(suggestion => {
                                const suggestionItem = document.createElement('div');
                                suggestionItem.className = 'suggestion-item';
                                suggestionItem.style.padding = '8px 12px';
                                suggestionItem.style.cursor = 'pointer';
                                suggestionItem.style.borderBottom = '1px solid #eee';
                                suggestionItem.textContent = suggestion.text;

                                // Add hover effect
                                suggestionItem.addEventListener('mouseover', () => {
                                    suggestionItem.style.backgroundColor = '#f0f0f0';
                                });
                                suggestionItem.addEventListener('mouseout', () => {
                                    suggestionItem.style.backgroundColor = '#fff';
                                });
                                
                                // Add click event to select suggestion
                                suggestionItem.addEventListener('click', () => {
                                    input.value = suggestion.text;
                                    suggestionsDropdown.style.display = 'none';
                                    displayClientOnMap(suggestion.id);
                                });

                                suggestionsDropdown.appendChild(suggestionItem);
                            });

                            // Show suggestions dropdown
                            suggestionsDropdown.style.display = 'block';
                        })
                        .catch(error => {
                            console.error('Error fetching suggestions:', error);
                            input.style.backgroundColor = '#fff';
                            suggestionsDropdown.style.display = 'none';
                        });
                }, 300); // 300ms debounce for autocomplete
            });

            // Close suggestions when clicking outside
            document.addEventListener('click', (e) => {
                if (!searchContainer.contains(e.target)) {
                    suggestionsDropdown.style.display = 'none';
                }
            });
        };

        function refreshMap(map, callback = null){
            let bounds =  map.getBounds();
            let topLeft = bounds.getNorthEast();
            let bottomRight = bounds.getSouthWest();
            loadPoints({
                map: map,
                topLeft: topLeft,
                bottomRight: bottomRight,
                onSuccess: (points) => {
                    displayPoints(map, points);
                    hidePoints(map, points);
                    userPref.mapPos = map.getCenter();
                    userPref.mapZoom = map.getZoom();
                    saveUserPref();
					
                    //updateUI(map);
                    // Execute callback if provided
                    if (callback && typeof callback === 'function') {

                        callback(points);
                    }

                },
                onError: (e) => {
                    console.log(e);
                },
            });
            
        }

        function initMap() {

            console.log('initMap...');
            const mapContainer = document.getElementById('map');
            const loadingContainer = document.getElementById('map-loading');

            navigator.geolocation.getCurrentPosition(
                position => {
                    // ✅ Géoloc OK → Créer la carte
                    console.log('GPS OK:', position.coords.latitude, position.coords.longitude);
                    const initialPosition = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    

                    // Cacher loader, montrer carte
                    loadingContainer.style.display = 'none';
                    mapContainer.style.display = 'block';

                    map = createMap(initialPosition);
            
                    const zones = @json($zones);

                    zones.forEach(zone => {
                        new google.maps.Polygon({
                            paths: zone.coords,
                            strokeColor: zone.color,
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            map: map,
                            clickable: false
                        });
                    });


                    userMarker = createUserMarker({map, position: initialPosition});
                    searchMarker = createSearchMarker({map, position: initialPosition});

                    const geocoder = new google.maps.Geocoder();

                    infowindow = new google.maps.InfoWindow();
                    let formmarker = new google.maps.Marker({
                        map,
                        title: "Nouveau client",
                        icon: { url: iconsUrl + colors[98].img, scaledSize: new google.maps.Size(35, 35) },
                    });
                    let formwindow = new google.maps.InfoWindow({
                        content: document.querySelector("div[name=formwindowcontent]")
                    });


                    google.maps.event.addListener( map, 'maptypeid_changed', () => {
                        userPref.mapType = map.getMapTypeId()
                        saveUserPref()
                    });
                    
                    const nomElement = document.getElementById('nom');
                    const adresseElement = document.getElementById('adresse');
                    const cpElement = document.getElementById('code_postal');
                    const villeElement = document.getElementById('ville');
                    const gpsLatElement = document.getElementById('gps_lat');
                    const gpsLngElement = document.getElementById('gps_lng');
                    const userLatElement = document.getElementById('user_lat');
                    const userLngElement = document.getElementById('user_lng'); 
                    const signeElement = document.getElementById('signe');
                    const sourceElement = document.getElementById('source');
                    const typeElement = document.getElementById('type');

                    google.maps.event.addListener(formmarker, 'click', function(marker) {
                        geocoder.geocode(
                            {'latLng': marker.latLng},
                            (results, status) => {
                                if (status === google.maps.GeocoderStatus.OK) {
                                    const place = results[0];

                                    if (adresseElement) adresseElement.value = place["address_components"][0]["long_name"] + " " + place["address_components"][1]["long_name"];
                                    if (cpElement) cpElement.value = (place["address_components"][6]) ? place["address_components"][6]["long_name"] : '';
                                    if (villeElement) villeElement.value = place["address_components"][2]["long_name"];
                                    if (gpsLatElement) gpsLatElement.value = marker.latLng.lat();
                                    if (gpsLngElement) gpsLngElement.value = marker.latLng.lng();
                                    if (userMarker && userMarker.map) {
                                        if (userLatElement) userLatElement.value = userMarker.getPosition().lat();
                                        if (userLngElement) userLngElement.value = userMarker.getPosition().lng();
                                    }
                                    if(nomElement) nomElement.value = '';
                                    if (signeElement) signeElement.value = '';
                                    if (sourceElement) sourceElement.value = '1';
                                    if (typeElement) typeElement.value = '';
                                }
                            }
                        )
                        // Make the form visible before opening it in the InfoWindow
                        const divElement = document.querySelector("div[name=formwindowcontent]");
                        if (divElement) {
                            formElement = divElement.querySelector("form")
                            divElement.style.display = "block";

                            // Add submit event listener to the form
                            formElement.addEventListener('submit', function(e) {
                                e.preventDefault(); // Prevent default form submission

                                const submitBtn = document.getElementById('submitContact');
                                if (submitBtn.disabled) return;

                                submitBtn.disabled = true;
                                submitBtn.textContent = 'Envoi...';
                                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');

                                // Get the selected sector from the header
                                /*const secteurSelect = document.getElementById('secteurs');
                                const secteurId = secteurSelect ? secteurSelect.options[secteurSelect.selectedIndex].value : '';

                                // Set the sector ID in the hidden input field
                                document.getElementById('secteur').value = secteurSelect ? secteurSelect.selectedIndex + 1 : 0;*/

                                // Create FormData object from the form
                                const formData = new FormData(formElement);

                                // Send AJAX request
                                fetch("{{ route('formulaire-contact') }}", {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(async response => {
                                    if (!response.ok) {
                                        if (response.status === 422) {
                                            const errorData = await response.json();
                                            displayValidationErrors(errorData.errors);
                                        } else {
                                            throw new Error('Unknown error');
                                        }
                                        return;
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    if (data) {
                                        if(data.doublon === true){
                                            const marker = markers[data.client];
                                            if(!marker){
                                                alert('Doublon detecté dans un autre secteur');
                                            }else{
                                                marker.setMap(map);
                                                google.maps.event.trigger(marker, 'click');
                                            }
                                        }else{
                                            // Refresh the map markers
                                            refreshMap(map);
                                        }
                                        // Close the info window
                                        formwindow.close();

                                        // Hide the marker
                                        formmarker.setMap(null);
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    submitBtn.disabled = false;
                                    submitBtn.textContent = 'Créer le contact';
                                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                })
                                .finally(() => {
                                    // Optionnel : reset après succès
                                    submitBtn.disabled = false;
                                    submitBtn.textContent = 'Créer le contact';
                                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                });
                            });
                        }
                        formwindow.open(map, formmarker)

                    });

                    // Fermeture de l'infowindow au clic sur la carte
                    google.maps.event.addListener(map, "click", (e) => {
                        if (infowindow && infowindow.getMap() != null) {
                            console.log("close infowindow")
                            infowindow.close()
                        }
                        else if (formwindow && formwindow.getMap() != null) {
                            console.log("close formwindow")
                            formwindow.close()
                        }
                        else {
                            console.log("set formmarker")
                            formmarker.setPosition(e.latLng)
                            formmarker.setMap(map)
                        }
                    });

                    // Mise à jour des coordonnées GPS si modification de l'adresse

                    if (adresseElement) {
                        adresseElement.addEventListener('change', function() {
                            // Construire l'adresse complète
                            const addressParts = [
                                this.value.trim(),
                                cpElement ? cpElement.value.trim() : '',
                                villeElement ? villeElement.value.trim() : ''
                            ];

                            // Joindre les parties d'adresse et nettoyer les espaces vides 
                            const fullAddress = addressParts.filter(part => part.length > 0).join(', ');

                            if (fullAddress.length > 0) {
                                geocoder.geocode({ address: fullAddress }, function(results, status) {
                                    if (status === 'OK' && results[0]) {
                                        const location = results[0].geometry.location;
                                        if (gpsLatElement) gpsLatElement.value = location.lat();
                                        if (gpsLngElement) gpsLngElement.value = location.lng();
                                        console.log('Coordonnées mises à jour avec adresse complète:', location.lat(), location.lng());
                                    } else {
                                        console.warn('Geocoding failed: ' + status);
                                    }
                                });
                            }
                        });
                    }

                    const controlUI = document.createElement("div");
                    controlUI.className = "control-ui";
                    document.getElementById('filters-container').appendChild(controlUI);
                    
                    // Add a location button to center on user's position
                    addUiLocationButton({controlUI, map});

                    addUiButton({controlUI, colorId: 6, label: "Refus"       });
                    addUiButton({controlUI, colorId: 7, label: "Absent"      });
                    addUiButton({controlUI, colorId: 8, label: "Relance"     });
                    addUiButton({controlUI, colorId: 0, label: "Précontacts" });
                    addUiButton({controlUI, colorId: 1, label: "Prospects"   });
                    addUiButton({controlUI, colorId: 2, label: "Clients"     });
                    addUiButton({controlUI, colorId: 3, label: "Annulés"     });
                    addUiButton({controlUI, colorId: 5, label: "Poses"       });
                    addUiButton({controlUI, colorId: 9, label: "Orphelin"    });
                    addUiAdmin({controlUI});

                    // Initialize user location tracking for continuous updates
                    initUserLocationTracking(map);

                    // https://developers.google.com/maps/documentation/javascript/events
                    // https://gis.stackexchange.com/questions/301340/google-maps-dynamically-load-marker-after-center-changed-with-minimum-distance
                    map.addListener('idle', () => {
                        //console.log('idle', map.zoom);
                        if (!isMarkerClicked) {
                            refreshMap(map);
                        }
                    });


                    // https://developers.google.com/maps/documentation/javascript/examples/places-searchbox
                    addUiSearchbox({controlUI, map});

                    // Add client search box
                    addUiClientSearchbox({controlUI, map});



                    console.log('Done');
                },
                error => {
                    // ❌ Géoloc refusée/échec → Bloquer la carte
                    console.error('GPS refusé:', error);
                    loadingContainer.innerHTML = `
                        <div class="instructions">
                            <h3>🔧 Activez la géolocalisation :</h3>
                            <ol style="text-align: left; font-size: 14px;">
                                <li><strong>Chrome/Edge :</strong> Cliquez 🔒 (à droite de l'URL) → <em>Site settings</em> → <em>Location</em> → <em>Allow</em> → <strong>Actualiser</strong></li>
                                <li><strong>Firefox :</strong> Cliquez 🔒 → <em>Permissions</em> → <em>Location</em> → <em>Allow</em> → <strong>Actualiser</strong></li>
                                <li><strong>Safari :</strong> Paramètres → Confidentialité → Localisation → Autoriser</li>
                                <li><strong>Mobile :</strong> Paramètres app → Permissions → Localisation → Toujours</li>
                            </ol>
                        </div>
                        <button onclick="location.reload()" class="retry-btn">
                            ✅ J'ai activé la localisation → Actualiser
                        </button>
                    `;
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,  // 10s max
                    maximumAge: 60000  // Cache 1min OK pour init
                }
            );
            
            

        }

        function retryGeolocation() {
            location.reload();
        }

        function displayValidationErrors(errors) {
            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(el => el.remove());

            for (const field in errors) {
                const messages = errors[field];
                const input = document.querySelector(`[name="${field}"]`);
                if (input && messages.length) {
                    const errorSpan = document.createElement('span');
                    errorSpan.classList.add('error-message', 'text-red-500', 'text-xs');
                    errorSpan.textContent = messages[0]; // affiche 1er message
                    input.insertAdjacentElement('afterend', errorSpan);
                }
            }
        }

        // Function to initialize user location tracking
        function initUserLocationTracking(map) {
            // Start tracking the user's location
            trackLocation({
                onSuccess: (position) => {
                    const userPos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    // Update user marker position
                    userMarker.setPosition(userPos);

                    // Make sure the marker is visible on the map
                    if (!userMarker.getMap()) {
                        userMarker.setMap(map);

                        // Center map on user's position the first time we get it
                        map.setCenter(userPos);
                    }

                    // Update hidden form fields for user location
                    const userLatElement = document.getElementById('user_lat');
                    const userLngElement = document.getElementById('user_lng');
                    if (userLatElement) userLatElement.value = userPos.lat;
                    if (userLngElement) userLngElement.value = userPos.lng;

                    //console.log('User position updated:', userPos);
                },
                onError: (error) => {
                    console.error('Geolocation error:', error.message);
                    // Optionally show an error message to the user
                    alert('Impossible de déterminer votre position. Veuillez vérifier que la géolocalisation est activée dans votre navigateur.');
                }
            });
        }

        async function getMarkerDetailContent(clientId){
            
            const formData = new FormData();
            formData.append('clientId', clientId);
            // Send AJAX request
            const response = await fetch("{{ route('map.detailContent') }}?clientId=" + clientId, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            
            if (!response.ok) {
                if (response.status === 422) {
                    const errorData = await response.json();
                    displayValidationErrors(errorData.errors);
                } else {
                    throw new Error('Unknown error');
                }
                return;
            }
            const data = await response.text();
            return data;
        }

        const trackLocation = ({
            onSuccess, onError = () => {
                console.error('Geolocation error');
            }
        }) => {
            // Géolocalisation activée
            if (!('geolocation' in navigator)) {
                return onError(new Error('Geolocation is not supported by your browser.'));
            }
            // Use watchPosition instead.
            return navigator.geolocation.watchPosition(onSuccess, onError, {
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 0
            });
        };

    </script>

    <!-- Async script executes immediately and must be after any DOM elements used in callback. -->
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&loading=async&callback=initMap&libraries=places&v=weekly" async defer></script>

    <script>
        function toggleCollapse(){
            const el = document.getElementById('filters-container');
            const text = document.getElementById('toggleFilterText');
            if(el.classList.contains('hidden')){
                el.classList.remove('hidden');
                text.innerHTML = "▲ Filtres ▲";
            
            }else{
                el.classList.add('hidden');
                text.innerHTML = "▼ Filtres ▼";
            }
        }
    </script>
@endsection
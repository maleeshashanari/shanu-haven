/* ===================================
   SHANU HAVEN - ADVANCED MAP AND GPS
=================================== */

document.addEventListener("DOMContentLoaded", function () {

    const villaLat = 6.0329;
    const villaLng = 80.2168;

    const gpsButton = document.getElementById("findLocationBtn");
    const searchButton = document.getElementById("searchLocationBtn");
    const selectButton = document.getElementById("selectMapBtn");
    const directionsButton = document.getElementById("directionsBtn");

    const searchInput = document.getElementById("locationSearch");
    const results = document.getElementById("locationResults");
    const gpsStatus = document.getElementById("gpsStatus");
    const distanceText = document.getElementById("villaDistance");
    const selectedLocation = document.getElementById("selectedLocation");

    let userMarker = null;
    let distanceLine = null;
    let startLocation = null;
    let selectingLocation = false;
    let searchResults = [];
    let lastSearch = 0;

    /* Check Leaflet Library */

    if (typeof L === "undefined") {
        gpsStatus.textContent = "Unable to load map.";
        return;
    }

    /* Create Map */

    const map = L.map("villaMap").setView(
        [villaLat, villaLng], 15
    );

    L.tileLayer(
        "https://tile.openstreetmap.org/{z}/{x}/{y}.png",
        {
            attribution: "&copy; OpenStreetMap contributors",
            maxZoom: 19
        }
    ).addTo(map);

    /* Villa Marker */

    L.marker([villaLat, villaLng])
        .addTo(map)
        .bindPopup(
            "<b>Shanu Haven - Coastal Villa</b><br>" +
            "Galle, Sri Lanka (Demo Location)"
        )
        .openPopup();

    /* Update Starting Location */

    function updateLocation(lat, lng, name) {

        startLocation = { lat, lng };

        if (userMarker) {
            map.removeLayer(userMarker);
        }

        if (distanceLine) {
            map.removeLayer(distanceLine);
        }

        userMarker = L.circleMarker([lat, lng], {
            radius: 9,
            color: "#ffffff",
            weight: 3,
            fillColor: "#2563EB",
            fillOpacity: 1
        }).addTo(map);

        const popup = document.createElement("span");
        popup.textContent = name;

        userMarker.bindPopup(popup);

        /* Calculate Straight-line Distance */

        const distance = map.distance(
            [lat, lng],
            [villaLat, villaLng]
        ) / 1000;

        distanceText.textContent =
            distance.toFixed(2) + " km (straight-line)";

        selectedLocation.textContent = name;

        /* Draw Distance Line */

        distanceLine = L.polyline(
            [
                [lat, lng],
                [villaLat, villaLng]
            ],
            {
                color: "#2563EB",
                weight: 3,
                dashArray: "8, 8"
            }
        ).addTo(map);

        map.fitBounds(
            [
                [lat, lng],
                [villaLat, villaLng]
            ],
            {
                padding: [40, 40],
                maxZoom: 16
            }
        );

        gpsStatus.textContent =
            "Starting location selected successfully.";

        selectingLocation = false;

        map.getContainer().classList.remove(
            "sh-map-selecting"
        );
    }

    /* 1. Search Location */

    async function searchLocation() {

        const query = searchInput.value.trim();

        if (!query) {
            gpsStatus.textContent =
                "Please enter a city or location.";
            return;
        }

        if (Date.now() - lastSearch < 1100) {
            return;
        }

        lastSearch = Date.now();

        searchButton.disabled = true;
        results.hidden = true;

        gpsStatus.textContent = "Searching location...";

        try {

            const url =
                "https://nominatim.openstreetmap.org/search" +
                "?format=json&countrycodes=lk&limit=5&q=" +
                encodeURIComponent(query);

            const response = await fetch(url);

            if (!response.ok) {
                throw new Error("Search unavailable");
            }

            searchResults = await response.json();

            if (searchResults.length === 0) {

                gpsStatus.textContent =
                    "Location not found. Try another city.";

                return;
            }

            results.replaceChildren();

            const placeholder = new Option(
                "Choose a location", ""
            );

            results.add(placeholder);

            searchResults.forEach(function (place, index) {

                results.add(
                    new Option(place.display_name, index)
                );

            });

            results.hidden = false;

            gpsStatus.textContent =
                "Please select a location from the results.";

        } catch (error) {

            gpsStatus.textContent =
                "Search unavailable. Try selecting on the map.";

        } finally {

            searchButton.disabled = false;

        }
    }

    searchButton.addEventListener("click", searchLocation);

    searchInput.addEventListener("keydown", function (event) {

        if (event.key === "Enter") {
            event.preventDefault();

            if (!searchButton.disabled) {
                searchLocation();
            }
        }

    });

    results.addEventListener("change", function () {

        if (results.value === "") {
            return;
        }

        const place = searchResults[Number(results.value)];

        if (!place) {
            return;
        }

        updateLocation(
            Number(place.lat),
            Number(place.lon),
            place.display_name
        );

    });

    /* 2. GPS Current Location */

    gpsButton.addEventListener("click", function () {

        if (!navigator.geolocation) {

            gpsStatus.textContent =
                "GPS is not supported by your browser.";

            return;
        }

        gpsButton.disabled = true;

        gpsStatus.textContent =
            "Finding your current location...";

        navigator.geolocation.getCurrentPosition(

            function (position) {

                updateLocation(
                    position.coords.latitude,
                    position.coords.longitude,
                    "My Current Location"
                );

                gpsButton.disabled = false;

            },

            function (error) {

                gpsStatus.textContent =
                    error.code === 1
                    ? "Location permission denied."
                    : "Unable to detect your location.";

                gpsButton.disabled = false;

            },

            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }

        );

    });

    /* 3. Select Location on Map */

    selectButton.addEventListener("click", function () {

        selectingLocation = true;

        map.getContainer().classList.add(
            "sh-map-selecting"
        );

        gpsStatus.textContent =
            "Click anywhere on the map to select your location.";

        map.setView([7.8731, 80.7718], 7);

    });

    map.on("click", function (event) {

        if (!selectingLocation) {
            return;
        }

        updateLocation(
            event.latlng.lat,
            event.latlng.lng,
            "Selected Map Location"
        );

    });

    /* 4. Google Maps Directions */

    directionsButton.addEventListener("click", function () {

        if (!startLocation) {

            gpsStatus.textContent =
                "Please select your starting location first.";

            return;
        }

        const url =
            "https://www.google.com/maps/dir/?api=1" +
            "&origin=" +
            startLocation.lat + "," + startLocation.lng +
            "&destination=" +
            villaLat + "," + villaLng +
            "&travelmode=driving";

        window.open(
            url,
            "_blank",
            "noopener,noreferrer"
        );

    });

});
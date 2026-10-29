// Leaflet helpers (OpenStreetMap tiles). Leaflet (global L) must be loaded before these are called.

function leafletCreateMap(elementId, lat, lng, zoom) {
    let map = L.map(elementId).setView([parseFloat(lat), parseFloat(lng)], zoom);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
    return map;
}

// Show the locations returned by jsonUrl: [{lat, lng, icon, iw_content}, ...]
function showLeafletLocations(elementId, lat, lng, zoom, jsonUrl) {
    let map = leafletCreateMap(elementId, lat, lng, zoom);
    fetch(jsonUrl)
        .then(res => res.json())
        .then(locations => {
            if (!Array.isArray(locations)) {
                return;
            }
            locations.forEach(location => {
                let options = {};
                if (location.icon) {
                    options.icon = L.icon({
                        iconUrl: location.icon,
                        iconSize: [32, 32],
                        iconAnchor: [16, 32],
                        popupAnchor: [0, -30]
                    });
                }
                let marker = L.marker([parseFloat(location.lat), parseFloat(location.lng)], options).addTo(map);
                if (location.iw_content) {
                    marker.bindPopup(location.iw_content);
                }
            });
        });
    return map;
}

// Draw the driving route between two points (OSRM) and call onDone(true|false) when finished
function showLeafletRoute(elementId, origin, destination, onDone) {
    let map = leafletCreateMap(elementId, destination[0], destination[1], 10);
    L.marker(origin).addTo(map);
    L.marker(destination).addTo(map);
    let url = 'https://router.project-osrm.org/route/v1/driving/' +
        origin[1] + ',' + origin[0] + ';' + destination[1] + ',' + destination[0] +
        '?overview=full&geometries=geojson';
    fetch(url)
        .then(res => res.json())
        .then(json => {
            if (json.code !== 'Ok' || !json.routes || json.routes.length === 0) {
                map.fitBounds([origin, destination]);
                onDone && onDone(false);
                return;
            }
            let line = L.geoJSON(json.routes[0].geometry, {
                style: {
                    color: '#4e73df',
                    weight: 5
                }
            }).addTo(map);
            map.fitBounds(line.getBounds());
            onDone && onDone(true);
        })
        .catch(() => {
            map.fitBounds([origin, destination]);
            onDone && onDone(false);
        });
    return map;
}

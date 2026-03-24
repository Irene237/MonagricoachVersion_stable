<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div id="map" style="height: 400px; border-radius: 20px; border: 2px solid #06bdbd;"></div>

<script>
    // On initialise la carte sur Douala (vu ton dashboard !)
    var map = L.map('map').setView([4.0511, 9.7679], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // Fonction pour récupérer la position de l'utilisateur
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition);
        }
    }

    function showPosition(position) {
        var userLat = position.coords.latitude;
        var userLng = position.coords.longitude;
        
        // Marqueur pour l'agriculteur
        L.marker([userLat, userLng]).addTo(map)
            .bindPopup("<b>Vous êtes ici</b>").openPopup();
            
        // Ici, on pourrait ajouter les points d'eau récupérés en PHP
        // Exemple : L.marker([4.06, 9.77]).addTo(map).bindPopup("Puits A1");
    }
    
    getLocation();
</script>
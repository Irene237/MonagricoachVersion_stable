<?php
// On s'assure que les paramètres 'lat' et 'lon' existent
if (!isset($_GET['lat']) || !isset($_GET['lon'])) {
    echo "<p class='error-msg'>Coordonnées non fournies.</p>";
    exit();
}

// 1. Récupération sécurisée des coordonnées
$latitude = filter_var($_GET['lat'], FILTER_VALIDATE_FLOAT);
$longitude = filter_var($_GET['lon'], FILTER_VALIDATE_FLOAT);

if ($latitude === false || $longitude === false) {
    echo "<p class='error-msg'>Coordonnées invalides.</p>";
    exit();
}

// URL de l'API Open-Meteo utilisant les coordonnées dynamiques
$apiUrl = "https://api.open-meteo.com/v1/forecast?latitude={$latitude}&longitude={$longitude}&current_weather=true&timezone=auto";

// 2. Récupération des données JSON
$response = @file_get_contents($apiUrl);

$html_output = "<p class='error-msg'>Impossible de récupérer les données météo.</p>";

if ($response !== FALSE) {
    $data = json_decode($response, true);

    if (isset($data['current_weather'])) {
        $current_temp = round($data['current_weather']['temperature']);
        $wind_speed = round($data['current_weather']['windspeed']);
        $weather_code = $data['current_weather']['weathercode'];
        
        // Récupération de l'heure locale et du nom de la ville/timezone
        $timezone = $data['timezone'] ?? 'Lieu inconnu';
        
        // Mappage simple du code météo (comme dans l'exemple précédent)
        $description = match($weather_code) {
            0 => "Ciel clair ☀",
            1, 2 => "Principalement clair à partiellement nuageux",
            3 => "Couvert ☁",
            45, 48 => "Brouillard",
            // Ajoutez d'autres codes pour une couverture complète
            default => "Temps inconnu ({$weather_code})",
        };

        // 3. Génération du HTML de la météo à retourner au JavaScript
        $html_output = "
            <div class='weather-details'>
                <h3>Météo pour votre position actuelle</h3>
                <p class='city-name'>Fuseau horaire : {$timezone}</p>
                <div class='main-info'>
                    <span class='temperature'>{$current_temp}°C</span>
                    <span class='description'>{$description}</span>
                </div>
                <div class='details'>
                    <p>Vitesse du vent : {$wind_speed} km/h</p>
                </div>
            </div>
        ";
    }
}

// 4. Affichage du résultat (sera reçu par le JavaScript)
echo $html_output;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Météo par Géolocalisation</title>
</head>
<body>

    <div id="loading">Chargement de la géolocalisation...</div>
    <div id="weather-result"></div>

    <script>
        // Fonction pour récupérer la position
        function getLocation() {
            if (navigator.geolocation) {
                // Demande au navigateur de fournir la position
                navigator.geolocation.getCurrentPosition(showPosition, showError);
            } else {
                document.getElementById("loading").innerHTML = "La géolocalisation n'est pas supportée par ce navigateur.";
            }
        }

        // Fonction appelée si la position est trouvée
        function showPosition(position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            
            // 1. Masquer le message de chargement
            document.getElementById("loading").style.display = 'none';

            // 2. Envoyer les coordonnées au PHP pour l'appel API
            fetchWeather(lat, lon); 
        }

        // Fonction appelée en cas d'erreur de géolocalisation
        function showError(error) {
            let msg;
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    msg = "L'utilisateur a refusé la demande de géolocalisation.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    msg = "Information de localisation non disponible.";
                    break;
                case error.TIMEOUT:
                    msg = "La demande de l'utilisateur a expiré.";
                    break;
                case error.UNKNOWN_ERROR:
                    msg = "Une erreur inconnue est survenue.";
                    break;
            }
            document.getElementById("loading").innerHTML = Erreur de Géolocalisation: ${msg};
        }
        
        // --- NOUVEAU : Fonction pour appeler le script PHP ---
        function fetchWeather(lat, lon) {
            // Nous appelons notre script PHP en lui passant les coordonnées via l'URL
            fetch(get_weather.php?lat=${lat}&lon=${lon})
                .then(response => response.text())
                .then(data => {
                    // Injecter le résultat du PHP dans la page
                    document.getElementById("weather-result").innerHTML = data;
                })
                .catch(error => {
                    document.getElementById("weather-result").innerHTML = "Erreur lors de la récupération des données météo.";
                    console.error('Fetch error:', error);
                });
        }

        // Démarrer la géolocalisation au chargement de la page
        window.onload = getLocation;
    </script>
</body>
</html>
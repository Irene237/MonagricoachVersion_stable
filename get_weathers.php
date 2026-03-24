<?php
// Fichier : get_weather.php
header('Content-Type: text/html; charset=utf-8');

// --- FORCE LES COORDONNÉES SUR YAOUNDÉ ---
$latitude = 3.848;
$longitude = 11.502;

// URL de l'API Open-Meteo avec les coordonnées de Yaoundé
$apiUrl = "https://api.open-meteo.com/v1/forecast?latitude={$latitude}&longitude={$longitude}&current_weather=true&temperature_unit=celsius&wind_speed_unit=kmh&precipitation_unit=mm&timezone=Africa/Lagos";

// Récupération des données
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$html_output = "<div class='error-msg-weather'><i class='fas fa-cloud'></i> Erreur météo: {$http_code}</div>";

if ($response !== FALSE && $http_code === 200) {
    $data = json_decode($response, true);

    if (isset($data['current_weather'])) {
        $current_temp = round($data['current_weather']['temperature']);
        $wind_speed = round($data['current_weather']['windspeed']);
        $weather_code = $data['current_weather']['weathercode'];
        
        // On force le nom de la ville car l'API retourne souvent le fuseau (ex: Lagos)
        $city_name = "Yaoundé"; 

        // Mappage complet des codes WMO pour éviter le "Temps inconnu"
        $description_map = [
            0  => ["Ciel clair", "☀️"],
            1  => ["Principalement clair", "🌤️"],
            2  => ["Partiellement nuageux", "⛅"],
            3  => ["Couvert", "☁️"],
            45 => ["Brouillard", "🌫️"],
            51 => ["Bruine légère", "🌦️"],
            61 => ["Pluie légère", "🌧️"],
            63 => ["Pluie modérée", "🌧️"],
            80 => ["Averses de pluie", "🌦️"],
            95 => ["Orage", "🌩️"],
        ];
        
        $desc_info = $description_map[$weather_code] ?? ["Temps varié", "🌤️"];
        $description = $desc_info[0];
        $icon = $desc_info[1];

        $html_output = "
            <div class='weather-result'> 
                <div class='city-name'><i class='fas fa-map-marker-alt'></i> **{$city_name}**</div>
                <div class='main-info'>
                    <span class='icon' style='font-size: 2.5em;'>{$icon}</span>
                    <span class='temperature'>{$current_temp}°C</span>
                </div>
                <div class='description'>{$description}</div>
                <div class='details'>
                    <p><i class='fas fa-wind'></i> Vent: **{$wind_speed} km/h**</p>
                    <p><i class='fas fa-clock'></i> Ville: Cameroun</p>
                </div>
            </div>
        ";
    }
}

echo $html_output;
?>
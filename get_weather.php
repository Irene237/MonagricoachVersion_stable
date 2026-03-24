<?php
// Fichier : get_weather.php
// Basé sur l'API Open-Meteo (pas de clé requise)
header('Content-Type: text/html; charset=utf-8');

// ==============================================
// === 1. MAPPAGE DES CODES MÉTÉO WMO (Open-Meteo) ===
// ==============================================

// Structure : [WMO Code] => [Description en Français, Icône FontAwesome]
// Ce tableau est essentiel pour traduire le code numérique de l'API en icônes/texte lisible
$wmo_map = [
    // Ciel dégagé
    0 => ["Ciel dégagé", "fas fa-sun"],
    // Principalement dégagé
    1 => ["Principalement dégagé", "fas fa-cloud-sun"],
    2 => ["Partiellement nuageux", "fas fa-cloud-sun"],
    3 => ["Couvert", "fas fa-cloud"],
    
    // Brouillard et dépôt de poussière ou de sable
    45 => ["Brouillard", "fas fa-smog"],
    48 => ["Brouillard givrant", "fas fa-smog"],
    
    // Bruine
    51 => ["Bruine légère", "fas fa-cloud-sun-rain"],
    53 => ["Bruine modérée", "fas fa-cloud-showers-heavy"],
    55 => ["Bruine dense", "fas fa-cloud-showers-heavy"],
    
    // Pluie verglaçante
    56 => ["Bruine verglaçante légère", "fas fa-icicles"],
    57 => ["Bruine verglaçante dense", "fas fa-icicles"],
    
    // Pluie
    61 => ["Pluie légère", "fas fa-cloud-sun-rain"],
    63 => ["Pluie modérée", "fas fa-cloud-showers-heavy"],
    65 => ["Forte pluie", "fas fa-cloud-showers-heavy"],
    
    // Pluie verglaçante
    66 => ["Pluie verglaçante légère", "fas fa-icicles"],
    67 => ["Forte pluie verglaçante", "fas fa-icicles"],
    
    // Neige
    71 => ["Chute de neige légère", "fas fa-snowflake"],
    73 => ["Chute de neige modérée", "fas fa-snowflake"],
    75 => ["Forte chute de neige", "fas fa-snowflake"],
    77 => ["Grains de neige", "fas fa-snowflake"],
    
    // Averses de pluie
    80 => ["Averses légères", "fas fa-cloud-rain"],
    81 => ["Averses modérées", "fas fa-cloud-rain"],
    82 => ["Averses violentes", "fas fa-cloud-showers-heavy"],
    
    // Averses de neige
    85 => ["Averses de neige légères", "fas fa-snowflake"],
    86 => ["Averses de neige fortes", "fas fa-snowflake"],
    
    // Orages
    95 => ["Orage léger à modéré", "fas fa-bolt"],
    96 => ["Orage avec grêle légère", "fas fa-cloud-showers-heavy"],
    99 => ["Orage avec grêle forte", "fas fa-cloud-showers-heavy"],
];


// ==============================================
// === 2. VÉRIFICATION ET APPEL API ===
// ==============================================

// Vérification de l'existence des paramètres GPS
if (!isset($_GET['lat']) || !isset($_GET['lon'])) {
    http_response_code(400);
    echo '<span class="weather-cell-error" title="Paramètres GET lat/lon manquants"><i class="fas fa-times-circle"></i> Paramètres manquants</span>';
    exit;
}

// Récupération sécurisée des coordonnées (le JS doit envoyer des points décimaux)
$latitude = filter_var($_GET['lat'], FILTER_VALIDATE_FLOAT);
$longitude = filter_var($_GET['lon'], FILTER_VALIDATE_FLOAT);

if ($latitude === false || $longitude === false) {
    http_response_code(400);
    echo '<span class="weather-cell-error" title="Coordonnées GPS invalides dans la BDD. Format: 48.8566,2.3522"><i class="fas fa-exclamation-circle"></i> Coords invalides</span>';
    exit;
}

// Vérification de l'activation de cURL (toujours nécessaire pour faire l'appel)
if (!function_exists('curl_init')) {
    http_response_code(500);
    echo '<span class="weather-cell-error" title="L\'extension cURL de PHP est désactivée sur ce serveur."><i class="fas fa-plug"></i> cURL Désactivé</span>';
    exit;
}

// URL de l'API Open-Meteo pour la météo actuelle
$apiUrl = "https://api.open-meteo.com/v1/forecast?latitude={$latitude}&longitude={$longitude}&current_weather=true&temperature_unit=celsius&wind_speed_unit=kmh&timezone=auto";

// Utilisation de cURL pour effectuer la requête HTTP
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout de 10 secondes
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch); // Capture l'erreur réseau cURL
curl_close($ch);


// ==============================================
// === 3. TRAITEMENT DES ERREURS ===
// ==============================================

// Erreur réseau (ex: pas de connexion internet, DNS non résolu)
if ($response === FALSE) {
    http_response_code(500);
    echo '<span class="weather-cell-error" title="Erreur cURL : ' . htmlspecialchars($curlError) . '"><i class="fas fa-wifi"></i> Erreur Réseau</span>';
    exit;
}

// Erreur HTTP (doit être 200 pour réussir)
if ($http_code !== 200) {
    http_response_code(500);
    echo '<span class="weather-cell-error" title="API Open-Meteo a retourné le Code HTTP: ' . $http_code . '"><i class="fas fa-cloud"></i> API Échouée</span>';
    exit;
}

$data = json_decode($response, true);

if (!isset($data['current_weather']) || !isset($data['current_weather']['temperature'])) {
    http_response_code(500);
    echo '<span class="weather-cell-error" title="Structure de données API Open-Meteo incorrecte."><i class="fas fa-frown"></i> Données illisibles</span>';
    exit;
}

// ==============================================
// === 4. FORMATAGE DU RÉSULTAT HTML ===
// ==============================================

$current_temp = round($data['current_weather']['temperature']);
$weather_code = $data['current_weather']['weathercode'];
$wind_speed = round($data['current_weather']['windspeed']);

// Récupération de la description et de l'icône via la table de mappage
$weather_info = $wmo_map[$weather_code] ?? ["Temps inconnu", "fas fa-question-circle"];
$description = $weather_info[0];
$faIcon = $weather_info[1];

// Génération du HTML compact pour la cellule de tableau
echo <<<HTML
<div class="weather-cell-success" title="{$description} | Vent: {$wind_speed} km/h">
    <i class="{$faIcon}"></i> 
    <span>{$current_temp}°C</span>
</div>
HTML;
?>
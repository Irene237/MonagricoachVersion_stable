<?php
session_start();
require_once 'db.php'; 

if (!isset($_SESSION['user_id'])) { 
    header("Location: connexion.php"); 
    exit(); 
} 

$userId = $_SESSION['user_id'];
$points_eau = [];
try {
    $stmt = $pdo->prepare("SELECT id, nom, latitude, longitude, type_source FROM points_eau WHERE id_agriculteur = :userId");
    $stmt->execute(['userId' => $userId]);
    $points_eau = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $error = $e->getMessage(); }

// Mise à jour pour Yaoundé (Coordonnées : 3.8480, 11.5021)
$parcelle_gps = isset($_GET['gps']) ? $_GET['gps'] : '3.8480,11.5021'; 
$coords_p = explode(',', $parcelle_gps);
$p_lat = trim($coords_p[0]); 
$p_lon = trim($coords_p[1]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation Eau | MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    
    <style>
        :root {
            --color-primary-emerald: #06bdbdff; 
            --color-primary-dark: #0cb4b4ff; 
            --color-secondary-gold: #FF8C00;
            --color-card-bg: #FFFFFF; 
            --color-text-dark: #374151;
            --sidebar-width: 260px; 
        }

        body { font-family: 'Poppins', sans-serif; margin: 0; display: flex; height: 100vh; background: #f5f8f5; overflow: hidden; }

        .sidebar {
            width: var(--sidebar-width); background-color: var(--color-card-bg); 
            height: 100vh; position: fixed; left: 0; top: 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; z-index: 1000;
        }
        .sidebar .logo {
            font-family: 'Montserrat', sans-serif; color: var(--color-primary-emerald); 
            font-size: 20px; font-weight: 800; text-align: center; padding: 25px 20px 40px; text-decoration: none;
        }
        .sidebar ul { list-style: none; padding: 0; margin: 0; flex-grow: 1; overflow-y: auto; }
        .sidebar li a {
            display: flex; align-items: center; padding: 12px 25px; color: var(--color-text-dark);
            text-decoration: none; font-size: 14px; font-weight: 500; transition: 0.2s;
        }
        .sidebar li a i { margin-right: 15px; width: 20px; text-align: center; color: #4B5563; }
        .sidebar li a:hover { background-color: rgba(6, 189, 189, 0.1); color: var(--color-primary-emerald); }

        .disconnect-item { padding: 15px; border-top: 1px solid #eee; }
        .disconnect-item button { width: 100%; border: none; background: none; padding: 0; }
        .disconnect-item a {
            display: flex; align-items: center; justify-content: center;
            background-color: var(--color-secondary-gold); color: white;
            padding: 12px; border-radius: 8px; text-decoration: none;
            font-weight: bold; font-size: 13px; transition: 0.3s;
        }
        .disconnect-item a:hover { background-color: #e67e00; transform: translateY(-2px); }

        .main-content { margin-left: var(--sidebar-width); flex-grow: 1; position: relative; }
        #map { height: 100%; width: 100%; z-index: 1; }

        .custom-zoom {
            position: absolute; bottom: 30px; left: 20px; z-index: 1000;
            display: flex; flex-direction: column; gap: 10px;
        }
        .zoom-btn {
            width: 45px; height: 45px; background: var(--color-primary-emerald); color: white;
            border: none; border-radius: 10px; font-size: 20px; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .nav-panel {
            position: absolute; top: 20px; right: 20px; z-index: 1000;
            background: white; padding: 20px; border-radius: 15px; width: 260px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-top: 5px solid var(--color-primary-emerald);
        }
    </style>
</head>
<body>

    <nav class="sidebar">
        <a href="index.php" class="logo"><i class="fas fa-leaf"></i> MonAgriCoach</a>
        <ul>
            <li><a href="farmer_dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a></li>
            <li> <a href="liste_parcelle.php"><i class="fas fa-map-marker-alt"></i> **Parcelles**</a></li> 
            <li> <a href="liste_plantation.php"><i class="fas fa-seedling"></i> Plantations</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i> Cultures</a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> Engrais</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i> Messagerie</a></li>
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i> Verser l'engrais</a></li>
            <li> <a href="liste_st_intrant.php"><i class="fas fa-warehouse"></i> Stock engrais</a></li>
            <li><a href="liste_re.php"><i class="fas fa-chart-bar"></i> Rendement</a></li>
            <li><a href="liste_taches.php"><i class="fas fa-tasks"></i> Tâches</a></li> 

            <li class="disconnect-item">
                <button>
                    <a href="deconnexion.php">
                        <i class="fas fa-sign-out-alt"></i> DÉCONNEXION
                    </a>
                </button>
            </li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="nav-panel">
            <button id="voice-start" style="background:var(--color-primary-emerald); color:white; border:none; width:100%; padding:12px; border-radius:8px; cursor:pointer; font-weight:bold;">
                <i class="fas fa-microphone"></i> Activer la voix
            </button>
            <div id="status" style="margin-top:10px; font-size:13px; color:#555;">Cliquez sur la carte pour ajouter un point.</div>
        </div>

        <div class="custom-zoom">
            <button class="zoom-btn" onclick="map.zoomIn()"><i class="fas fa-plus"></i></button>
            <button class="zoom-btn" onclick="map.zoomOut()"><i class="fas fa-minus"></i></button>
        </div>

        <div id="map"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <script>
        const map = L.map('map', { zoomControl: false }).setView([<?= $p_lat ?>, <?= $p_lon ?>], 16);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(map);

        const homeIcon = L.divIcon({
            html: '<i class="fas fa-home" style="color:#FF8C00; font-size:24px; text-shadow: 0 0 3px white;"></i>',
            className: 'custom-div-icon', iconSize: [30, 30], iconAnchor: [15, 15]
        });
        L.marker([<?= $p_lat ?>, <?= $p_lon ?>], {icon: homeIcon}).addTo(map).bindPopup("<b>Ma Parcelle</b>");

        const points = <?= json_encode($points_eau) ?>;
        points.forEach(p => {
            L.marker([p.latitude, p.longitude]).addTo(map).bindPopup(`<b>${p.nom}</b>`);
        });

        let routingControl = null;

        map.on('click', function(e) {
            const lat = e.latlng.lat.toFixed(6);
            const lon = e.latlng.lng.toFixed(6);
            const form = `
                <div style="padding:5px;">
                    <form action="save_point_eau.php" method="POST">
                        <input type="hidden" name="lat" value="${lat}">
                        <input type="hidden" name="lon" value="${lon}">
                        <input type="text" name="nom" placeholder="Nom du point" required style="width:100%; margin-bottom:10px;">
                        <button type="submit" style="background:var(--color-primary-emerald); color:white; border:none; padding:5px 10px; width:100%; cursor:pointer;">Enregistrer</button>
                    </form>
                </div>`;
            L.popup().setLatLng(e.latlng).setContent(form).openOn(map);
        });

        let lastInstruction = "";

        function parler(texte) {
            if (texte === lastInstruction) return; 
            window.speechSynthesis.cancel();
            const msg = new SpeechSynthesisUtterance(texte);
            msg.lang = 'fr-FR';
            window.speechSynthesis.speak(msg);
            lastInstruction = texte;
            document.getElementById('status').innerText = texte;
        }

        function getDistance(lat1, lon1, lat2, lon2) {
            const R = 6371e3; 
            const dLat = (lat2-lat1) * Math.PI/180;
            const dLon = (lon2-lon1) * Math.PI/180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI/180) * Math.cos(lat2 * Math.PI/180) * Math.sin(dLon/2) * Math.sin(dLon/2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        }

        document.getElementById('voice-start').addEventListener('click', function() {
            if(points.length === 0) return parler("Aucun point d'eau enregistré.");
            
            let plusProche = points[0];
            let minDist = getDistance(<?= $p_lat ?>, <?= $p_lon ?>, points[0].latitude, points[0].longitude);

            points.forEach(p => {
                const d = getDistance(<?= $p_lat ?>, <?= $p_lon ?>, p.latitude, p.longitude);
                if (d < minDist) {
                    minDist = d;
                    plusProche = p;
                }
            });

            parler("Guidage démarré vers le point d'eau le plus proche : " + plusProche.nom);

            if (routingControl) map.removeControl(routingControl);
            routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(<?= $p_lat ?>, <?= $p_lon ?>),
                    L.latLng(plusProche.latitude, plusProche.longitude)
                ],
                lineOptions: { styles: [{ color: '#06bdbd', weight: 6 }] },
                createMarker: function() { return null; },
                addWaypoints: false,
                draggableWaypoints: false
            }).addTo(map);

            navigator.geolocation.watchPosition(pos => {
                const uLat = pos.coords.latitude;
                const uLon = pos.coords.longitude;
                const distRestante = getDistance(uLat, uLon, plusProche.latitude, plusProche.longitude);
                
                if (distRestante < 15) {
                    parler("Vous êtes arrivé à " + plusProche.nom);
                    if (routingControl) map.removeControl(routingControl);
                } else {
                    parler("Continuez. Encore " + Math.round(distRestante) + " mètres.");
                }
            }, err => console.error(err), { enableHighAccuracy: true });
        });
    </script>
</body>
</html>
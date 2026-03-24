<?php
// Démarre la session au tout début du script
session_start();
// Inclut le fichier de connexion à la base de données (db.php)
// Assurez-vous que ce fichier initialise la variable $pdo (connexion PDO).
require_once 'db.php'; 

// --- Bloc de Vérification de Connexion ---
if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) { 
    // Redirige si l'utilisateur n'est pas connecté
    header("Location: connexion.php"); 
    exit(); 
} 

$userId = $_SESSION['user_id'];
$parcelles = [];
$error_message = null; // Initialisation du message d'erreur

try{
    // Récupération des parcelles pour l'utilisateur connecté (agriculteur)
    $sql = "SELECT p.id, p.nom_parcelle, p.superficie_ha, p.localisation_gps, p.typ_sol, p.date_creation, p.statut, u.nom AS nom_agriculteur 
            FROM parcelle AS p 
            JOIN utilisateur AS u ON p.id_agriculteur_proprietaire = u.id 
            WHERE p.id_agriculteur_proprietaire = :userId 
            ORDER BY p.nom_parcelle";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $parcelles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Gestion d'erreur de base de données
    $error_message = "Erreur de récupération des parcelles : " . $e->getMessage();
}

// Prépare les données PHP des parcelles pour être utilisées en JavaScript
// Nécessaire pour les appels asynchrones à l'API météo
$parcelles_js = [];
foreach ($parcelles as $p) {
    // On passe l'ID et les coordonnées GPS pour la requête météo
    $parcelles_js[] = [
        'id' => $p['id'],
        'localisation_gps' => $p['localisation_gps']
    ];
}

?>

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Liste des Parcelles | MonAgriCoach</title> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- PALETTE OPTIMISÉE --- */
        :root {
            --color-primary-emerald: #06bdbdff; /* Vert Émeraude Vif */
            --color-primary-dark: #0cb4b4ff; 
            
            --color-secondary-gold: #FF8C00; /* Orange Vif (Déconnexion) */
            --color-secondary-gold-hover: #E37D00;

            --color-accent-danger: #ef4444; /* Rouge pour les erreurs/danger */
            
            --color-heading: #111827; 
            --color-card-bg: #FFFFFF; 
            --color-light-bg: #F5F8F5; 
            --color-text-dark: #374151;
            --color-text-medium: #4B5563; 
            
            --color-disconnect-bg: var(--color-secondary-gold); 

            --font-main: 'Poppins', sans-serif;
            --font-heading: 'Montserrat', sans-serif;
            --sidebar-width: 260px; 
        }

        body {
            font-family: var(--font-main);
            margin: 0;
            padding: 0;
            background-color: var(--color-light-bg);
            color: var(--color-text-dark);
            display: flex; 
            min-height: 100vh;
            overflow-x: hidden; 
        }

        /* --- BARRE LATÉRALE (SIDEBAR) --- */
        .sidebar {
           width: var(--sidebar-width); 
            background-color: var(--color-card-bg); 
            padding: 25px 0;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }

        .sidebar .logo {
            font-family: var(--font-heading);
            color: var(--color-primary-emerald); 
            font-size: 20px;
            font-weight: 800; 
            text-align: center;
            padding: 0 20px 40px 20px; 
            text-decoration : none;
        }
        .sidebar .logo i {
            color: var(--color-primary-emerald);
            font-size: 28px;
            margin-right: 5px;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .sidebar li a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: var(--color-text-dark);
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.2s ease-in-out; 
            border-left: 0px solid transparent;
        }

        .sidebar li a i {
            margin-right: 15px; 
            font-size: 18px;
            color: var(--color-text-medium);
            width: 25px; 
            text-align: center;
        }
        
        .sidebar li a:hover {
            background-color: #f5f8f8ff;
            color: var(--color-primary-dark);
        }

        /* Lien Actif : Parcelle */
        .sidebar li a[href="liste_parcelle.php"] {
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-emerald); 
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald); 
        }
        .sidebar li a[href="liste_parcelle.php"] i {
             color: var(--color-primary-emerald); 
        }
        
        /* Bouton Déconnexion */
        .sidebar li.disconnect-item {
            margin-top: auto; 
            padding: 25px; 
        }
        
        .sidebar li.disconnect-item button { 
             border: none; 
             padding: 0; 
             background: none; 
             width: 100%; 
        }

        .sidebar li.disconnect-item button a {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            background-color: var(--color-disconnect-bg); 
            color: var(--color-card-bg); 
            padding: 12px 20px;
            border-radius: 8px; 
            font-size: 15px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(255, 140, 0, 0.4); 
            transition: all 0.3s; 
            text-decoration: none;
            border-left: none; 
        }
        .sidebar li.disconnect-item button a:hover { 
              background-color: var(--color-secondary-gold-hover);
              box-shadow: 0 6px 20px rgba(255, 140, 0, 0.6);
        } 
        
        /* --- CONTENU PRINCIPAL --- */
        .main-content {
            margin-left: var(--sidebar-width); 
            padding: 50px 40px; 
            flex-grow: 1;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px; 
            padding-bottom: 20px;
            border-bottom: 1px solid #E5E7EB;
        }

        .header h1 {
            font-family: var(--font-heading);
            font-weight: 900; 
            color: var(--color-heading); 
            font-size: 35px; 
            margin: 0;
            line-height: 1.1;
        }
        
        /* Bouton Ajouter (Émeraude) */
        .add-new-link {
            background-color: var(--color-primary-emerald); 
            color: var(--color-card-bg); 
            padding: 12px 25px; 
            border-radius: 8px;
            font-weight: 600; 
            font-size: 16px;
            text-decoration: none;
            transition: background-color 0.2s ease, box-shadow 0.2s;
            box-shadow: 0 4px 12px rgba(6, 189, 189, 0.4); 
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .add-new-link:hover { 
              background-color: var(--color-primary-dark); 
              box-shadow: 0 6px 15px rgba(6, 189, 189, 0.6);
        }

        .content-container {
            background: var(--color-card-bg);
            padding: 30px;
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
        
        .content-container h2 {
            font-family: var(--font-heading); 
            font-weight: 700;
            font-size: 24px; 
            color: var(--color-heading);
            margin-top: 0;
            margin-bottom: 25px;
        }

        /* --- Formulaire de Recherche (Améliorations) --- */
        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            align-items: center;
            border: 1px solid #E5E7EB; 
            padding: 5px;
            border-radius: 10px;
            background-color: var(--color-light-bg);
        }

        .search-form input[type="text"] {
            flex-grow: 1;
            padding: 10px;
            border: none; 
            border-radius: 6px;
            font-size: 15px;
            background-color: var(--color-card-bg); 
        }
        .search-form button {
            padding: 10px 18px; 
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: background-color 0.2s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .search-form button[type="submit"] {
            background-color: var(--color-primary-emerald); 
            color: var(--color-card-bg); 
            box-shadow: 0 2px 5px rgba(6, 189, 189, 0.2);
        }
        .search-form button[type="button"] {
            background-color: #D1D5DB; 
            color: var(--color-text-dark);
        }
        
        /* --- Tableau des Parcelles --- */
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            min-width: 1100px; /* Augmenté pour la colonne Météo */
        }

        thead th {
            text-align: left;
            padding: 18px 15px;
            color: var(--color-heading);
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: var(--color-card-bg);
            border-bottom: 3px solid var(--color-primary-emerald);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        tbody tr:nth-child(even) { 
            background-color: #FAFAFA;
        }
        
        tbody tr:hover {
            background-color: #E6F7F7; 
        }
        
        tbody td {
            padding: 15px;
            font-size: 14px;
            color: var(--color-text-dark);
            vertical-align: middle;
            border-bottom: 1px solid #F0F0F0; 
        }

        /* Statut */
        td.statut-cell span {
            padding: 6px 12px;
            border-radius: 15px; 
            font-size: 12px;
            font-weight: 600;
        }
        
        .statut-actif {
            color: #15803d; 
            background-color: #dcfce7; 
        }
        .statut-inactif {
            color: #b45309; 
            background-color: #fffbeb; 
        }
        
        /* --- Localisation GPS --- */
        .gps-location {
            font-family: monospace;
            font-size: 12px;
            color: var(--color-text-medium);
        }

        /* --- Actions --- */
        .table-actions {
            white-space: nowrap;
            display: flex;
            gap: 8px; 
            align-items: center;
        }

        .table-actions a, .table-actions button {
            width: 34px; 
            height: 34px; 
            border-radius: 50%; 
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            padding: 0;
            font-size: 15px;
        }
        
        /* Modifier */
        .table-actions a[href*="modifier_parcelle"] {
            background-color: var(--color-primary-emerald); 
            color: var(--color-card-bg);
        }
        .table-actions a[href*="modifier_parcelle"]:hover {
            background-color: var(--color-primary-dark);
            transform: scale(1.05); 
        }
        
        /* Supprimer */
        .delete-button {
            background-color: var(--color-secondary-gold); 
            color: var(--color-card-bg); 
            box-shadow: 0 2px 5px rgba(255, 140, 0, 0.3);
        }
        .delete-button:hover {
            background-color: var(--color-secondary-gold-hover);
            transform: scale(1.05); 
        }
        
        /* --- Message Aucune Donnée --- */
        .content-container > p {
            text-align: center;
            padding: 40px;
            font-size: 16px;
            color: var(--color-text-medium);
            background-color: var(--color-light-bg);
            border-radius: 8px;
            margin-top: 20px;
            font-weight: 500;
            border: 1px dashed var(--color-primary-emerald); 
        }

        /* --- Message d'erreur PHP --- */
        .error-php-message {
            color: var(--color-accent-danger); 
            background-color: #FEE2E2; 
            padding: 15px; 
            border-radius: 8px; 
            font-weight: 600; 
            margin-bottom: 30px; 
            border: 1px solid var(--color-accent-danger);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* --- Style Météo PAR PARCELLE (Nouveau) --- */
        .weather-cell-container {
            font-size: 13px;
            color: var(--color-text-medium);
            text-align: center;
            min-width: 100px; 
        }
        
        .weather-cell-error, .weather-cell-loading {
            font-size: 12px;
            color: #b45309; 
            background-color: #fffbeb; 
            padding: 4px 8px;
            border-radius: 5px;
            display: inline-block;
        }
        
        .weather-cell-error i {
            margin-right: 5px;
        }
        /* Style pour le résultat météo réussi (à adapter dans get_weather.php) */
        .weather-cell-success {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            justify-content: center;
        }
        .weather-cell-success i {
            font-size: 18px;
            color: var(--color-primary-dark);
        }

    </style>
</head>

<body> 
    <nav class="sidebar">
        <a href="index.php" class="logo">
            <i class="fas fa-leaf"></i> MonAgriCoach
        </a>
        
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
        <div class="header">
            <h1>Gestion des Parcelles</h1>
            <a href="parcelle.php" class="add-new-link">
                <i class="fas fa-plus-circle"></i> Ajouter 
            </a>
        </div>
        
        <?php if (isset($error_message) && !empty($error_message)): ?>
            <p class="error-php-message">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?>
            </p>
        <?php endif; ?>

        <div class="content-container">
            <h2>Liste de vos parcelles enregistrées</h2>
            
            <form action="recherche_parcelle.php" method="GET" class="search-form">
                <label for="search_query" style="display:none;">Rechercher une parcelle</label>
                <input type="text" name="recherche" placeholder="Recherche par nom de parcelle ou type de sol..." id="search_query">
                
                <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
                <button type="button" onclick="window.location.href='liste_parcelle.php'"><i class="fas fa-sync-alt"></i> Rénitialiser</button>
            </form>

            <?php if (count($parcelles) > 0): ?>
                <div class="table-responsive">
                <table>
                    <thead>
                        <tr> 
                            <th>Nom Parcelle</th> 
                            <th>Superficie</th> 
                            <th>Localisation</th> 
                            <th>Type Sol</th> 
                            <th>Date</th> 
                            <th>Statut</th> 
                            <th>Météo</th> <th>Agriculteur</th> 
                            <th>Action</th> 
                        </tr>
                    </thead>
                    <tbody>
    <?php foreach($parcelles as $parcelle ): 
        $statut_class = strtolower($parcelle['statut']) === 'actif' ? 'statut-actif' : 'statut-inactif';
        $gps_coords = htmlspecialchars($parcelle['localisation_gps']);
    ?>
        <tr>
            <td><?= htmlspecialchars($parcelle['nom_parcelle']); ?></td>
            <td><strong><?= htmlspecialchars(number_format($parcelle['superficie_ha'], 2, '.', '')); ?></strong> ha</td>
            <td class="gps-location"><?= $gps_coords; ?></td>
            <td><?= htmlspecialchars($parcelle['typ_sol']); ?></td>
            <td><?= htmlspecialchars(date('d/m/Y', strtotime($parcelle['date_creation']))); ?></td>
            <td class="statut-cell">
                <span class="<?= $statut_class; ?>">
                    <?= htmlspecialchars($parcelle['statut']); ?>
                </span>
            </td>
            
            <td class="weather-cell-container">
                <span id="weather-<?= $parcelle['id']; ?>" class="weather-cell-loading">
                    <i class="fas fa-sync fa-spin"></i> Chargement...
                </span>
            </td>
            
            <td><?= htmlspecialchars($parcelle['nom_agriculteur']); ?></td>
            <td class="table-actions">
                <a href="carte_points_eau.php?id=<?= $parcelle['id']; ?>&gps=<?= urlencode($parcelle['localisation_gps']); ?>" 
                   title="Points d'eau" style="background-color: #2c5282; color: white;">
                    <i class="fas fa-tint"></i>
                </a>
                
                <a href="modifier_parcelle.php?id=<?= $parcelle['id']; ?>" title="Modifier">
                    <i class="fas fa-edit"></i>
                </a>

                <button class="delete-button" 
                        onclick="if(confirm('Voulez-vous vraiment supprimer cette parcelle ?')) { window.location.href='supprimer_parcelle.php?id=<?= $parcelle['id']; ?>'; }" 
                        title="Supprimer">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr> 
    <?php endforeach; ?>
</tbody>
                </table>
                </div> 
            <?php else: ?> 
                <p><i class="fas fa-info-circle"></i> Aucune parcelle trouvée pour votre compte. Cliquez sur "**Ajouter Parcelle**" pour commencer.</p> 
            <?php endif; ?>
        </div> 
    </div>

    <script>
        // Récupère les IDs et coordonnées GPS des parcelles injectées par PHP
        const parcellesData = <?php echo json_encode($parcelles_js); ?>;

        /**
         * Appelle le script PHP météo pour une parcelle donnée et insère le résultat.
         * @param {number} id - L'ID de la parcelle.
         * @param {string} gps - Les coordonnées GPS (lat,lon). Ex: "48.8566,2.3522"
         */
        function fetchWeather(id, gps) {
            const container = document.getElementById(`weather-${id}`);
            if (!container) return; // Si l'élément n'existe pas, on arrête.

            // Sépare lat et lon
            const coords = gps.split(',');
            if (coords.length !== 2 || isNaN(parseFloat(coords[0])) || isNaN(parseFloat(coords[1]))) {
                container.innerHTML = `<span class="weather-cell-error"><i class="fas fa-exclamation-circle"></i> Coordonnées GPS invalides</span>`;
                return;
            }
            const lat = coords[0].trim();
            const lon = coords[1].trim();

            // Appel du fichier PHP dédié à la météo
            fetch(`get_weather.php?lat=${lat}&lon=${lon}`) 
                .then(response => {
                    if (!response.ok) {
                        // Traite les erreurs HTTP (404, 500, etc.)
                        throw new Error(`Erreur HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(data => {
                    // Le contenu HTML de la météo est inséré
                    container.innerHTML = data;
                    container.classList.remove('weather-cell-loading');
                })
                .catch(error => {
                    // Affiche une erreur si l'appel API échoue
                    container.innerHTML = `
                        <span class="weather-cell-error" title="Détail: ${error.message}">
                            <i class="fas fa-bolt"></i> API Échouée
                        </span>`;
                    container.classList.remove('weather-cell-loading');
                    console.error(`Erreur Météo pour parcelle ${id}:`, error);
                });
        }

        /**
         * Lance la requête météo pour toutes les parcelles récupérées.
         */
        function fetchWeatherForParcels() {
            if (parcellesData.length === 0) return;

            parcellesData.forEach(parcelle => {
                fetchWeather(parcelle.id, parcelle.localisation_gps);
            });
        }

        // Démarre la récupération de la météo au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            fetchWeatherForParcels();
        });
    </script>
</body> 
</html>
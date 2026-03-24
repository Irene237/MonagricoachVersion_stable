<?php
// Fichier : recherche_parcelle.php
// Démarre la session au tout début du script
session_start();
// Inclut le fichier de connexion à la base de données (db.php)
require_once 'db.php'; 

// --- Bloc de Vérification de Connexion (Très important !) ---
if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) { 
    header("Location: connexion.php"); 
    exit(); 
} 

$userId = $_SESSION['user_id']; // ID de l'utilisateur pour ne chercher que ses parcelles
$parcelles = [];
$error_message = null;
$conditions = [];
// Paramètre pour filtrer par l'utilisateur connecté
$params = [':userId' => $userId]; 

// La requête de base cible SEULEMENT les parcelles de l'utilisateur connecté
// Ajout de la colonne 'description_courte' si elle existe dans votre table pour plus de détails
$query_string = "
    SELECT p.id, p.nom_parcelle, p.superficie_ha, p.localisation_gps, p.typ_sol, p.date_creation, p.statut, u.nom AS nom_agriculteur 
    FROM parcelle AS p 
    JOIN utilisateur AS u ON p.id_agriculteur_proprietaire = u.id 
    WHERE p.id_agriculteur_proprietaire = :userId 
";

// --- Ajout des conditions de recherche ---

// 1. Recherche générique par nom, type de sol (ou nom de l'agriculteur)
if (!empty($_GET['recherche'])) {
    $termeRecherche = '%' . $_GET['recherche'] . '%';
    // La recherche doit inclure 'p.id_agriculteur_proprietaire = :userId' pour la sécurité
    $conditions[] = "(p.nom_parcelle LIKE :termeRecherche OR p.typ_sol LIKE :termeRecherche OR u.nom LIKE :termeRecherche)";
    $params[':termeRecherche'] = $termeRecherche;
}

// 2. Recherche par superficie
if (!empty($_GET['superficie_ha']) && is_numeric($_GET['superficie_ha'])) {
    $conditions[] = "p.superficie_ha = :superficie_ha";
    $params[':superficie_ha'] = (float)$_GET['superficie_ha'];
}

// 3. Recherche par date
if (!empty($_GET['date_creation'])) {
    $conditions[] = "p.date_creation = :date_creation";
    $params[':date_creation'] = $_GET['date_creation'];
}

// Construction de la chaîne de requête finale
if (!empty($conditions)) {
    $query_string .= " AND " . implode(" AND ", $conditions);
}

// Ajout de l'ordre par défaut
$query_string .= " ORDER BY p.nom_parcelle";


try {
    $stmt = $pdo->prepare($query_string);
    $stmt->execute($params);
    $parcelles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error_message = "Erreur de récupération : " . $e->getMessage();
}

/**
 * Fonction pour appeler la météo de manière asynchrone pour une cellule de tableau.
 * Cette fonction est identique à celle de liste_parcelle.php
 * @param string $lat_lon Coordonnées GPS (ex: 48.8566,2.3522)
 * @param int $parcelleId L'ID de la parcelle
 * @return string Le code HTML pour le conteneur météo vide
 */
function displayWeatherCell($lat_lon, $parcelleId) {
    // Sépare Latitude et Longitude
    $coords = explode(',', $lat_lon);
    $lat = trim($coords[0]);
    $lon = trim($coords[1]);

    // L'ID du conteneur où le JS va injecter la météo
    $cell_id = "weather-{$parcelleId}"; 

    // On retourne la cellule vide avec un spinner et les attributs data
    // pour que le JavaScript la remplisse après le chargement de la page
    return <<<HTML
    <td id="{$cell_id}" class="weather-cell-container" data-lat="{$lat}" data-lon="{$lon}" style="width: 120px;">
        <span class="weather-cell-loading" title="Chargement météo...">
            <i class="fas fa-spinner fa-spin"></i>
        </span>
    </td>
    HTML;
}

?>

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Résultats de Recherche | MonAgriCoach</title> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- Styles de base et barre latérale (Identiques à liste_parcelle.php) --- */
        :root {
            --color-primary-emerald: #06bdbdff; 
            --color-primary-dark: #0cb4b4ff; 
            --color-secondary-gold: #FF8C00; 
            --color-secondary-gold-hover: #E37D00;
            --color-accent-danger: #ef4444; 
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

        /* --- SIDEBAR --- */
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
        .sidebar li a[href*="parcelle.php"] { /* Utilisez *="parcelle.php" pour inclure recherche_parcelle.php */
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-emerald); 
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald); 
        }
        .sidebar li a[href*="parcelle.php"] i {
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

        /* --- Formulaire de Recherche (Identique) --- */
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
        /* Bouton de réinitialisation qui renvoie à liste_parcelle.php */
        .search-form button.reset-button { 
            background-color: #D1D5DB; 
            color: var(--color-text-dark);
        }
        
        /* --- Tableau des Parcelles (Identique) --- */
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            min-width: 1000px; /* Augmenté pour la colonne météo */
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
        
        /* Localisation GPS */
        .gps-location {
            font-family: monospace;
            font-size: 12px;
            color: var(--color-text-medium);
            white-space: nowrap; /* Empêche le retour à la ligne des coordonnées */
        }

        /* --- Cellule Météo Styles --- */
        .weather-cell-container {
            text-align: center;
        }
        .weather-cell-loading {
            font-size: 1.2em;
            color: var(--color-primary-emerald);
        }
        /* Style pour le résultat météo injecté par AJAX (doit être compatible avec get_weather.php) */
        .weather-cell-success {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 1.1em;
            font-weight: 600;
            color: var(--color-heading);
        }
        .weather-cell-success i {
            color: var(--color-primary-dark);
            font-size: 1.3em;
        }
        /* Styles d'erreur météo */
        .weather-cell-error {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 12px;
            color: var(--color-accent-danger);
            line-height: 1.2;
            cursor: help;
        }
        .weather-cell-error i {
            font-size: 1.3em;
            margin-bottom: 3px;
        }


        /* --- Actions (Icônes) --- */
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
        
        /* Modifier (Vert Émeraude) */
        .table-actions a[href*="modifier_parcelle"] {
            background-color: var(--color-primary-emerald); 
            color: var(--color-card-bg);
        }
        .table-actions a[href*="modifier_parcelle"]:hover {
            background-color: var(--color-primary-dark);
            transform: scale(1.05); 
        }
        
        /* Supprimer (Orange) */
        .delete-button {
            background-color: var(--color-secondary-gold); 
            color: var(--color-card-bg); 
            box-shadow: 0 2px 5px rgba(255, 140, 0, 0.3);
        }
        .delete-button:hover {
            background-color: var(--color-secondary-gold-hover);
            transform: scale(1.05); 
        }
        
        /* Message Aucune Donnée */
        .no-data-message {
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

        /* Message d'erreur PHP */
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
            <li> <a href="liste_plantation.php"><i class="fas fa-seedling"></i> plantations</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i>  Cultures</a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> Engrais</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i> Messagerie</a></li>
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i>Verser l'engrais</a></li>
            <li> <a href="liste_st_intrant.php"><i class="fas fa-warehouse"></i> stock engrais</a></li>
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
            <h1>Résultats de Recherche des Parcelles</h1>
            <a href="parcelle.php" class="add-new-link">
                <i class="fas fa-plus-circle"></i> Ajouter Parcelle
            </a>
        </div>
        
        <?php if (isset($error_message) && !empty($error_message)): ?>
            <p class="error-php-message">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?>
            </p>
        <?php endif; ?>

        <div class="content-container">
            <h2>Parcelles correspondant à votre recherche</h2>
            
            <form action="recherche_parcelle.php" method="GET" class="search-form">
                <label for="search_query" style="display:none;">Rechercher une parcelle</label>
                <input type="text" name="recherche" placeholder="Recherche par nom de parcelle, type de sol ou agriculteur..." id="search_query" value="<?= htmlspecialchars($_GET['recherche'] ?? '') ?>">
                
                <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
                <button type="button" class="reset-button" onclick="window.location.href='liste_parcelle.php'"><i class="fas fa-sync-alt"></i> Rénitialiser</button>
            </form>

            <?php if (count($parcelles) > 0): ?>
                <div class="table-responsive">
                <table>
                    <thead>
                        <tr> 
                            <th>Parcelle</th> 
                            <th>Superficie</th> 
                            <th>Localisatio</th> 
                            <th>Sol</th> 
                            <th>Météo</th> <th>Date</th> 
                            <th>Statut</th> 
                            <th>Agriculteur</th> 
                            <th>Action</th> 
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($parcelles as $parcelle ): 
                            $statut_class = strtolower($parcelle['statut']) === 'actif' ? 'statut-actif' : 'statut-inactif';
                            $gps_coords = htmlspecialchars($parcelle['localisation_gps']);
                            $parcelle_id = $parcelle['id'];
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($parcelle['nom_parcelle']); ?></td>
                                <td>**<?= htmlspecialchars(number_format($parcelle['superficie_ha'], 2, '.', '')); ?>** ha</td>
                                <td class="gps-location"><?= $gps_coords; ?></td>
                                <td><?= htmlspecialchars($parcelle['typ_sol']); ?></td>
                                
                                <?= displayWeatherCell($parcelle['localisation_gps'], $parcelle_id); ?>
                                
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($parcelle['date_creation']))); ?></td>
                                <td class="statut-cell">
                                    <span class="<?= $statut_class; ?>">
                                        <?= htmlspecialchars($parcelle['statut']); ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($parcelle['nom_agriculteur']); ?></td>
                                <td class="table-actions">
                                    <a href="modifier_parcelle.php?id=<?= $parcelle_id; ?>" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="supprime_parcelle.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette parcelle? Cette action est irréversible.');" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $parcelle_id; ?>" >
                                        <button type="submit" title="Supprimer" class="delete-button">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr> 
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div> 
            <?php else: ?> 
                <p class="no-data-message"><i class="fas fa-info-circle"></i> Aucune parcelle trouvée pour ces critères de recherche.</p> 
            <?php endif; ?>
        </div> 
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Séléctionne toutes les cellules météo à charger
            const weatherCells = document.querySelectorAll('.weather-cell-container');

            weatherCells.forEach(cell => {
                const lat = cell.dataset.lat;
                const lon = cell.dataset.lon;
                const cellId = cell.id;

                // Vérifie si les coordonnées sont valides (pas vide, etc.)
                if (lat && lon) {
                    // Effectue l'appel AJAX
                    fetch(`get_weather.php?lat=${lat}&lon=${lon}`)
                        .then(response => {
                            // Si la réponse n'est pas OK (ex: 400 Bad Request),
                            // on traite quand même le corps pour le message d'erreur
                            if (!response.ok && response.status !== 400 && response.status !== 500) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.text();
                        })
                        .then(html => {
                            // Injecte le HTML retourné par get_weather.php (succès ou erreur)
                            document.getElementById(cellId).innerHTML = html;
                        })
                        .catch(error => {
                            console.error("Error fetching weather:", error);
                            document.getElementById(cellId).innerHTML = `
                                <span class="weather-cell-error" title="Erreur JS : Voir console">
                                    <i class="fas fa-exclamation-triangle"></i> Erreur Client
                                </span>
                            `;
                        });
                } else {
                    document.getElementById(cellId).innerHTML = `
                        <span class="weather-cell-error" title="Coordonnées GPS absentes ou mal formatées.">
                            <i class="fas fa-map-marker-alt"></i> Coords Invalides
                        </span>
                    `;
                }
            });
        });
    </script>
</body>
</html>
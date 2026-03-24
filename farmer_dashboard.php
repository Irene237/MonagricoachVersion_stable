<?php
// Fichier : farmer_dashboard.php
session_start();
require_once 'db.php'; 

if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) { 
    header("Location: connexion.php"); 
    exit(); 
} 

$message ='';
$user_id = $_SESSION['user_id'];

// --- RÉCUPÉRATION DES DONNÉES UTILISATEUR (NOM, PRENOM, PHOTO) ---
try {
    $stmt_user = $pdo->prepare("SELECT photo, nom, prenom FROM utilisateur WHERE id = ?");
    $stmt_user->execute([$user_id]);
    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);
    
    // Chemin de la photo
    $photo_nom = $user_data['photo'] ?? '';
    if (!empty($photo_nom) && file_exists("uploads/" . $photo_nom)) {
        $photo_path = "uploads/" . $photo_nom;
    } else {
        // Avatar par défaut si le fichier n'existe pas ou est vide
        $photo_path = "https://ui-avatars.com/api/?name=" . urlencode($user_data['nom'] ?? 'U') . "+" . urlencode($user_data['prenom'] ?? '') . "&background=0ab9b1&color=fff";
    }
} catch (PDOException $e) {
    $photo_path = "https://via.placeholder.com/80";
}
$message ='';
$user_id = $_SESSION['user_id'];
$cultures_actives=0;
$taches_en_attente=[]; 
$nombre_taches_en_attente=0; 
$rendement_total_actuels=0;
$rendement_total_an_dernier=0;
$rendement_nets_actuels=0;
$rendement_nets_an_dernier=0;
$pourcentage_rendement_total=0;
$pourcentage_rendement_nets=0;

// --- Données pour le Graphique (Ces données DOIVENT VENIR de votre BDD) ---
// Simulez des données de rendement mensuel si la requête est complexe ou absente
$rendement_total_mensuel = [500, 650, 800, 750, 900, 1100, 1050, 1200, 1350, 1500, 1400, 1600];
$rendement_nets_mensuel = [300, 400, 550, 500, 650, 800, 750, 900, 1050, 1150, 1000, 1250];

try{
    // --- Requêtes de Base de Données pour les KPI ---
    
    // 1. Nombre de cultures actives
    $stmt = $pdo->prepare("SELECT count(*) FROM culture WHERE statut ='actif' AND id_agriculteur = ?");
    $stmt->execute([$user_id]);
    $cultures_actives = $stmt->fetchColumn();

    // 2. Tâches en attente (Liste des 5 premières)
    $sql_taches = "SELECT id, titre, date_debut, date_fin, priorite 
                    FROM tache 
                    WHERE statut ='en_attente' AND id_agriculteur = ? 
                    ORDER BY priorite DESC, date_debut ASC LIMIT 5";
    $stmt = $pdo->prepare($sql_taches);
    $stmt->execute([$user_id]);
    $taches_en_attente = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $nombre_taches_en_attente = count($taches_en_attente); 

    // 3. Calculs de Rendement (Année Actuelle et Année Précédente)
    $annee_actuelle = date('Y');
    $annee_derniere = date('Y') - 1;

    // ATTENTION: La colonne rendement_nets_actuels dans Observation n'est pas standard. J'utilise un alias pour simuler, ajustez si nécessaire.
    $stmt = $pdo->prepare("SELECT SUM(rendement) AS rendement, SUM(quantite- cout) AS rendement_nets_actuels FROM observation WHERE id_agriculteur = ? AND YEAR(date_re) = ?");
    
    $stmt->execute([$user_id, $annee_actuelle]);
    $data_actuelle = $stmt->fetch(PDO::FETCH_ASSOC);
    $rendement_total_actuels = $data_actuelle['rendement'] ?? 0;
    $rendement_nets_actuels = $data_actuelle['rendement_nets_actuels'] ?? 0;

    $stmt->execute([$user_id, $annee_derniere]);
    $data_an_dernier = $stmt->fetch(PDO::FETCH_ASSOC);
    $rendement_total_an_dernier = $data_an_dernier['rendement'] ?? 0;
    $rendement_nets_an_dernier = $data_an_dernier['rendement_nets_actuels'] ?? 0;

    // Calcul des pourcentages de tendance
    if ($rendement_total_an_dernier > 0) {
        $pourcentage_rendement_total = (($rendement_total_actuels - $rendement_total_an_dernier) / $rendement_total_an_dernier) * 100;
    } else {
        $pourcentage_rendement_total = ($rendement_total_actuels > 0) ? 100 : 0;
    }

    if ($rendement_nets_an_dernier > 0) {
        $pourcentage_rendement_nets = (($rendement_nets_actuels - $rendement_nets_an_dernier) / $rendement_nets_an_dernier) * 100;
    } else {
        $pourcentage_rendement_nets = ($rendement_nets_actuels > 0) ? 100 : 0;
    }

} catch (PDOException $e) {
    error_log("Erreur de BDD dans farmer_dashboard: " .$e->getMessage());
    $message = "Une erreur de base de données est survenue. Veuillez contacter le support.";
}

// --- Suppression de la logique OpenWeatherMap (API Statique) ---

$current_time = date('H:i');

?> 


<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Tableau de Bord Agricole - MonAgriCoach</title> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.0.0"></script>
    
    <style>
        /* --- PALETTE & GÉNÉRAL --- */
        :root {
            --color-primary-emerald: #0ab9b1ff; /* Émeraude Vif */
            --color-primary-dark: #008080; /* Teal/Vert foncé */
            --color-secondary-gold: #fd9f07ff; /* Or Doux */
            
            --color-secondary-beige-light: #f5f8f8ff; 
            --color-secondary-beige-hover: #e0f8f8; 

            --color-heading: #1F2937; 
            --color-accent-warning: #fc8e08ff; 
            --color-accent-danger: #df1313ff; 

            --color-light-bg: #F8F9FA; 
            --color-card-bg: #FFFFFF; 
            --color-text-dark: #1F2937; 
            --color-text-medium: #4B5563; 
            --color-text-light: #9CA3AF; 
            --color-success: #34A853; 

            --font-main: 'Poppins', sans-serif;
            --font-heading: 'Montserrat', sans-serif;
            --sidebar-width: 260px; 
            --border-radius-lg: 15px; 
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
                /* --- BARRE LATÉRALE (SIDEBAR) --- */
     
        .sidebar {
            width: var(--sidebar-width); background-color: var(--color-card-bg); 
            height: 100vh; position: fixed; box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            display: flex; flex-direction: column; z-index: 1000;
        }

        /* --- BLOC PHOTO DE PROFIL SIDEBAR --- */
        .sidebar-profile {
            text-align: center;
           
            border-bottom: 1px solid #f0f0f0;
            margin-bottom: 10px;
        }
        .profile-img-container {
            width: 85px;
            height: 85px;
            border-radius: 50%;
            margin: 0 auto 10px;
            border: 3px solid var(--color-primary-emerald);
            overflow: hidden;
            background-color: #eee;
        }
        .profile-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .profile-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--color-text-dark);
        }

        .sidebar .logo {
            font-family: var(--font-heading); color: var(--color-primary-emerald); 
            font-size: 20px; font-weight: 800; text-align: center; padding: 20px; text-decoration: none; display: block;
        }

        .sidebar ul { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .sidebar li a {
            display: flex; align-items: center; padding: 6px 25px;
            color: var(--color-text-dark); text-decoration: none; font-size: 15px; font-weight: 500;
        }
        .sidebar li a:hover { background-color: #f5f8f8; color: var(--color-primary-dark); }
        .sidebar li a i { margin-right: 15px; width: 25px; text-align: center; color: var(--color-text-medium); }
        
        /* Bouton Déconnexion */
        .disconnect-item {
            margin-top: auto; 
            padding: 25px; 
        }
        
        .disconnect-item button { 
            border: none; 
            padding: 0; 
            background: none; 
            width: 100%; 
        }

        .disconnect-item button a {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            background-color: var(--color-secondary-gold); 
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
        .disconnect-item button a:hover { 
            background-color: #E37D00;
            box-shadow: 0 6px 20px rgba(255, 140, 0, 0.6);
        } 

        /* --- CONTENU PRINCIPAL --- */
        .main-content {
            margin-left: var(--sidebar-width); 
            padding: 40px; 
            flex-grow: 1;
            min-width: 0; 
        }

        /* En-tête de Bienvenue & Météo */
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: stretch; 
            margin-bottom: 40px; 
            gap: 30px;
        }
        
        /* Bienvenue */
        .welcome-section {
            background: linear-gradient(135deg, var(--color-primary-emerald) 0%, #4cddd7 100%);
            padding: 35px 50px;
            border-radius: var(--border-radius-lg);
            color: #FFFFFF;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            flex-grow: 1;
            position: relative;
            overflow: hidden; 
        }
        
        .welcome-section::after {
            content: "\f560"; 
            font-family: 'Font Awesome 5 Pro';
            font-weight: 900;
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 150px;
            color: rgba(255, 255, 255, 0.1);
            transform: rotate(-10deg);
        }

        .welcome-section h2 { 
            font-family: var(--font-heading);
            font-weight: 800; 
            margin-top: 0; 
            margin-bottom: 5px;
            font-size: 38px; 
        }

        /* MÉTÉO (COMMUN) */
        /* Style pour le conteneur principal */
#weather-result-container {
    padding: 15px;
    border-radius: 12px;
    background-color: var(--color-card-bg); /* Couleur de fond de carte */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    min-height: 150px; /* Pour éviter le saut de mise en page */
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}

/* Style pour le message de chargement */
.loading-message {
    color: var(--color-text-light);
    font-size: 1.1em;
}

/* Style pour les messages d'erreur */
.error-msg-weather {
    color: #ff4d4d; /* Rouge vif */
    font-weight: bold;
    padding: 10px;
}

/* Style du résultat météo */
.weather-result .city-name {
    font-size: 1.2em;
    font-weight: 600;
    color: var(--color-primary-dark);
    margin-bottom: 10px;
}

.weather-result .main-info {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    margin-bottom: 5px;
}

.weather-result .temperature {
    font-size: 3em;
    font-weight: bold;
    color: var(--color-primary-emerald);
}

.weather-result .description {
    font-style: italic;
    color: var(--color-text-medium);
    margin-bottom: 15px;
}

.weather-result .details p {
    margin: 3px 0;
    font-size: 0.9em;
}
        .weather-card {
            background: var(--color-card-bg);
            padding: 25px 30px; 
            border-radius: var(--border-radius-lg);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #E5E7EB; 
            border-top: 4px solid var(--color-secondary-gold); 
            text-align: right;
            min-width: 280px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        /* Styles spécifiques pour le contenu dynamique injecté */
        .weather-card #weather-result-container {
            min-height: 150px; 
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-end; /* Alignement par défaut à droite */
        }

        .weather-card .location {
            font-size: 13px;
            color: var(--color-text-medium);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            font-weight: 500;
            width: 100%;
        }
        .weather-card .location i {
            margin-right: 5px;
            color: var(--color-secondary-gold); 
        }

        .weather-card .temp-icon {
            font-family: var(--font-heading);
            font-size: 52px; 
            font-weight: 900;
            color: var(--color-heading);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            line-height: 1;
            margin-bottom: 5px;
        }
        .weather-card .temp-icon i {
            font-size: 48px;
            margin-left: 15px; 
            margin-right: 0; 
            color: var(--color-secondary-gold); 
        }
        .weather-card .status {
            font-size: 14px;
            font-weight: 600;
            color: var(--color-primary-dark);
            margin-bottom: 10px;
        }
        .weather-card .geo-details {
            font-size: 11px;
            color: var(--color-text-medium);
        }

        /* Messages de chargement et d'erreur pour la météo géolocalisée */
        .loading-message, .error-msg-weather {
            text-align: center;
            width: 100%;
            font-size: 14px;
            color: var(--color-text-medium);
        }
        .loading-message i {
            color: var(--color-secondary-gold);
            margin-bottom: 10px;
            font-size: 24px;
        }
        .error-msg-weather {
            color: var(--color-accent-danger);
            font-weight: 600;
        }
        
        /* --- CARTES DE MÉTRIQUES (CARDS) --- */
        .cards {
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
            gap: 25px; 
            margin-bottom: 40px;
        }

        .card {
            background: var(--color-card-bg);
            border-radius: var(--border-radius-lg);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            border-left: 4px solid transparent; 
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            padding: 25px; 
            position: relative;
        }

        .card:hover {
            transform: translateY(-5px); 
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
            border-left-color: var(--color-primary-emerald); 
        }
        
        .card h4 {
            font-weight: 600;
            font-size: 13px; 
            color: var(--color-text-light); 
            margin-top: 0;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        
        .card .metric-value {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 10px;
        }
        .card .metric-value p {
            font-family: var(--font-heading);
            font-size: 24px; 
            font-weight: 900;
            color: var(--color-primary-dark); 
            margin: 0;
            line-height: 1;
        }
        .card .metric-value i {
            font-size: 40px; 
            opacity: 0.1; 
            color: var(--color-primary-emerald); 
        }
        
        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 10px;
            border-top: 1px solid #F3F4F6; 
            font-size: 12px;
            color: var(--color-text-medium);
            margin-top: 5px;
        }
        
        .trend-up { color: var(--color-success); font-weight: 600; }
        .trend-down { color: var(--color-accent-danger); font-weight: 600; }
        .trend-zero { color: var(--color-text-medium); font-weight: 500; }

        /* --- GRAPHIQUE & TÂCHES --- */
        .dashboard-layout {
            display: flex;
            gap: 25px;
            align-items: stretch; 
        }
        
        .chart-container, .tasks-container {
            background-color: var(--color-card-bg);
            border-radius: var(--border-radius-lg);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #E5E7EB;
            padding: 30px;
            display: flex;
            flex-direction: column;
        }
        
        .chart-canvas-wrapper {
             position: relative;
             height: 300px; 
        }
        
        .chart-container .header, .tasks-container h3 {
            font-family: var(--font-heading);
            margin-top: 0;
            color: var(--color-heading); 
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 15px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            font-size: 18px; 
        }

        /* BOUTON STANDARD */
        .add-new-link, .view-all-button {
            background-color: var(--color-primary-emerald); 
            color: var(--color-card-bg); 
            padding: 8px 15px; 
            border-radius: 20px;
            font-weight: 500; 
            font-size: 13px;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 3px 8px rgba(10, 185, 177, 0.4);
            display: inline-flex; 
            align-items: center;
            gap: 5px;
        }

        .add-new-link:hover, .view-all-button:hover { 
             background-color: var(--color-primary-dark); 
             transform: translateY(-1px);
        } 
        
        /* Liste des Tâches */
        .tasks-list {
            display: flex;
            flex-direction: column;
            gap: 10px; 
        }
        .task-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border-radius: var(--border-radius-lg);
            background-color: var(--color-secondary-beige-light); 
            border-left: 4px solid var(--color-secondary-gold); 
        }

        /* --- STYLE DE L'IA FLOTTANTE --- */
#ai-chat-button {
    position: fixed; bottom: 30px; right: 30px;
    width: 60px; height: 60px;
    background-color: var(--color-primary-emerald);
    color: white; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; cursor: pointer; z-index: 2000;
    box-shadow: 0 4px 15px rgba(10, 185, 177, 0.5);
    transition: transform 0.3s;
}
#ai-chat-button:hover { transform: scale(1.1); }

#ai-chat-window {
    position: fixed; bottom: 100px; right: 30px;
    width: 350px; height: 450px;
    background: white; border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    display: none; flex-direction: column; z-index: 2000;
    overflow: hidden; border: 1px solid #ddd;
}

.chat-header {
    background: var(--color-primary-emerald); color: white;
    padding: 15px; display: flex; justify-content: space-between; align-items: center;
    font-weight: 600;
}

#chat-messages {
    flex-grow: 1; padding: 15px; overflow-y: auto;
    display: flex; flex-direction: column; gap: 10px;
}

.message { padding: 10px 15px; border-radius: 10px; max-width: 85%; font-size: 14px; }
.ai-msg { background: var(--color-secondary-beige-light); align-self: flex-start; color: var(--color-text-dark); }
.user-msg { background: var(--color-primary-emerald); color: white; align-self: flex-end; }

.chat-input-area { padding: 10px; display: flex; border-top: 1px solid #eee; }
.chat-input-area input {
    flex-grow: 1; border: 1px solid #ddd; padding: 8px; border-radius: 5px; outline: none;
}
.chat-input-area button {
    background: var(--color-secondary-gold); border: none; color: white;
    padding: 8px 12px; margin-left: 5px; border-radius: 5px; cursor: pointer;
}

        .task-details strong {
            display: block;
            font-size: 14px; 
            color: var(--color-text-dark);
            font-weight: 600;
            margin-bottom: 2px;
        }
        .task-details small {
            font-size: 11px;
            color: var(--color-text-medium);
        }
        .task-details small i {
            margin-right: 5px;
            font-size: 10px;
            color: var(--color-primary-emerald); 
        }
        
        .task-priority {
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 15px;
            font-weight: 700;
            text-transform: uppercase;
            min-width: 65px; 
            text-align: center;
        }

        /* Couleurs de Priorité */
        .task-priority-high {
            background-color: #FEE2E2; 
            color: var(--color-accent-danger);
            border: 1px solid var(--color-accent-danger);
        }
        .task-priority-medium {
            background-color: #FFF3E0; 
            color: var(--color-accent-warning);
            border: 1px solid var(--color-accent-warning);
        }
        .task-priority-low {
            background-color: #E0F0E0; 
            color: var(--color-primary-dark); 
            border: 1px solid var(--color-primary-dark);
        }

        /* Layout Grid */
        .chart-container { flex: 2; min-width: 60%; }
        .tasks-container { flex: 1; min-width: 320px; max-width: 380px; } 

        /* --- Réactivité (Media Queries) --- */
        @media (max-width: 1200px) {
            .dashboard-layout {
                flex-direction: column;
            }
            .chart-container, .tasks-container {
                min-width: 100%;
                max-width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            body { display: block; }
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }
            .sidebar ul {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
            }
            .sidebar li {
                flex: 1 1 45%; 
                text-align: center;
            }
            .sidebar li a {
                justify-content: center;
                border-left: none;
                border-bottom: 3px solid transparent;
            }
            .sidebar li a.active {
                border-left: none;
                border-bottom: 3px solid var(--color-primary-emerald);
                background-color: transparent;
            }
            .sidebar .logo { padding-bottom: 20px; }
            .sidebar .btn_lien { display: none; } 
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            .header-content {
                flex-direction: column;
                margin-bottom: 30px;
            }
            .welcome-section::after { content: none; }
            .weather-card {
                min-width: 100%;
                text-align: left;
                align-items: flex-start;
                border-top: none;
                border-left: 4px solid var(--color-secondary-gold); 
            }
            .weather-card .location, .weather-card .temp-icon, .weather-card #weather-result-container {
                 align-items: flex-start; /* Réaligner à gauche sur mobile */
            }
            .weather-card .temp-icon i {
                margin-left: 0;
                margin-right: 15px; 
            }
            .cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body> 
    
   <nav class="sidebar">
        <a href="index.php" class="logo">
            <i class="fas fa-leaf"></i> MonAgriCoach
        </a>
        <div class="sidebar-profile">
            <div class="profile-img-container">
                <img src="<?php echo $photo_path; ?>" alt="Profil">
            </div>
            <div class="profile-name">
                <?php echo htmlspecialchars(($user_data['prenom'] ?? '') . ' ' . ($user_data['nom'] ?? '')); ?>
            </div>
        </div>
        <ul>
            
            <li><a href="farmer_dashboard.php"><i class="fas fa-chart-line"></i> **Tableau de bord**</a></li>
            <li><a href="parametre.php"><i class="fas fa-tasks"></i> paramètre</a></li>
            <li><a href="liste_parcelle.php"><i class="fas fa-map-marker-alt"></i> Parcelles</a></li> 
            <li><a href="liste_plantation.php"><i class="fas fa-seedling"></i> Plantations</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i> Cultures</a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> Engrais</a></li> <li><a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li><a href="liste_message_agri.php"><i class="fas fa-comments"></i> Mes messages</a></li> 
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i> Verser engrais</a></li>
            <li><a href="liste_st_intrant.php"><i class="fas fa-warehouse"></i> Stock Engrais</a></li>
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
        <div id="ai-chat-button" onclick="toggleChat()">
    <i class="fas fa-robot"></i>
</div>

<div id="ai-chat-window">
    <div class="chat-header">
        <span><i class="fas fa-magic"></i> Assistant Agri-IA</span>
        <button onclick="toggleChat()">&times;</button>
    </div>
    <div id="chat-messages">
        <div class="message ai-msg">Bonjour ! Je suis votre assistant MonAgriCoach. Comment puis-je vous aider pour vos cultures aujourd'hui ?</div>
    </div>
   <div class="chat-input-area">
    <input type="text" id="user-query" placeholder="Posez votre question...">
    <button onclick="startVoiceRecognition()" id="mic-btn" style="background: var(--color-primary-dark); margin-right: 5px;">
        <i class="fas fa-microphone"></i>
    </button>
    <button onclick="sendToAI()"><i class="fas fa-paper-plane"></i></button>
</div>
</div>
        <div class="header-content">
            <div class="welcome-section">
                <h2>Tableau de bord</h2> 
                <p>Bienvenue, **<?php echo htmlspecialchars($_SESSION['user_email'] ?? 'agriculteur'); ?>** !</p>
                <p>Vos indicateurs clés de performance et données en temps réel.</p>
                <p class="time">Heure Locale : **<?php echo $current_time; ?>**</p>
                <?php if (!empty($message)): ?>
                    <p style="color: #FFECB3; font-weight: 600; font-size: 14px; margin-top: 10px; padding: 5px 0;">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($message); ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <div class="weather-card" id="geo-weather-card">
                <div id="weather-result-container">
                    <div class="location">
                        <i class="fas fa-map-pin"></i> Météo locale (Géolocalisation)
                    </div>
                    <div class="loading-message">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Chargement de votre position...</p>
                    </div>
                </div>
            </div>
            </div>

        <div class="cards">
            <div class="card">
                <h4>Cultures actives</h4>
                <div class="metric-value">
                    <p><?php echo htmlspecialchars($cultures_actives);?> </p>
                    <i class="fas fa-seedling"></i>
                </div>
                <div class="card-footer">
                    <span>Gérer les cultures</span>
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
            <div class="card">
                <h4>Tâches en attente</h4> 
                <div class="metric-value">
                    <p><?php echo htmlspecialchars($nombre_taches_en_attente);?></p>
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="card-footer">
                    <span>Planifier vos travaux</span>
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
            <div class="card">
                <h4>Rendement Total (t)</h4>
                <div class="metric-value">
                    <p><?php echo htmlspecialchars(number_format($rendement_total_actuels, 2, ',', ' '));?> t</p>
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="card-footer">
                    <?php 
                        $trend_class_total = ($pourcentage_rendement_total > 0) ? 'trend-up' : (($pourcentage_rendement_total < 0) ? 'trend-down' : 'trend-zero');
                        $trend_icon_total = ($pourcentage_rendement_total > 0) ? 'up' : (($pourcentage_rendement_total < 0) ? 'down' : 'right');
                    ?>
                    <span class="<?php echo $trend_class_total; ?>"> 
                        <?php echo htmlspecialchars(number_format(abs($pourcentage_rendement_total), 1));?>% 
                        <i class="fas fa-arrow-<?php echo $trend_icon_total; ?>"></i>
                    </span>
                    <span>Vs Année Précédente</span>
                </div>
            </div>
            <div class="card">
                <h4>Rendement Nets (FCFA)</h4>
                <div class="metric-value">
                    <p><?php echo htmlspecialchars(number_format($rendement_nets_actuels, 0, ',', ' '));?> FCFA</p>
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="card-footer">
                    <?php 
                        $trend_class_nets = ($pourcentage_rendement_nets > 0) ? 'trend-up' : (($pourcentage_rendement_nets < 0) ? 'trend-down' : 'trend-zero');
                        $trend_icon_nets = ($pourcentage_rendement_nets > 0) ? 'up' : (($pourcentage_rendement_nets < 0) ? 'down' : 'right');
                    ?>
                    <span class="<?php echo $trend_class_nets; ?>"> 
                        <?php echo htmlspecialchars(number_format(abs($pourcentage_rendement_nets), 1));?>% 
                        <i class="fas fa-arrow-<?php echo $trend_icon_nets; ?>"></i>
                    </span>
                    <span>Vs Année Précédente</span>
                </div>
            </div>
        </div>
        
        <div class="dashboard-layout">
            
            <div class="chart-container">
                <div class="header">
                    <h3>Statistiques Annuelles (Rendement)</h3>
                    <a href="rendement_mens.php" class="add-new-link"><i class="fas fa-plus"></i> Ajouter</a> 
                </div>
                <div class="chart-canvas-wrapper">
                    <canvas id="myChart"></canvas>
                </div>
            </div>
            
            <div class="tasks-container">
                <h3>
                    📝 Tâches en attente
                    <a href="liste_taches.php" class="view-all-button"><i class="fas fa-eye"></i> Voir tout</a>
                </h3>
                <div class="tasks-list">
                    <?php if (!empty($taches_en_attente)): ?>
                        <?php 
                        $priority_map = [
                            'haute' => 'task-priority-high',
                            'moyenne' => 'task-priority-medium',
                            'basse' => 'task-priority-low', 
                        ];
                        ?>
                        <?php foreach($taches_en_attente as $tache): 
                            $normalized_priority = strtolower($tache['priorite']);
                            $priority_class = $priority_map[$normalized_priority] ?? 'task-priority-low';
                            $priority_display = htmlspecialchars(ucfirst($tache['priorite']));
                        ?>
                        <div class="task-item">
                            <div class="task-details">
                                <strong><?php echo htmlspecialchars($tache['titre']); ?></strong>
                                <small>
                                    <i class="far fa-calendar-check"></i> Du <?php echo date('d/m/Y', strtotime($tache['date_debut'])); ?> 
                                    <i class="far fa-calendar-times" style="margin-left: 10px;"></i> Au <?php echo date('d/m/Y', strtotime($tache['date_fin'])); ?> 
                                </small>
                            </div>
                            
                            <span class="task-priority <?php echo $priority_class; ?>"><?php echo $priority_display; ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: var(--color-text-medium); padding: 30px 0; border: 1px dashed #DDD; border-radius: var(--border-radius-lg);">
                            <i class="fas fa-check-circle" style="color: var(--color-success); margin-right: 5px;"></i> Aucune tâche urgente à planifier pour le moment.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
        
    </div>
        
        <script>
          
// --- SCRIPT 1: LOGIQUE GEOLOCALISATION ET RECUPERATION METEO ---

        function getLocation() {
    const weatherContainer = document.getElementById("weather-result-container");
    
    // Coordonnées fixes pour Yaoundé
    const lat = 3.848;
    const lon = 11.502;
    
    // On affiche un petit message de chargement avant l'appel
    weatherContainer.innerHTML = `
        <div class="loading-message">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Chargement de la météo de Yaoundé...</p>
        </div>`;
    
    fetchWeather(lat, lon); 
}

        function showPosition(position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            fetchWeather(lat, lon); 
        }

        function showError(error) {
            let msg;
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    msg = "Accès refusé. Veuillez autoriser la géolocalisation.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    msg = "Localisation non disponible. Essayez à nouveau.";
                    break;
                case error.TIMEOUT:
                    msg = "Délai de la demande de position expiré.";
                    break;
                default:
                    msg = `Erreur inconnue (${error.code}).`;
            }
            document.getElementById("weather-result-container").innerHTML = `
                <div class="error-msg-weather" style="text-align: center; padding: 20px;">
                    <i class="fas fa-exclamation-triangle"></i> Géolocalisation : ${msg}
                </div>`;
        }

        function fetchWeather(lat, lon) {
            // Appel du fichier PHP dédié à la météo
            fetch(`get_weathers.php?lat=${lat}&lon=${lon}`) 
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erreur HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(data => {
                    document.getElementById("weather-result-container").innerHTML = data;
                })
                .catch(error => {
                    document.getElementById("weather-result-container").innerHTML = `
                        <div class="error-msg-weather">
                            <i class="fas fa-server"></i> Erreur de l'API: ${error.message}
                        </div>`;
                    console.error('Fetch error:', error);
                });
        }

            document.addEventListener('DOMContentLoaded', function() {
                // Récupération des données PHP dans des variables JavaScript
                const rendementTotalMensuel = <?php echo json_encode($rendement_total_mensuel); ?>;
                const rendementNetsMensuel = <?php echo json_encode($rendement_nets_mensuel); ?>;

                const ctx = document.getElementById('myChart').getContext('2d');
                
                // Configuration du dégradé pour les barres (Émeraude Vif)
                const gradientBar = ctx.createLinearGradient(0, 0, 0, 400);
                gradientBar.addColorStop(0, 'rgba(10, 185, 177, 0.8)'); 
                gradientBar.addColorStop(1, 'rgba(10, 185, 177, 0.1)'); 

                const myChart = new Chart(ctx, {
                    type: 'bar', // Type par défaut (le premier dataset sera une barre)
                    data: {
                        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
                        datasets: [
                            {
                                type: 'bar',
                                label: 'Rendement Total (t/ha)',
                                data: rendementTotalMensuel,
                                backgroundColor: gradientBar,
                                borderColor: 'var(--color-primary-emerald)',
                                borderWidth: 1,
                                borderRadius: 5,
                                borderSkipped: false,
                                yAxisID: 'y'
                            },
                            {
                                type: 'line',
                                label: 'Rendement Net (FCFA)',
                                data: rendementNetsMensuel,
                                backgroundColor: 'rgba(253, 159, 7, 0.2)', // Or Doux léger
                                borderColor: 'var(--color-secondary-gold)',
                                borderWidth: 3,
                                pointBackgroundColor: 'var(--color-secondary-gold)',
                                pointBorderColor: 'var(--color-card-bg)',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                fill: true,
                                tension: 0.3, // Courbe douce
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false, 
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20
                                }
                            },
                            zoom: { 
                                pan: { enabled: true, mode: 'x' }, // Zoom et Pan sur l'axe X
                                zoom: { enabled: true, mode: 'x' } 
                            },
                            tooltip: {
                                backgroundColor: 'rgba(31, 41, 55, 0.9)',
                                titleFont: { weight: '600' },
                                padding: 12
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false }
                            },
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: { display: true, text: 'Rendement Total (t/ha)', color: 'var(--color-primary-dark)' },
                                grid: { borderDash: [5, 5] }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: { display: true, text: 'Rendement Net (FCFA)', color: 'var(--color-secondary-gold)' },
                                grid: { drawOnChartArea: false } 
                            }
                        }
                    }
                });
                getLocation();
            });
            // --- 0. OUVERTURE/FERMETURE DE LA FENÊTRE ---
function toggleChat() {
    const chat = document.getElementById('ai-chat-window');
    // Vérifie si la fenêtre est cachée pour l'afficher, et inversement
    if (chat.style.display === 'none' || chat.style.display === '') {
        chat.style.display = 'flex';
    } else {
        chat.style.display = 'none';
    }
}

// --- 1. RECONNAISSANCE VOCALE (L'agriculteur parle) ---
const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
recognition.lang = 'fr-FR';

function startVoiceRecognition() {
    const micBtn = document.getElementById('mic-btn');
    micBtn.style.background = '#ff4d4d'; // Rouge quand on enregistre
    recognition.start();
}

recognition.onresult = (event) => {
    const transcript = event.results[0][0].transcript;
    document.getElementById('user-query').value = transcript;
    document.getElementById('mic-btn').style.background = 'var(--color-primary-dark)';
    sendToAI(); 
};

// --- 2. SYNTHÈSE VOCALE (L'IA parle) ---
function speakResponse(text) {
    const cleanText = text.replace(/<[^>]*>?/gm, ''); // Nettoyage HTML
    const utterance = new SpeechSynthesisUtterance(cleanText);
    utterance.lang = 'fr-FR';
    utterance.rate = 0.9; 
    window.speechSynthesis.speak(utterance);
}

// --- 3. ENVOI À GEMINI ---
function sendToAI() {
    const input = document.getElementById('user-query');
    const query = input.value.trim();
    if (!query) return;

    const chatMessages = document.getElementById('chat-messages');
    chatMessages.innerHTML += `<div class="message user-msg">${query}</div>`;
    input.value = '';

    chatMessages.innerHTML += `<div class="message ai-msg" id="loading-ai"><em>L'IA réfléchit...</em></div>`;
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    fetch('ai_process.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'query=' + encodeURIComponent(query)
    })
    .then(res => res.text())
    .then(data => {
        const loader = document.getElementById('loading-ai');
        if(loader) loader.remove();
        
        chatMessages.innerHTML += `<div class="message ai-msg">${data}</div>`;
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        speakResponse(data);
    })
    .catch(error => {
        console.error('Erreur:', error);
        const loader = document.getElementById('loading-ai');
        if(loader) loader.remove();
        chatMessages.innerHTML += `<div class="message ai-msg">Erreur de connexion à l'IA.</div>`;
    });
}
        </script>
    </div>
</body> 
</html>


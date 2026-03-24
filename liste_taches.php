<?php
// Démarre la session au tout début du script
session_start();

// Inclut le fichier de connexion à la base de données (db.php)
require_once 'db.php'; 

// Vérifie si l'utilisateur est connecté. Sinon, le redirige vers la page de connexion.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$taches = [];
$errorMessage = null;
$conditions = [];
$params = [':userId' => $userId];
$current_search_term = $_GET['recherche'] ?? '';

// --- LOGIQUE DE RECHERCHE ET FILTRAGE ---

// 1. Définition de la requête de base
$sql = "SELECT 
            t.id, t.titre, t.statut, t.priorite, 
            t.date_debut, t.date_fin, 
            u.nom AS nom_agriculteur 
        FROM tache AS t 
        JOIN utilisateur AS u ON t.id_agriculteur = u.id 
        WHERE t.id_agriculteur = :userId";

// 2. Ajout de la condition de recherche si un terme est fourni
if(!empty($current_search_term)){
    $termeRecherche = '%' . $current_search_term . '%';
    // Recherche par titre, statut et priorité
    $conditions[] = "(t.titre LIKE :termeRecherche OR t.statut LIKE :termeRecherche OR t.priorite LIKE :termeRecherche)";
    $params[':termeRecherche'] = $termeRecherche;
}

// 3. Construction de la requête finale
if(!empty($conditions)) {
    $sql .= " AND " . implode(" AND ", $conditions);
}

// 4. Ajout du tri
// Tri : Les tâches terminées ('Terminée') vont à la fin (ASC), puis par priorité.
$sql .= " ORDER BY t.statut = 'Terminée' ASC, 
                    FIELD(LOWER(t.priorite), 'haute', 'moyenne', 'basse') ASC, 
                    t.date_debut DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $taches = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Enregistrement de l'erreur
    error_log("Erreur de récupération des tâches: " . $e->getMessage());
    $errorMessage = "Erreur lors de la récupération des tâches. Veuillez réessayer.";
}

// Fonction pour styliser le statut
function getStatusBadge($statut) {
    $statut = strtolower($statut);
    switch ($statut) {
        case 'en cours':
            return ['text' => 'En Cours', 'class' => 'statut-progress', 'icon' => 'fas fa-spinner'];
        case 'terminée':
        case 'terminer':
            return ['text' => 'Terminée', 'class' => 'statut-done', 'icon' => 'fas fa-check-circle'];
        case 'en attente':
            return ['text' => 'En Attente', 'class' => 'statut-waiting', 'icon' => 'fas fa-clock'];
        case 'annulée':
            return ['text' => 'Annulée', 'class' => 'statut-canceled', 'icon' => 'fas fa-times-circle'];
        default:
            return ['text' => ucfirst($statut), 'class' => 'statut-default', 'icon' => 'fas fa-info-circle'];
    }
}

// Fonction pour styliser la priorité
function getPriorityBadge($priorite) {
    $priorite = strtolower($priorite);
    switch ($priorite) {
        case 'haute':
            return ['text' => 'HAUTE', 'class' => 'priorite-high', 'icon' => 'fas fa-exclamation-triangle'];
        case 'moyenne':
            return ['text' => 'MOYENNE', 'class' => 'priorite-medium', 'icon' => 'fas fa-sort-up'];
        case 'basse':
            return ['text' => 'BASSE', 'class' => 'priorite-low', 'icon' => 'fas fa-sort-down'];
        default:
            return ['text' => ucfirst($priorite), 'class' => 'priorite-default', 'icon' => 'fas fa-star'];
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= empty($current_search_term) ? 'Liste des Tâches' : 'Résultats Tâches' ?> | MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- PALETTE HARMONISÉE --- */
        :root {
            --color-primary-emerald: #06bdbdff; /* Vert Émeraude Vif */
            --color-primary-dark: #0cb4b4ff; 
            
            --color-secondary-gold: #FF8C00; /* Orange Vif (Déconnexion/Accent) */
            --color-secondary-gold-hover: #E37D00;

            --color-accent-danger: #ef4444; /* Rouge pour Haute Priorité / Alerte */
            --color-accent-warning: #f59e0b; /* Jaune/Orange pour Moyenne Priorité / En Cours */
            --color-accent-info: #3b82f6; /* Bleu pour En Attente */
            
            --color-heading: #111827; 
            --color-card-bg: #FFFFFF; 
            --color-light-bg: #F5F8F5; /* Fond principal */
            --color-text-dark: #374151;
            --color-text-medium: #4B5563; 
            
            --color-disconnect-bg: var(--color-secondary-gold); 
            --color-success: #10B981; /* Vert Succès (Terminée) */
            
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

        /* Lien Actif : Tâches */
        .sidebar li a[href="liste_taches.php"] {
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-emerald); 
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald); 
        }
        .sidebar li a[href="liste_taches.php"] i {
             color: var(--color-primary-emerald); 
        }
        
        /* Bouton Déconnexion */
        .btn-deconnexion-wrapper {
            margin-top: auto; 
            padding: 25px; 
        }
        
        .sidebar .btn_lien button { 
             border: none; 
             padding: 0; 
             background: none; 
             width: 100%; 
        }

        .sidebar .btn_lien button a {
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
        .sidebar .btn_lien button a:hover { 
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
        
        /* --- Formulaire de Recherche (Identique à liste_st_intrant.php) --- */
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
        .search-form button:hover {
            opacity: 0.9;
        }
        
        /* Message d'info sur la recherche */
        .search-info {
            font-size: 16px;
            font-weight: 600;
            color: var(--color-primary-dark);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #E5E7EB;
        }
        
        /* Messages de statut (flash messages) */
        .flash-notification {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .flash-success {
            background-color: #D1FAE5; /* Light Green */
            color: var(--color-success) !important;
        }
        .flash-danger {
            background-color: #FEE2E2; /* Light Red */
            color: var(--color-accent-danger) !important;
        }

        /* --- STYLE DES CARTES DE TÂCHES --- */
        
        .tache-grid { 
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); 
            gap: 25px;
            margin-top: 20px;
        }

        .tache-card {
            background-color: var(--color-card-bg);
            border: 1px solid #E5E7EB;
            border-left: 5px solid var(--color-primary-emerald); 
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s, opacity 0.3s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .tache-card:hover:not(.done) {
            box-shadow: 0 10px 25px rgba(6, 189, 189, 0.2); 
            transform: translateY(-5px);
        }
        
        /* NOUVEAU STYLE POUR TÂCHE TERMINÉE */
        .tache-card.done {
            background-color: #F8F8F8; /* Gris très clair */
            border: 1px solid var(--color-success);
            border-left: 5px solid var(--color-success); /* Barre latérale verte */
            opacity: 0.75; 
            transition: all 0.5s ease;
        }

        .tache-card.done .card-header-tache h3,
        .tache-card.done .card-body-tache p {
            text-decoration: line-through; /* Barrer le texte */
            color: var(--color-text-medium) !important;
        }
        .tache-card.done .card-header-tache {
            border-bottom: 2px dotted var(--color-success); /* Ligne de séparation verte */
        }

        /* Header Tâche */
        .card-header-tache {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #E5E7EB; 
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .card-header-tache h3 {
            margin: 0;
            color: var(--color-heading);
            font-family: var(--font-heading);
            font-size: 20px;
            font-weight: 800;
            line-height: 1.3;
            max-width: 70%;
        }
        
        /* Statut Badge */
        .statut-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px; 
            font-size: 13px;
            text-transform: uppercase;
            font-weight: 700;
            text-align: center;
            min-width: 100px;
        }
        
        .statut-done { /* Terminée */
            color: var(--color-success);
            background-color: #D1FAE5;
        }
        .statut-progress { /* En Cours */
            color: var(--color-accent-warning);
            background-color: #FEF3C7; 
        }
        .statut-waiting { /* En Attente */
            color: var(--color-accent-info);
            background-color: #DBEAFE; 
        }
        .statut-canceled { /* Annulée */
            color: var(--color-text-medium);
            background-color: #E5E7EB; 
        }

        /* Body Tâche */
        .card-body-tache p {
            margin: 0;
            padding: 10px 0;
            font-size: 15px;
            color: var(--color-text-medium);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px dotted #E5E7EB;
        }
        
        .card-body-tache p:last-of-type {
             border-bottom: none;
        }
        
        .card-body-tache p strong {
            color: var(--color-text-dark);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .card-body-tache p span {
             font-weight: 500;
             color: var(--color-text-dark);
        }
        
        /* Priorité Badge dans le body */
        .priorite-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 700;
            min-width: 60px;
            text-align: center;
        }

        .priorite-high {
            color: var(--color-accent-danger);
            background-color: #FEE2E2;
        }
        .priorite-medium {
            color: var(--color-accent-warning);
            background-color: #FEF3C7;
        }
        .priorite-low {
            color: var(--color-success);
            background-color: #D1FAE5;
        }

        /* Actions */
        .card-actions-tache {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #E5E7EB;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .card-actions-tache a, .card-actions-tache button {
            width: 38px; 
            height: 38px; 
            border-radius: 50%; 
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            padding: 0;
            font-size: 16px;
        }

        /* Modifier (Émeraude) */
        .card-actions-tache a {
            background-color: var(--color-primary-emerald);
            box-shadow: 0 2px 5px rgba(6, 189, 189, 0.3);
            text-decoration: none; /* Important pour les liens */
        }
        .card-actions-tache a:hover {
            background-color: var(--color-primary-dark);
            transform: scale(1.05);
        }
        
        /* Supprimer (Orange Vif) */
        .delete-button {
            background-color: var(--color-secondary-gold);
            box-shadow: 0 2px 5px rgba(255, 140, 0, 0.3);
        }
        .delete-button:hover {
            background-color: var(--color-secondary-gold-hover);
            transform: scale(1.05);
        }
        
        /* NOUVEAU: Bouton Terminer (Vert Succès) */
        .complete-button {
            background-color: var(--color-success); 
            box-shadow: 0 2px 5px rgba(16, 185, 129, 0.3);
        }
        .complete-button:hover {
            background-color: #0c9e6e; 
            transform: scale(1.05);
        }

        .card-actions-tache .fas { 
              color: var(--color-card-bg);
        }
        
        /* Message "Aucune tâche" */
        .no-records {
            text-align: center;
            padding: 40px;
            color: var(--color-text-medium);
            background-color: var(--color-light-bg);
            border: 1px dashed var(--color-primary-emerald);
            border-radius: 10px; 
            font-size: 16px;
        }
        
    </style>
</head>

<body> 
    <nav class="sidebar">
        <a href="index.php" class="logo"><i class="fas fa-leaf"></i> MonAgriCoach</a>
        
        <ul>
            <li><a href="farmer_dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a></li>
            <li> <a href="liste_parcelle.php"><i class="fas fa-map-marker-alt"></i> Parcelles</a></li> 
            <li> <a href="liste_plantation.php"><i class="fas fa-seedling"></i> Plantations</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i> Cultures</a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> Engrais</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i> Messagerie</a></li>
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i> Verser l'engrais</a></li>
            <li> <a href="liste_st_intrant.php"><i class="fas fa-warehouse"></i> Stock engrais</a></li>
            <li><a href="liste_re.php"><i class="fas fa-chart-bar"></i> Rendement</a></li>
            <li><a href="liste_taches.php"><i class="fas fa-tasks"></i> **Tâches**</a></li> 
        </ul>
        
        <div class="btn-deconnexion-wrapper"> 
            <div class="btn_lien">
                <button >
                    <a href="deconnexion.php">
                        <i class="fas fa-sign-out-alt"></i> DÉCONNEXION
                    </a>
                </button>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="header">
            <h1>Gestions des Tâches</h1>
            <a href="tache.php" class="add-new-link"> 
                <i class="fas fa-plus-circle"></i> Ajouter une Tâche
            </a>
        </div>
        
        <div class="content-container">
            <h2>Gérer l'ensemble de vos tâches</h2>
            
            <?php 
            // Afficher les messages de succès ou d'erreur
            if (isset($_GET['status']) && $_GET['status'] == 'updated') {
                echo '<p class="flash-notification flash-success"><i class="fas fa-check-circle"></i> Tâche modifiée avec succès.</p>';
            }
            if (isset($_GET['status']) && $_GET['status'] == 'deleted') {
                echo '<p class="flash-notification flash-success"><i class="fas fa-check-circle"></i> Tâche supprimée avec succès.</p>';
            }
            if (isset($_GET['status']) && $_GET['status'] == 'completed') {
                echo '<p class="flash-notification flash-success"><i class="fas fa-check-circle"></i> **FÉLICITATIONS !** La tâche a été marquée comme **TERMINÉE** !</p>';
            }
            if (isset($errorMessage)) {
                echo '<p class="flash-notification flash-danger"><i class="fas fa-exclamation-triangle"></i> ' . htmlspecialchars($errorMessage) . '</p>';
            }
            ?>
            
            <form action="liste_taches.php" method="GET" class="search-form">
                <label for="search_query" style="display:none;">Rechercher une tâche</label>
                <input type="text" name="recherche" id="search_query" placeholder="Rechercher par titre, statut ou priorité..." value="<?= htmlspecialchars($current_search_term) ?>">
                
                <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
                <button type="button" onclick="window.location.href='liste_taches.php'"><i class="fas fa-sync-alt"></i> Réinitialiser</button>
            </form>

            <?php if (!empty($current_search_term)): ?>
                <p class="search-info">
                    <i class="fas fa-filter"></i> **<?= count($taches); ?>** tâche(s) trouvée(s) pour la recherche : "<?= htmlspecialchars($current_search_term) ?>".
                </p>
            <?php endif; ?>

            <?php if (count($taches) > 0): ?>
                <div class="tache-grid">
                    <?php foreach($taches as $tache ): 
                        $status_info = getStatusBadge($tache['statut']);
                        $priority_info = getPriorityBadge($tache['priorite']);
                        // Vérifie si la tâche est terminée pour appliquer le style 'done'
                        $is_done = (strtolower($tache['statut']) == 'terminée' || strtolower($tache['statut']) == 'terminer'); 
                    ?>
                        <div class="tache-card <?= $is_done ? 'done' : ''; ?>">
                            <div class="card-header-tache">
                                <h3><?= htmlspecialchars($tache['titre']); ?></h3>
                                <span class="statut-badge <?= $status_info['class']; ?>">
                                    <i class="<?= $status_info['icon']; ?>"></i> <?= htmlspecialchars($status_info['text']); ?>
                                </span>
                            </div>
                            
                            <div class="card-body-tache">
                                <p><strong><i class="fas fa-calendar-day"></i> Début Prévu :</strong> 
                                    <span><?= date('d/m/Y H:i', strtotime($tache['date_debut'])); ?></span>
                                </p>
                                <p><strong><i class="fas fa-calendar-check"></i> Fin Prévue :</strong> 
                                    <span><?= date('d/m/Y H:i', strtotime($tache['date_fin'])); ?></span>
                                </p>
                                <p><strong><i class="fas fa-sort-amount-up-alt"></i> Priorité :</strong> 
                                    <span class="priorite-badge <?= $priority_info['class']; ?>">
                                        <?= htmlspecialchars($priority_info['text']); ?>
                                    </span>
                                </p>
                                <p><strong><i class="fas fa-user-tag"></i> Assigné par :</strong> 
                                    <span><?= htmlspecialchars($tache['nom_agriculteur']); ?></span>
                                </p>
                            </div>
                            
                            <div class="card-actions-tache">
                                
                                <?php if (!$is_done): ?>
                                    <form action="terminer_tache.php" method="POST" onsubmit="return confirm('Voulez-vous vraiment marquer cette tâche comme TERMINÉE ?');" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $tache['id']; ?>" >
                                        <button type="submit" title="Marquer comme Terminée" class="complete-button">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <a href="modifier_tache.php?id=<?= $tache['id']; ?>" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <form action="traitement_suppression_tache.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer la tâche : <?= htmlspecialchars($tache['titre']); ?> ?');" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $tache['id']; ?>" >
                                    <button type="submit" title="Supprimer" class="delete-button">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach;?>
                </div> 
            <?php else:?> 
                <p class="no-records"><i class="fas fa-info-circle"></i> **<?= empty($current_search_term) ? 'Aucune tâche trouvée pour le moment.' : 'Aucune tâche ne correspond à votre recherche.' ?>** Cliquez sur "+ Ajouter une Tâche" pour commencer.</p> 
            <?php endif;?>
        </div> 
    </div>
</body>
</html>
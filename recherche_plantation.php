<?php
// Démarre la session si ce fichier est inclus ou exécuté directement (bonne pratique)
// Si la vérification de connexion est nécessaire, elle doit être ici aussi.
// Dans cet exemple, nous supposons que la session est gérée ailleurs pour la connexion.
// session_start(); 
require_once 'db.php';

// Initialisation des tableaux pour les conditions et les paramètres
$conditions = [];
$params = [];
// La requête de base commence par la jointure
$query_string = "SELECT 
                    p.id, 
                    p.date_semis, 
                    p.date_recolte_prevue, 
                    p.quantite_semis_kg_ha, 
                    p.statut_plantation, 
                    p.rendement_final_kg, 
                    p.unite_rendement, 
                    pa.nom_parcelle AS nom_parcelle, 
                    c.nom_commun AS nom_commun,
                    u.nom AS nom_agriculteur
                 FROM plantation AS p 
                 JOIN parcelle AS pa ON p.id_parcelle = pa.id 
                 JOIN culture AS c ON p.id_culture = c.id
                 JOIN utilisateur AS u ON pa.id_agriculteur_proprietaire = u.id";

// Si l'utilisateur est connecté (supposons qu'il doive l'être), ajouter la condition de sécurité
// $userId = $_SESSION['user_id'];
// $conditions[] = "pa.id_agriculteur_proprietaire = :userId";
// $params[':userId'] = $userId;
// Nous omettons ceci ici car le code PHP initial ne le faisait pas, mais C'EST UNE VULNÉRABILITÉ.
// La page de recherche devrait TOUJOURS filtrer par l'utilisateur connecté !

// --- Logique de recherche (conservée et filtrée) ---
$search_term = $_GET['recherche'] ?? ''; // Utilisation de l'opérateur null-coalesce
$date_semis = $_GET['date_semis'] ?? '';
$date_recolte_prevue = $_GET['date_recolte_prevue'] ?? '';

if (!empty($search_term)) {
    $termeRecherche = '%' . $search_term . '%';
    // Ajout de parenthèses pour gérer la priorité
    $conditions[] = "(p.statut_plantation LIKE :termeRecherche OR p.unite_rendement LIKE :termeRecherche OR pa.nom_parcelle LIKE :termeRecherche OR c.nom_commun LIKE :termeRecherche)";
    $params[':termeRecherche'] = $termeRecherche;
}

if (!empty($date_semis)) {
    $conditions[] = "date_semis = :date_semis";
    $params[':date_semis'] = $date_semis;
}

if (!empty($date_recolte_prevue)) {
    $conditions[] = "date_recolte_prevue = :date_recolte_prevue";
    $params[':date_recolte_prevue'] = $date_recolte_prevue;
}

// Construction finale de la requête
if (!empty($conditions)) {
    // Si nous avions un userId, il devrait être géré ici.
    // Puisque le code initial ne filtre pas par utilisateur, j'ajoute WHERE directement au lieu de AND
    // Si vous aviez déjà une clause WHERE avant, il faudrait utiliser AND
    $query_string .= " WHERE " . implode(" AND ", $conditions); 
}

// Ajouter l'ordre pour uniformité
$query_string .= " ORDER BY p.date_semis DESC";


$error_message = null;
try {
    $stmt = $pdo->prepare($query_string);
    $stmt->execute($params);
    $plantations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Gérer l'erreur de manière plus propre
    $error_message = "Erreur de base de données : " . $e->getMessage();
    // Ne pas utiliser die() dans un environnement de production
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
        /* --- COPTE INTÉGRALE DU CSS de liste_plantation.php --- */
        
        /* --- PALETTE OPTIMISÉE --- */
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
        /* Mettre en évidence le lien Plantations pour la page de recherche aussi */
        .sidebar li a[href="liste_plantation.php"],
        .sidebar li a[href="recherche_plantation.php"] { 
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-emerald); 
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald); 
        }
        .sidebar li a[href="liste_plantation.php"] i,
        .sidebar li a[href="recherche_plantation.php"] i {
             color: var(--color-primary-emerald); 
        }

        .sidebar li.disconnect-item {
            margin-top: auto; 
            padding: 25px; 
        }
        /* Style pour le lien de déconnexion */
        .btn_lien {
            padding: 25px;
        }
        .btn_lien button {
            border: none;
            padding: 0;
            background: none;
            width: 100%;
        }
        .btn_lien a {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            background-color: var(--color-disconnect-bg); 
            color: var(--color-card-bg) !important; 
            padding: 12px 20px;
            border-radius: 8px; 
            font-size: 15px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(255, 140, 0, 0.4); 
            transition: all 0.3s; 
            text-decoration: none;
            border-left: none; 
        }
        .btn_lien a:hover { 
              background-color: var(--color-secondary-gold-hover) !important;
              box-shadow: 0 6px 20px rgba(255, 140, 0, 0.6);
        } 
        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: var(--sidebar-width); 
            padding: 50px 40px; 
            flex-grow: 1;
            width: 100%; 
            box-sizing: border-box; 
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
        .search-form input[type="text"],
        .search-form input[type="date"] { /* Ajout de input date */
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
        
        /* --- Styles de la Grille (Cartes) --- */
        .plantation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
            gap: 25px;
            margin-top: 20px;
        }

        .plantation-card {
            background-color: var(--color-card-bg);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            flex-direction: column;
            border: 1px solid #E5E7EB;
        }

        .plantation-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--color-light-bg);
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .card-header h3 {
            font-family: var(--font-heading);
            font-size: 20px;
            font-weight: 800;
            color: var(--color-primary-emerald);
            margin: 0;
            flex-grow: 1;
        }
        
        .card-header span.parcelle-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--color-text-medium);
            background-color: #F3F4F6;
            padding: 4px 10px;
            border-radius: 6px;
        }

        .card-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }

        .detail-item {
            font-size: 14px;
        }

        .detail-item strong {
            display: block;
            font-weight: 600;
            color: var(--color-text-dark);
            margin-bottom: 2px;
            font-size: 13px;
            text-transform: uppercase;
        }

        .detail-item span {
            color: var(--color-text-medium);
            font-weight: 500;
        }
        
        /* État / Statut */
        .card-status {
            text-align: center;
            padding: 10px 0;
            border-top: 1px solid #E5E7EB;
            margin-top: auto; /* Pousse les actions en bas */
            padding-top: 15px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px; 
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        /* COULEURS DES STATUTS */
        .status-en_cours { /* En Cours / Semée */
            color: #059669; 
            background-color: #D1FAE5; 
        }
        .status-complete { /* Récoltée */
            color: var(--color-text-medium); 
            background-color: #F3F4F6; 
        }
        .status-danger { /* Mauvaise / Abandonnée */
            color: #DC2626; 
            background-color: #FEE2E2; 
        }
        
        /* Actions en bas de carte */
        .card-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 15px;
        }
        
        .card-actions a, .card-actions button {
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
        
        .card-actions a[href*="modifier_plantation"] {
            background-color: var(--color-primary-emerald); 
            color: var(--color-card-bg);
        }
        .card-actions a[href*="modifier_plantation"]:hover {
            background-color: var(--color-primary-dark);
        }
        
        .card-actions .delete-button {
            background-color: var(--color-secondary-gold); 
            color: var(--color-card-bg); 
        }
        .card-actions .delete-button:hover {
            background-color: var(--color-secondary-gold-hover);
        }
        
        /* Media Query pour les petits écrans */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            .sidebar {
                display: none; 
            }
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .search-form {
                flex-direction: column;
                padding: 15px;
            }
            .search-form button,
            .search-form input[type="text"] {
                width: 100%;
            }
        }
        
    </style>
</head>
<body class="dashboard-body"> 
    <nav class="sidebar">
       <a href="index.php" class="logo">
                <i class="fas fa-leaf"></i> MonAgriCoach
            </a>
        <ul>
            <li><a href="farmer_dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a></li>
            <li> <a href="liste_parcelle.php"><i class="fas fa-map-marker-alt"></i> Parcelles</a></li> 
            <li> <a href="liste_plantation.php"><i class="fas fa-seedling"></i> **plantations**</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i>  Cultures</a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> Engrais</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i> Messagerie</a></li>
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i>Verser l'engrais</a></li>
            <li> <a href="liste_st_intrant.php"><i class="fas fa-warehouse"></i> stock engrais</a></li>
            <li><a href="liste_re.php"><i class="fas fa-chart-bar"></i> Rendement</a></li>
            <li><a href="liste_taches.php"><i class="fas fa-tasks"></i> Tâches</a></li>
            
            <div class="btn_lien">
                <button >
                    <a href="deconnexion.php">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </button>
            </div>
        </ul>
    </nav>
    <div class="main-content">
        <div class="header">
            <h1>Résultats de Recherche Plantations</h1>
            <a href="plantation.php" class="add-new-link">
                <i class="fas fa-plus-circle"></i> Ajouter
            </a>
        </div>

        <?php if (isset($error_message) && !empty($error_message)): ?>
            <p class="error-php-message">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?>
            </p>
        <?php endif; ?>
        
        <div class="content-container">
            <h2>Plantations correspondant à votre recherche</h2>
            
            <form action="recherche_plantation.php" method="GET" class="search-form">
                <label for="search_query" style="display:none;">Rechercher une plantation</label>
                <input type="text" name="recherche" placeholder="Recherche par culture, parcelle ou statut..." id="search_query" value="<?= htmlspecialchars($search_term); ?>">
                <input type="date" name="date_semis" title="Rechercher par date de semis" value="<?= htmlspecialchars($date_semis); ?>">
                <input type="date" name="date_recolte_prevue" title="Rechercher par date de récolte prévue" value="<?= htmlspecialchars($date_recolte_prevue); ?>">
                
                <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
                <button type="button" onclick="window.location.href='liste_plantation.php'"><i class="fas fa-sync-alt"></i> Rénitialiser</button>
            </form>

            <?php if (count($plantations) > 0): ?>
                
                <div class="plantation-grid">
                    <?php foreach($plantations as $plantation ): 
                        // --- LOGIQUE DE STATUT (copiée de liste_plantation.php) ---
                        $statut_lower = strtolower($plantation['statut_plantation']);
                        $status_class = 'status-complete';
                        $status_icon = 'fas fa-info-circle';

                        switch ($statut_lower) {
                            case 'en croissance':
                            case 'en cours':
                            case 'semée':
                                $status_class = 'status-en_cours';
                                $status_icon = 'fas fa-seedling';
                                break;
                            case 'récoltée':
                                $status_class = 'status-complete'; 
                                $status_icon = 'fas fa-check-circle';
                                break;
                            case 'mauvaise':
                            case 'abandonnée':
                                $status_class = 'status-danger';
                                $status_icon = 'fas fa-exclamation-triangle';
                                break;
                        }
                    ?>
                        <div class="plantation-card">
                            
                            <div class="card-header">
                                <h3><?= htmlspecialchars($plantation['nom_commun']); ?></h3>
                                <span class="parcelle-name"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($plantation['nom_parcelle']); ?></span>
                            </div>
                            
                            <div class="card-details">
                                <div class="detail-item">
                                    <strong><i class="fas fa-calendar-alt"></i> Semis</strong>
                                    <span><?= htmlspecialchars(date('d/m/Y', strtotime($plantation['date_semis']))); ?></span>
                                </div>
                                <div class="detail-item">
                                    <strong><i class="fas fa-calendar-check"></i> Récolte Prévue</strong>
                                    <span><?= htmlspecialchars(date('d/m/Y', strtotime($plantation['date_recolte_prevue']))); ?></span>
                                </div>
                                <div class="detail-item">
                                    <strong><i class="fas fa-weight-hanging"></i> Qté Semis (Kg/Ha)</strong>
                                    <span><?= htmlspecialchars($plantation['quantite_semis_kg_ha']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <strong><i class="fas fa-chart-line"></i> Rendement Final</strong>
                                    <span>
                                        <?php 
                                            $rendement_final = $plantation['rendement_final_kg'] ?? '';
                                            $unite_rendement = $plantation['unite_rendement'] ?? '';
                                            
                                            if (empty($rendement_final)) {
                                                echo 'N/A';
                                            } else {
                                                echo htmlspecialchars($rendement_final) . ' ' . htmlspecialchars($unite_rendement);
                                            }
                                        ?>
                                    </span>
                                </div>
                                <div class="detail-item" style="grid-column: span 2;">
                                    <strong><i class="fas fa-user-circle"></i> Agriculteur</strong>
                                    <span><?= htmlspecialchars($plantation['nom_agriculteur'] ?? 'Non spécifié'); ?></span>
                                </div>
                            </div>
                            
                            <div class="card-status">
                                <strong>Statut : </strong>
                                <span class="status-badge <?= $status_class; ?>">
                                    <i class="<?= $status_icon; ?>"></i>
                                    <?= htmlspecialchars($plantation['statut_plantation']); ?>
                                </span>
                            </div>
                            
                            <div class="card-actions">
                                <a href="modifier_plantation.php?id=<?= $plantation['id']; ?>" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="supprime_plantation.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette plantation? Cette action est irréversible.');" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $plantation['id']; ?>" >
                                    <button type="submit" title="Supprimer" class="delete-button">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div> 
                
            <?php else: ?> 
                <p><i class="fas fa-info-circle"></i> Aucune plantation trouvée pour vos critères de recherche. Vous pouvez <a href="liste_plantation.php">réinitialiser la recherche</a> ou <a href="plantation.php">ajouter une nouvelle plantation</a>.</p> 
            <?php endif; ?>
        </div> 
    </div>
</body>
</html>
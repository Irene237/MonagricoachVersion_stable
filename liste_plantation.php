<?php
// Démarre la session au tout début du script
session_start();
// Inclut le fichier de connexion à la base de données (db.php)
require_once 'db.php'; 

// --- Bloc de Vérification de Connexion ---
if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) { 
    // Redirige si l'utilisateur n'est pas connecté
    header("Location: connexion.php"); 
    exit(); 
} 

$userId = $_SESSION['user_id'];
$plantations = [];
$error_message = null; // Initialisation du message d'erreur

try{
    // Récupération des plantations pour l'utilisateur connecté (agriculteur)
    $sql = "SELECT 
                pl.id, 
                pl.date_semis, 
                pl.date_recolte_prevue, 
                pl.quantite_semis_kg_ha, 
                pl.statut_plantation, 
                pl.rendement_final_kg, 
                pl.unite_rendement, 
                c.nom_commun, 
                p.nom_parcelle,
                u.nom AS nom_agriculteur 
            FROM plantation AS pl 
            JOIN culture AS c ON pl.id_culture = c.id
            JOIN parcelle AS p ON pl.id_parcelle = p.id
            JOIN utilisateur AS u ON p.id_agriculteur_proprietaire = u.id 
            WHERE p.id_agriculteur_proprietaire = :userId 
            ORDER BY pl.date_semis DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $plantations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Gestion d'erreur de base de données
    $error_message = "Erreur de récupération des plantations : " . $e->getMessage();
}

?>

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Liste des Plantations | MonAgriCoach</title> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* Les styles généraux (sidebar, header, etc.) sont conservés */
        
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
        
        /* SIDEBAR & MAIN-CONTENT styles (conservés pour la concision) */
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
        .sidebar li a[href="liste_plantation.php"] {
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-emerald); 
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald); 
        }
        .sidebar li a[href="liste_plantation.php"] i {
             color: var(--color-primary-emerald); 
        }
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
        
        /* --- NOUVEAUX STYLES pour les Cartes --- */
        .plantation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); /* Responsive grid */
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
        
        .status-en_cours {
            color: #059669; /* Émeraude */
            background-color: #D1FAE5; /* Vert clair */
        }
        .status-mauvais {
            color: #DC2626; /* Rouge */
            background-color: #FEE2E2; /* Rouge clair */
        }
        .status-en_attente {
            color: #F59E0B; /* Jaune/Orange */
            background-color: #FFFBEB; /* Jaune clair */
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
                display: none; /* Simplification : cacher la sidebar sur mobile pour cet exemple */
            }
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
            <li> <a href="liste_parcelle.php"><i class="fas fa-map-marker-alt"></i> Parcelles</a></li> 
            <li> <a href="liste_plantation.php"><i class="fas fa-seedling"></i> **Plantations**</a></li>
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
            <h1>Gestion des Plantations</h1>
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
            <h2>Liste de vos plantations enregistrées</h2>
            
            <form action="recherche_plantation.php" method="GET" class="search-form">
                <label for="search_query" style="display:none;">Rechercher une plantation</label>
                <input type="text" name="recherche" placeholder="Recherche par culture ou parcelle..." id="search_query">
                
                <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
                <button type="button" onclick="window.location.href='liste_plantation.php'"><i class="fas fa-sync-alt"></i> Rénitialiser</button>
            </form>

            <?php if (count($plantations) > 0): ?>
                
                <div class="plantation-grid">
                    <?php foreach($plantations as $plantation ): 
                        // Détermine la classe CSS pour le statut
                        $statut_lower = strtolower($plantation['statut_plantation']);
                        $status_class = 'status-en_attente';
                        $status_icon = 'fas fa-hourglass-half';

                        switch ($statut_lower) {
                            case 'en croissance':
                            case 'en cours':
                                $status_class = 'status-en_cours';
                                $status_icon = 'fas fa-seedling';
                                break;
                            case 'récoltée':
                                $status_class = 'status-en_attente'; // Couleur neutre pour "terminé"
                                $status_icon = 'fas fa-check-circle';
                                break;
                            case 'mauvaise':
                                $status_class = 'status-mauvais';
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
                                            echo (empty($plantation['rendement_final_kg']) ? 'N/A' : htmlspecialchars($plantation['rendement_final_kg']));
                                            echo (empty($plantation['unite_rendement']) ? '' : ' ' . htmlspecialchars($plantation['unite_rendement']));
                                        ?>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <strong><i class="fas fa-user-circle"></i> Agriculteur</strong>
                                    <span><?= htmlspecialchars($plantation['nom_agriculteur']); ?></span>
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
                <p><i class="fas fa-info-circle"></i> Aucune plantation trouvée pour votre compte. Cliquez sur "**Ajouter**" pour commencer.</p> 
            <?php endif; ?>
        </div> 
    </div>

    </body> 
</html>
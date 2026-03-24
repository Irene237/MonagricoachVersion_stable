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
$intrants = [];
$error_message = null;

try{
    // Récupération des intrants pour l'utilisateur connecté (agriculteur)
    $sql ="SELECT id, nom_intrant, type_intrant, unite_standard, descriptions 
           FROM intrant 
           WHERE id_agriculteur = :userId 
           ORDER BY nom_intrant ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $intrants = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Affichage d'un message d'erreur en cas de problème de connexion ou de requête
    $error_message = "Erreur de récupération des intrants : " . $e->getMessage();
}

// Initialisation d'un compteur pour la logique de démonstration des statuts
$counter = 0;
?>

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Liste des Intrants | MonAgriCoach</title> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- PALETTE HARMONISÉE (Émeraude & Or) --- */
        :root {
            --color-primary-emerald: #06bdbdff; 
            --color-primary-dark: #0cb4b4ff; 
            
            --color-secondary-gold: #FF8C00; 
            --color-secondary-gold-hover: #E37D00;

            --color-accent-danger: #ef4444; 
            --color-accent-success: #10B981; /* Vert Succès */
            --color-accent-warning: #F59E0B; /* Orange Avertissement */
            
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

        /* --- BARRE LATÉRALE (SIDEBAR) (Reste le même) --- */
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
            font-family: 'Font Awesome 5 Free'; 
            font-weight: 900;
        }
        
        .sidebar li a:hover {
            background-color: #f5f8f8ff;
            color: var(--color-primary-dark);
        }

        /* Lien Actif : Intrant */
        .sidebar li a[href="liste_intrant.php"] {
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-emerald); 
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald); 
        }
        .sidebar li a[href="liste_intrant.php"] i {
             color: var(--color-primary-emerald); 
        }
        
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

        /* --- CONTENU PRINCIPAL (Reste le même) --- */
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
            padding: 20px;
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

        /* --- Formulaire de Recherche (Reste le même) --- */
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
        
        /* --- STYLE DES LISTES D'INTRANTS (AVEC STATUT À DROITE) --- */
        
        .intrants-list {
            list-style: none;
            padding: 0;
            margin-top: 20px;
            display: grid;
            gap: 20px;
        }

        .intrant-item {
            background-color: var(--color-card-bg);
            border: 1px solid #E5E7EB;
            border-left: 6px solid var(--color-primary-emerald); 
            border-radius: 12px;
            padding: 20px 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .intrant-item:hover {
            box-shadow: 0 8px 20px rgba(6, 189, 189, 0.15); 
            transform: translateY(-3px);
        }
        
        /* Conteneur principal (Header + Détails + Statut) */
        .intrant-main-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start; /* Alignement du haut pour les détails */
            margin-bottom: 15px;
        }

        .intrant-details {
             flex-grow: 1;
             margin-right: 20px;
        }
        
        .intrant-details h3 {
             margin: 0 0 10px 0;
             font-family: var(--font-heading);
             font-size: 22px;
             color: var(--color-heading);
             font-weight: 800;
             display: flex;
             align-items: center;
             gap: 10px;
        }
        
        .intrant-details h3 i {
            color: var(--color-secondary-gold);
            font-size: 24px;
        }
        
        .metadata-detail {
            font-size: 15px;
            color: var(--color-text-medium);
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .metadata-detail strong {
            font-weight: 600;
            color: var(--color-text-dark);
            min-width: 150px; /* Aligner les valeurs */
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* --- NOUVEAU BLOC STATUT À DROITE --- */
        .intrant-status {
            width: 800px; /* Largeur fixe pour le bloc de statut */
            padding: 15px;
            text-align: center;
            border-radius: 10px;
            background-color: var(--color-light-bg);
            border: 1px solid #E5E7EB;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .intrant-status h4 {
            font-family: var(--font-heading);
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 10px 0;
            color: var(--color-heading);
        }

        .status-tag {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            margin-bottom: 10px;
        }

        /* Styles pour les différents statuts de démo */
        .status-tag.excellent {
            background-color: var(--color-accent-success);
            color: var(--color-card-bg);
        }
        .status-tag.warning {
            background-color: var(--color-accent-warning);
            color: var(--color-text-dark);
        }
        
        .intrant-status p {
            font-size: 13px;
            color: var(--color-text-medium);
            margin: 0;
            font-style: italic;
        }

        .status-actions {
             margin-top: 15px;
             display: flex;
             justify-content: center;
             gap: 10px;
        }
        
        /* Description détaillée (Utilise toute la largeur) */
        .description-detail {
            padding-top: 15px;
            border-top: 1px dashed #E5E7EB;
            font-size: 14px;
            line-height: 1.5;
            color: var(--color-text-medium);
        }
        
        .description-detail strong {
            font-style: normal;
            font-weight: 700;
            color: var(--color-heading);
            margin-bottom: 5px;
            display: block;
        }
        
        /* Bloc d'actions (Déplacé dans le bloc de Statut pour l'organisation) */
        .status-actions a, .status-actions button {
            width: 35px; 
            height: 35px; 
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

        /* Modifier (Émeraude) */
        .status-actions a {
            background-color: var(--color-primary-emerald);
            box-shadow: 0 2px 5px rgba(6, 189, 189, 0.3);
        }
        /* Supprimer (Danger Rouge) */
        .delete-button {
            background-color: var(--color-accent-danger);
            box-shadow: 0 2px 5px rgba(239, 68, 68, 0.3);
        }
        
        .status-actions a:hover, .delete-button:hover {
            transform: scale(1.1);
        }
        
        .status-actions .fas { 
             color: var(--color-card-bg);
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
            <li> <a href="liste_plantation.php"><i class="fas fa-seedling"></i> Plantations</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i> Cultures</a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> **Engrais**</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i> Messagerie</a></li>
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i> Verser l'engrais</a></li>
            <li> <a href="liste_st_intrant.php"><i class="fas fa-warehouse"></i> Stock engrais</a></li>
            <li><a href="liste_re.php"><i class="fas fa-chart-bar"></i> Rendement</a></li>
            <li><a href="liste_taches.php"><i class="fas fa-tasks"></i> Tâches</a></li>
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
            <h1>Gestion des Intrants</h1>
            <a href="intrant.php" class="add-new-link">
                <i class="fas fa-plus-circle"></i> Ajouter
            </a> 
        </div>

        <?php if (isset($error_message)): ?>
            <p class="error-php-message" style="color: var(--color-accent-danger); background-color: #FEE2E2; padding: 15px; border-radius: 8px; font-weight: 600; margin-bottom: 30px;"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>
        
        <div class="content-container">
            <h2>Gérer l'ensemble de vos intrants</h2>
            
            <form action="recherche_intrant.php" method="GET" class="search-form">
                <label for="search_query" style="display:none;">Rechercher un intrant</label>
                <input type="text" name="recherche" placeholder="Recherche par nom d'intrant..." id="search_query">
                
                <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
                <button type="button" onclick="window.location.href='liste_intrant.php'"><i class="fas fa-sync-alt"></i> Réinitialiser</button>
            </form>

            <?php if (count($intrants) > 0): ?>
                <ul class="intrants-list">
                    <?php foreach($intrants as $intrant ): 
                        $counter++;
                        // Logique de Démo : alterne les statuts
                        if ($counter % 2 == 1) {
                            $status_class = 'excellent';
                            $status_message = 'Excellent intrant !';
                            $status_tip = 'Potentiel maximal de rendement.';
                        } else {
                            $status_class = 'warning';
                            $status_message = 'Peu recommandé.';
                            $status_tip = 'Consulter les recommandations pour alternatives.';
                        }
                    ?>
                        <li class="intrant-item">
                            <div class="intrant-main-row">
                                <div class="intrant-details">
                                    <h3>
                                        <i class="fas fa-flask"></i> 
                                        <?= htmlspecialchars($intrant['nom_intrant']); ?>
                                    </h3>
                                    
                                    <div class="metadata-detail">
                                        <strong><i class="fas fa-tag"></i> Type d'intrant :</strong> 
                                        <span><?= htmlspecialchars($intrant['type_intrant']); ?></span>
                                    </div>
                                    <div class="metadata-detail">
                                        <strong><i class="fas fa-balance-scale"></i> Unité standard :</strong> 
                                        <span><?= htmlspecialchars($intrant['unite_standard']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="intrant-status">
                                    <h4>Mon Évaluation :</h4>
                                    <span class="status-tag <?= $status_class; ?>">
                                        <?= $status_message; ?>
                                    </span>
                                    <p><i class="fas fa-lightbulb"></i> <?= $status_tip; ?></p>
                                    
                                    <div class="status-actions">
                                        <a href="modifier_intrant.php?id=<?= $intrant['id']; ?>" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="supprime_intrant.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer l\'intrant : <?= htmlspecialchars($intrant['nom_intrant']); ?> ?');" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $intrant['id']; ?>" >
                                            <button type="submit" title="Supprimer" class="delete-button">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($intrant['descriptions'])): ?>
                                <div class="description-detail">
                                    <strong><i class="fas fa-info-circle"></i> Description détaillée :</strong> 
                                    <?= htmlspecialchars($intrant['descriptions']); ?>
                                </div>
                            <?php endif; ?>

                        </li> 
                    <?php endforeach;?>
                </ul>
            <?php else:?> 
                <p><i class="fas fa-info-circle"></i> Aucun intrant enregistré. Cliquez sur "**Ajouter Intrant**" pour commencer.</p> 
            <?php endif;?>
        </div> 
    </div>
</body>
</html>
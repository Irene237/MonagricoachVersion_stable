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
$cultures = [];
$error_message = null;

try{
    // Récupération des cultures pour l'utilisateur connecté (agriculteur)
    $sql ="SELECT 
                c.id, 
                c.nom_commun, 
                c.cycle_de_vie_jours, 
                c.besoins_eau_mm, 
                c.besoins_nutriments, 
                c.sensibilite_maladies, 
                c.informations_generales,
                c.statut,
                u.nom AS nom_agriculteur 
            FROM culture AS c 
            JOIN utilisateur AS u ON c.id_agriculteur = u.id 
            WHERE u.id = :userId AND u.type_utilisateur = 'agriculteur' 
            ORDER BY c.nom_commun ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $cultures = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Affichage d'un message d'erreur en cas de problème de connexion ou de requête
    $error_message = "Erreur de récupération des cultures : " . $e->getMessage();
}
?>

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Liste des Cultures | MonAgriCoach</title> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- PALETTE HARMONISÉE (Basée sur liste_plantation.php) --- */
        :root {
            --color-primary-emerald: #06bdbdff; /* Vert Émeraude Vif */
            --color-primary-dark: #0cb4b4ff; 
            
            --color-secondary-gold: #FF8C00; /* Orange Vif (Déconnexion/Accent) */
            --color-secondary-gold-hover: #E37D00;

            --color-accent-danger: #ef4444; /* Rouge pour les erreurs/danger */
            
            --color-heading: #111827; /* Titres sombres */
            --color-card-bg: #FFFFFF; 
            --color-light-bg: #F5F8F5; /* Fond principal */
            --color-text-dark: #374151;
            --color-text-medium: #4B5563; 
            
            --color-disconnect-bg: var(--color-secondary-gold); 
            --color-success: #10B981; /* Vert Succès */
            
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

        /* --- BARRE LATÉRALE (SIDEBAR) - Harmonisé --- */
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

        /* Lien Actif : Culture */
        .sidebar li a[href="liste_culture.php"] {
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-emerald); 
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald); 
        }
        .sidebar li a[href="liste_culture.php"] i {
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
            border-radius: 8px; /* Changé de 25px à 8px pour l'harmonisation */
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
            border-radius: 15px; /* Changé de 12px à 15px pour l'harmonisation */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
        
        .content-container h2 {
            font-family: var(--font-heading); /* Harmonisé */
            font-weight: 700;
            font-size: 24px; /* Harmonisé */
            color: var(--color-heading);
            margin-top: 0;
            margin-bottom: 25px;
        }

        /* --- Formulaire de Recherche (Harmonisé) --- */
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
        /* Bouton Rechercher : Émeraude pour l'action principale */
        .search-form button[type="submit"] {
            background-color: var(--color-primary-emerald); 
            color: var(--color-card-bg); 
            box-shadow: 0 2px 5px rgba(6, 189, 189, 0.2);
        }
        .search-form button[type="button"] {
            background-color: #D1D5DB; 
            color: var(--color-text-dark);
        }
        
        /* --- STYLE DES CARTES DE CULTURE (Magnifique!) --- */
        
        .cultures-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); 
            gap: 25px;
            margin-top: 20px;
        }

        .culture-card {
            background-color: var(--color-card-bg);
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            padding: 25px; /* Augmenté pour l'esthétique */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .culture-card:hover {
            box-shadow: 0 10px 25px rgba(6, 189, 189, 0.2); /* Ombre émeraude au survol */
            transform: translateY(-5px);
        }

        .card-header-culture {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--color-primary-emerald); /* Ligne de séparation émeraude */
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .card-header-culture h3 {
            margin: 0;
            color: var(--color-heading);
            font-family: var(--font-heading);
            font-size: 24px; /* Plus grand */
            font-weight: 800;
        }
        .card-header-culture h3 i {
             color: var(--color-primary-emerald);
             margin-right: 10px;
        }
        
        .card-body-culture p {
            margin: 0;
            padding: 10px 0;
            font-size: 15px;
            color: var(--color-text-medium);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px dotted #E5E7EB;
        }
        
        .card-body-culture p:last-of-type {
             border-bottom: none;
        }
        
        .card-body-culture p strong {
            color: var(--color-text-dark);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Style des valeurs numériques */
        .card-body-culture p span {
             font-weight: 500;
             color: var(--color-primary-dark);
        }
        
        /* Informations Générales (Bloc) */
        .card-body-culture .info-generale {
            font-size: 13px;
            font-style: italic;
            margin-top: 15px;
            padding: 10px;
            background-color: var(--color-light-bg);
            border-radius: 8px;
            border-left: 4px solid var(--color-secondary-gold);
            color: var(--color-text-medium);
            line-height: 1.5;
            display: block; /* Important pour ne pas déranger le flex */
        }
        .card-body-culture .info-generale strong {
            display: block;
            margin-bottom: 5px;
            color: var(--color-heading);
            font-style: normal;
        }
        
        /* Affichage Agriculteur */
         .card-body-culture p.agriculteur-info {
            padding-top: 15px;
            border-top: 1px solid #F0F0F0;
            font-size: 13px;
            color: var(--color-text-light);
            justify-content: flex-start;
        }

        /* Statut Badge */
        .statut-cell span {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px; 
            font-size: 13px;
            text-transform: uppercase;
            font-weight: 700;
        }
        
        .statut-actif {
            color: var(--color-success);
            background-color: #D1FAE5; /* Vert clair */
        }
        .statut-inactif {
            color: var(--color-accent-danger);
            background-color: #FEE2E2; /* Rouge clair */
        }

        /* Actions (Harmonisé) */
        .card-actions-culture {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #E5E7EB;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .card-actions-culture a, .card-actions-culture button {
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
        .card-actions-culture a {
            background-color: var(--color-primary-emerald);
            box-shadow: 0 2px 5px rgba(6, 189, 189, 0.3);
        }
        .card-actions-culture a:hover {
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
        
        .card-actions-culture .fas { 
             color: var(--color-card-bg);
        }
        
        /* Message Erreur/Aucune donnée */
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
        
    </style>
</head>

<body> 
    <nav class="sidebar">
        <a href="index.php" class="logo"> <i class="fas fa-leaf"></i> MonAgriCoach
        </a>
        
        <ul>
          <li><a href="farmer_dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a></li>
            <li> <a href="liste_parcelle.php"><i class="fas fa-map-marker-alt"></i> Parcelles</a></li> 
            <li> <a href="liste_plantation.php"><i class="fas fa-seedling"></i> Plantations</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i> **Cultures**</a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> Engrais</a></li>
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
            <h1>Gestions des Cultures</h1>
            <a href="culture.php" class="add-new-link">
                <i class="fas fa-plus-circle"></i> Ajouter 
            </a>
        </div>
        
        <?php if (isset($error_message)): ?>
            <p class="error-php-message" style="color: var(--color-accent-danger); background-color: #FEE2E2; padding: 15px; border-radius: 8px; font-weight: 600; margin-bottom: 30px;"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <div class="content-container">
            <h2>Gérer l'ensemble de vos cultures</h2>
            
            <form action="recherche_culture.php" method="GET" class="search-form">
                <label for="search_query" style="display:none;">Rechercher une culture</label>
                <input type="text" name="recherche" placeholder="Recherche par nom de culture..." id="search_query">
                
                <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
                <button type="button" onclick="window.location.href='liste_culture.php'"><i class="fas fa-sync-alt"></i> Réinitialiser</button>
            </form>

            <?php if (count($cultures) > 0): ?>
                <div class="cultures-grid">
                    <?php foreach($cultures as $culture ): 
                        // Détermination de la classe de statut
                        $statut_class = (strtolower($culture['statut']) == 'actif') ? 'statut-actif' : 'statut-inactif';
                        $statut_icon = (strtolower($culture['statut']) == 'actif') ? 'fas fa-check-circle' : 'fas fa-times-circle';
                        
                        // Détermination de l'icône de sensibilité aux maladies
                        $maladie_icon = '';
                        switch (strtolower($culture['sensibilite_maladies'])) {
                            case 'faible':
                                $maladie_icon = 'fas fa-shield-alt';
                                break;
                            case 'moyenne':
                                $maladie_icon = 'fas fa-hand-paper';
                                break;
                            case 'elevée':
                                $maladie_icon = 'fas fa-bug';
                                break;
                            default:
                                $maladie_icon = 'fas fa-question-circle';
                                break;
                        }
                    ?>
                        <div class="culture-card">
                            <div class="card-header-culture">
                                <h3><i class="fas fa-seedling"></i> <?= htmlspecialchars($culture['nom_commun']); ?></h3>
                                <div class="statut-cell">
                                    <span class="<?= $statut_class; ?>">
                                        <i class="<?= $statut_icon; ?>"></i> <?= htmlspecialchars($culture['statut']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="card-body-culture">
                                <p><strong><i class="fas fa-clock"></i> Cycle de vie :</strong> <span><?= htmlspecialchars($culture['cycle_de_vie_jours']); ?> jours</span></p>
                                <p><strong><i class="fas fa-tint"></i> Besoins en eau :</strong> <span><?= htmlspecialchars($culture['besoins_eau_mm']); ?> mm</span></p>
                                <p><strong><i class="fas fa-flask"></i> Besoins nutriments :</strong> <span><?= htmlspecialchars($culture['besoins_nutriments']); ?></span></p>
                                <p><strong><i class="<?= $maladie_icon; ?>"></i> Sensibilité maladies :</strong> <span><?= htmlspecialchars($culture['sensibilite_maladies']); ?></span></p>
                                
                                <div class="info-generale">
                                     <strong><i class="fas fa-info-circle"></i> Informations Générales :</strong>
                                     *<?= htmlspecialchars($culture['informations_generales']); ?>*
                                </div>
                                
                                <p class="agriculteur-info">
                                    <small>Enregistrée par : <span style="font-weight: 600; color: var(--color-text-dark);"><?= htmlspecialchars($culture['nom_agriculteur']); ?></span></small>
                                </p>
                            </div>
                            
                            <div class="card-actions-culture">
                                <a href="modifier_culture.php?id=<?= $culture['id']; ?>" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="supprime_culture.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer la culture : <?= htmlspecialchars($culture['nom_commun']); ?> ?');" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $culture['id']; ?>" >
                                    <button type="submit" title="Supprimer" class="delete-button">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?> 
                <p><i class="fas fa-info-circle"></i> Aucune culture enregistrée. Cliquez sur "**Ajouter Culture**" pour commencer.</p> 
            <?php endif; ?>
        </div> 
    </div>
</body>
</html>
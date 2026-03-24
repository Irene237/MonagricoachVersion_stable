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
$recoms = [];
$error_message = null;

try{
    $sql ="SELECT r.id, r.date_recom, r.statut_recom, r.contenu_recom, p.statut_plantation AS statut_plantation, u.nom AS nom_conseiller 
           FROM recommandation AS r 
           JOIN plantation AS p ON r.id_plantation = p.id 
           JOIN utilisateur AS u ON r.id_conseiller = u.id 
           WHERE u.type_utilisateur = 'conseiller_agricole' AND r.id_utilisateur_recom = :userId 
           ORDER BY r.date_recom DESC"; 
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $recoms = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Erreur de récupération des recommandations : " . $e->getMessage();
}

// Fonction pour déterminer la couleur et l'icône du statut de la recommandation
function get_status_style($statut_recom) {
    $statut = strtolower($statut_recom);
    if (strpos($statut, 'urgent') !== false || strpos($statut, 'critique') !== false) {
        return ['class' => 'status-critical', 'icon' => 'fas fa-exclamation-circle', 'color' => '#E91E63']; // Rose/Rouge Crise
    } elseif (strpos($statut, 'terminé') !== false || strpos($statut, 'effectué') !== false) {
        return ['class' => 'status-completed', 'icon' => 'fas fa-check-double', 'color' => '#4CAF50']; // Vert Succès
    } elseif (strpos($statut, 'en cours') !== false || strpos($statut, 'planifié') !== false) {
        return ['class' => 'status-pending', 'icon' => 'fas fa-clock', 'color' => '#FFC107']; // Jaune Attente
    } else {
        return ['class' => 'status-new', 'icon' => 'fas fa-bell', 'color' => '#06bdbdff']; // Émeraude Nouveau
    }
}
?>

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Liste des Recommandations | MonAgriCoach</title> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- PALETTE HARMONISÉE --- */
        :root {
            --color-primary-emerald: #06bdbdff; 
            --color-primary-dark: #0cb4b4ff; 
            --color-secondary-gold: #FF8C00; 
            --color-secondary-gold-hover: #E37D00;
            --color-heading: #111827; 
            --color-card-bg: #FFFFFF; 
            --color-light-bg: #F5F8F5; 
            --color-text-dark: #374151;
            --color-text-medium: #4B5563; 
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

        /* --- BARRE LATÉRALE (SIDEBAR) - NOUVEAU STYLE --- */
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

        /* --- Formulaire de Recherche --- */
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
            background-color: var(--color-primary-emerald); 
            color: var(--color-card-bg); 
            box-shadow: 0 2px 5px rgba(6, 189, 189, 0.2);
        }

        /* --- STYLE DES CARTES DE RECOMMANDATION --- */
        
        .recom-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); /* 2 ou 3 colonnes selon la taille */
            gap: 25px;
            padding: 0;
            list-style: none;
            margin-top: 20px;
        }

        .recom-item {
            background-color: var(--color-card-bg);
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid #E5E7EB;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s;
        }
        
        .recom-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        /* En-tête : Statut (Bande supérieure colorée) */
        .recom-status-bar {
            padding: 10px 20px;
            color: white;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 15px;
        }
        
        .recom-status-bar i {
            margin-right: 8px;
            font-size: 16px;
        }

        /* Corps de la Recommandation */
        .recom-body {
            padding: 20px;
            flex-grow: 1; /* Permet à la carte de s'étirer si le contenu est court */
            font-size: 14px;
            line-height: 1.6;
            color: var(--color-text-dark);
        }
        
        .recom-body strong {
            font-weight: 600;
            color: var(--color-heading);
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        /* Pied de Page (Conseiller, Contexte & Actions) */
        .recom-footer {
            padding: 15px 20px;
            background-color: #F8FAFC; 
            border-top: 1px solid #EEE;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .recom-metadata {
            font-size: 12px;
            color: var(--color-text-medium);
            line-height: 1.8;
        }

        .recom-metadata span {
            display: block;
            font-weight: 500;
        }

        .recom-metadata strong {
            font-weight: 700;
            color: var(--color-heading);
        }

        /* Bloc Action (Supprimer) */
        .delete-button {
            width: 35px; 
            height: 35px; 
            border-radius: 50%; 
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            background-color: #EF4444; 
            color: var(--color-card-bg);
            transition: background-color 0.2s, transform 0.2s;
            font-size: 16px;
        }
        .delete-button:hover {
            background-color: #DC2626; 
            transform: scale(1.1);
        }
        
        /* Message Aucun élément */
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
        <a href="index.php" class="logo">
            <i class="fas fa-leaf"></i> 
            MonAgriCoach
        </a>
        
        <ul>
            <li><a href="farmer_dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a></li>
            <li> <a href="liste_parcelle.php"><i class="fas fa-map-marker-alt"></i> Parcelles</a></li> 
            <li> <a href="liste_plantation.php"><i class="fas fa-seedling"></i> Plantations</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i> Cultures</a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> Engrais</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb"></i> **Recommandations**</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i> Messagerie</a></li>
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i> Verser l'engrais</a></li>
            <li> <a href="liste_st_intrant.php"><i class="fas fa-warehouse"></i> Stock engrais</a></li>
            <li><a href="liste_re.php"><i class="fas fa-chart-bar"></i> Rendement</a></li>
            <li><a href="liste_taches.php"><i class="fas fa-tasks"></i> Tâches</a></li>
        </ul>
        
        <div class="btn_lien">
            <button ><a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i> DÉCONNEXION</a></button>
        </div>
    </nav>

    <div class="main-content">
        <div class="header">
            <h1>Mes Recommandations</h1>
        </div>

        <?php if (isset($error_message)): ?>
            <p class="error-php-message" style="color: #991B1B; background-color: #FEE2E2; padding: 15px; border-radius: 8px; font-weight: 600; margin-bottom: 30px;"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>
        
        <div class="content-container">
            <h2>Gérer l'ensemble de vos recommandations</h2>
            
            <form action="recherche_recom.php" method="GET" class="search-form">
                <label for="search_query" style="display:none;">Rechercher par nom du conseiller</label>
                <input type="text" name="recherche" placeholder="Recherche par nom du conseiller...">
                
                <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
                <button type="button" onclick="window.location.href='liste_recom.php'" style="background-color: #D1D5DB; color: var(--color-text-dark); box-shadow: none;"><i class="fas fa-sync-alt"></i> Réinitialiser</button>
            </form>

            <?php if (count($recoms) > 0): ?>
                <ul class="recom-list">
                    <?php foreach($recoms as $recom ): 
                        $style = get_status_style($recom['statut_recom']);
                    ?>
                        <li class="recom-item">
                            
                            <div class="recom-status-bar" style="background-color: <?= $style['color']; ?>;">
                                <div class="statut">
                                    <i class="<?= $style['icon']; ?>"></i> 
                                    Statut : **<?= htmlspecialchars($recom['statut_recom']); ?>**
                                </div>
                                <div class="date">
                                    <i class="fas fa-calendar-alt"></i> 
                                    <?= date('d/m/Y', strtotime($recom['date_recom'])); ?>
                                </div>
                            </div>
                            
                            <div class="recom-body">
                                <strong><i class="fas fa-comment-dots" style="color: <?= $style['color']; ?>;"></i> Message du Conseiller :</strong>
                                <?= nl2br(htmlspecialchars($recom['contenu_recom'])); ?>
                            </div>
                            
                            <div class="recom-footer">
                                <div class="recom-metadata">
                                    <span>Conseiller : <strong><?= htmlspecialchars($recom['nom_conseiller']); ?></strong></span>
                                    <span>Contexte Plantation : <strong><?= htmlspecialchars($recom['statut_plantation']); ?></strong></span>
                                </div>

                                <div class="recom-actions">
                                    <form action="supprime_recom.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette recommandation ?');" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $recom['id']; ?>" >
                                        <button type="submit" title="Supprimer la recommandation" class="delete-button">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li> 
                    <?php endforeach;?>
                </ul>
            <?php else:?> 
                <p><i class="fas fa-info-circle"></i> Aucune recommandation trouvée pour l'instant. Votre conseiller n'a pas encore envoyé de conseils.</p> 
            <?php endif;?>
        </div> 
    </div>
</body>
</html>
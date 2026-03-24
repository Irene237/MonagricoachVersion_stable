<?php
// Démarre la session pour utiliser les variables de session et les messages flash
session_start();
// Assurez-vous que 'db.php' est correctement configuré.
require_once 'db.php';

// --- Bloc de Vérification de Connexion ---
if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) {
    // Redirige si l'utilisateur n'est pas connecté
    header("Location: connexion.php");
    exit();
}

$id_agriculteur = $_SESSION['user_id'];
$stintrants = [];
$error_message = null;

try{
    // Requête SQL mise à jour : i.unite_mesure a été retirée.
    $sql ="SELECT 
                s.id, 
                s.quantite_actuelle,
                s.seuil_alerte,
                s.date_derniere_mise_a_jour,
                u.nom AS nom_agriculteur,
                i.nom_intrant AS nom_intrant 
            FROM stock_intrant AS s 
            JOIN intrant AS i ON s.id_intrant = i.id 
            JOIN utilisateur AS u ON s.id_agriculteur = u.id 
            WHERE u.type_utilisateur = 'agriculteur' 
            AND s.id_agriculteur = :id_agriculteur 
            ORDER BY 
                CASE 
                    WHEN s.quantite_actuelle <= s.seuil_alerte THEN 0 
                    ELSE 1 
                END, 
                s.quantite_actuelle ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_agriculteur', $id_agriculteur, PDO::PARAM_INT);
    $stmt->execute();
    $stintrants = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Erreur de récupération des stocks d'intrants : " . $e->getMessage();
}

// Récupère la notification de session (message flash) et l'efface immédiatement
$flash_message = null;
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}
?>

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Liste des Stocks Intrants | MonAgriCoach</title> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- PALETTE HARMONISÉE --- */
        :root {
            --color-primary-emerald: #06bdbd;
            --color-primary-dark: #0cb4b4;
            
            --color-secondary-gold: #FF8C00; 
            --color-secondary-gold-hover: #E37D00;

            --color-accent-danger: #ef4444; 
            --color-accent-warning: #F59E0B; /* Pour les seuils d'alerte */
            --color-accent-success: #10B981; /* Pour les messages de succès */
            
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
            display: flex;
            align-items: center;
            justify-content: center;
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
        }

        .sidebar li a:hover {
            background-color: #f5f8f8;
            color: var(--color-primary-dark);
        }

        /* Lien Actif : Stock Intrant */
        .sidebar li a[href="liste_st_intrant.php"] {
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-emerald); 
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald); 
        }
        .sidebar li a[href="liste_st_intrant.php"] i {
             color: var(--color-primary-emerald); 
        }
        
        /* Bouton Déconnexion */
        .btn-deconnexion-wrapper {
            margin-top: auto; 
            padding: 25px; 
        }
        
        .btn_deconnexion {
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
            width: 100%;
            box-sizing: border-box;
        }
        .btn_deconnexion:hover { 
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

        /* --- Conteneur et Formulaire --- */
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

        /* --- GRILLE DE CARTES --- */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .intrant-card {
            background: var(--color-card-bg);
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }
        
        .intrant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #F3F4F6;
        }

        .card-icon {
            font-size: 28px;
            color: var(--color-primary-emerald);
            margin-right: 15px;
        }

        .card-title {
            font-family: var(--font-heading);
            font-size: 20px;
            font-weight: 700;
            color: var(--color-heading);
            margin: 0;
            line-height: 1.2;
        }

        .card-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px 10px;
            margin-bottom: 20px;
        }

        .detail-item strong {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--color-text-medium);
            margin-bottom: 4px;
        }
        
        .detail-value {
            font-size: 16px;
            font-weight: 600;
            color: var(--color-text-dark);
        }
        
        .detail-value .date {
            font-size: 15px;
            font-weight: 500;
            color: var(--color-text-medium);
        }

        /* Alerte et Quantité */
        .alert-indicator {
            position: absolute;
            top: 0;
            right: 0;
            padding: 8px 15px;
            border-radius: 0 12px 0 12px;
            font-weight: 700;
            font-size: 13px;
        }
        
        .status-ok {
            background-color: rgba(6, 189, 189, 0.1);
            color: var(--color-primary-emerald);
        }

        .status-low {
            background-color: rgba(245, 158, 11, 0.15);
            color: var(--color-accent-warning);
        }
        
        .quantite-actuelle {
            font-size: 24px;
            font-family: var(--font-heading);
            color: var(--color-primary-emerald);
        }
        .intrant-card.status-low .quantite-actuelle {
            color: var(--color-accent-warning);
        }
        
        .card-actions {
            display: flex;
            justify-content: flex-end; 
            gap: 10px;
            padding-top: 15px;
            border-top: 1px dashed #E5E7EB;
        }

        .card-actions a, .card-actions button {
            padding: 8px 15px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            cursor: pointer;
        }

        .action-modifier {
            background-color: var(--color-primary-emerald);
            color: var(--color-card-bg);
            box-shadow: 0 2px 5px rgba(6, 189, 189, 0.3);
        }
        .action-modifier:hover {
            background-color: var(--color-primary-dark);
        }

        .action-supprimer {
            background-color: var(--color-accent-danger);
            color: var(--color-card-bg);
            box-shadow: 0 2px 5px rgba(239, 68, 68, 0.3);
        }
        .action-supprimer:hover {
            background-color: #CC3737;
        }
        
        /* --- Styles de Notification Flash --- */
        .flash-notification {
            padding: 15px 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .flash-notification i {
            font-size: 24px;
        }

        .flash-success {
            background-color: #E6FFF6;
            color: var(--color-accent-success);
            border-left: 5px solid var(--color-accent-success);
        }
        .flash-warning {
            background-color: #FFFBEA;
            color: var(--color-accent-warning);
            border-left: 5px solid var(--color-accent-warning);
        }
        .flash-danger {
            background-color: #FEE2E2;
            color: var(--color-accent-danger);
            border-left: 5px solid var(--color-accent-danger);
        }
        
        /* Message d'erreur/absence */
        .no-records {
             background-color: #E5E7EB;
             color: var(--color-text-dark);
             border: 1px solid #D1D5DB;
             padding: 20px;
             border-radius: 10px;
             font-weight: 500;
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
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> Engrais</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i> Messagerie</a></li>
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i> verser engrais</a></li>
            <li> <a href="liste_st_intrant.php"><i class="fas fa-warehouse"></i> **Stock engrais**</a></li>
            <li><a href="liste_re.php"><i class="fas fa-chart-bar"></i> Rendement</a></li>
            <li><a href="liste_taches.php"><i class="fas fa-tasks"></i> Tâches</a></li> 
            
        </ul>
        
        <div class="btn-deconnexion-wrapper"> 
            <a href="deconnexion.php" class="btn_deconnexion">
                <i class="fas fa-sign-out-alt"></i> DÉCONNEXION
            </a>
        </div>
    </nav>

    <div class="main-content">
        <div class="header">
            <h1>Gestion des Stocks d'Engrais</h1>
            <a href="stock_intrant.php" class="add-new-link">
                <i class="fas fa-plus-circle"></i> Ajouter un Stock
            </a> 
        </div>

        <?php if ($flash_message): ?>
            <?php 
                $icon = '';
                if ($flash_message['type'] === 'success') {
                    $icon = 'fa-check-circle';
                } elseif ($flash_message['type'] === 'warning') {
                    $icon = 'fa-exclamation-triangle';
                } elseif ($flash_message['type'] === 'danger') {
                    $icon = 'fa-times-circle';
                }
            ?>
            <div class="flash-notification flash-<?= htmlspecialchars($flash_message['type']); ?>">
                <i class="fas <?= $icon; ?>"></i>
                <?= nl2br(htmlspecialchars($flash_message['message'])); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <p class="flash-notification flash-danger"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>
        
        <div class="content-container">
            <h2>Gérer l'ensemble de vos stocks d'engrais</h2>
            
            <form action="recherche_st_intrant.php" method="GET" class="search-form">
                <label for="search_query" style="display:none;">Rechercher un stock intrant</label>
                <input type="text" name="recherche" placeholder="Recherche par nom d'intrant..." id="search_query">
                
                <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
                <button type="button" onclick="window.location.href='liste_st_intrant.php'"><i class="fas fa-sync-alt"></i> Réinitialiser</button>
            </form>

            <?php if (count($stintrants) > 0): ?>
                <div class="cards-grid">
                    <?php foreach($stintrants as $stintrant ): 
                        // Détermine le statut du stock
                        $is_low_stock = ($stintrant['quantite_actuelle'] <= $stintrant['seuil_alerte']);
                        $status_class = $is_low_stock ? 'status-low' : 'status-ok';
                        $status_text = $is_low_stock ? 'Alerte Stock' : 'Stock OK';
                        
                        // Formate la quantité
                        $quantite_display = number_format((float)$stintrant['quantite_actuelle'], 2, ',', ' ');
                        $seuil_display = number_format((float)$stintrant['seuil_alerte'], 2, ',', ' ');
                    ?>
                        <div class="intrant-card <?= $status_class; ?>">
                            
                            <div class="alert-indicator <?= $status_class; ?>">
                                <i class="fas <?= $is_low_stock ? 'fa-exclamation-triangle' : 'fa-check-circle'; ?>"></i> <?= $status_text; ?>
                            </div>

                            <div class="card-header">
                                <i class="fas fa-box card-icon"></i>
                                <h3 class="card-title"><?= htmlspecialchars($stintrant['nom_intrant']); ?></h3>
                            </div>
                            
                            <div class="card-details">
                                <div class="detail-item">
    <strong>Quantité Actuelle</strong>
    <span class="detail-value quantite-actuelle" style="<?= ($stintrant['quantite_actuelle'] <= $stintrant['seuil_alerte']) ? 'color: #e74c3c; font-weight: bold;' : ''; ?>">
        <?= htmlspecialchars($stintrant['quantite_actuelle']); ?> 
        <?= htmlspecialchars($stintrant['unite_mesure'] ?? 'kg'); ?>
    </span>
    
    
</div>
                                <div class="detail-item">
                                    <strong>Seuil d'Alerte</strong>
                                    <span class="detail-value"><?= $seuil_display; ?></span>
                                </div>
                                <div class="detail-item">
                                    <strong>Dernière Mise à Jour</strong>
                                    <span class="detail-value date"><?= date('d/m/Y', strtotime($stintrant['date_derniere_mise_a_jour'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <strong>Agriculteur</strong>
                                    <span class="detail-value"><?= htmlspecialchars($stintrant['nom_agriculteur']); ?></span>
                                </div>
                            </div>

                            <div class="card-actions">
                                <a href="modifier_st_intrant.php?id=<?= $stintrant['id']; ?>" title="Modifier le Stock" class="action-modifier">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <form action="supprime_st_intrant.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce stock?');" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $stintrant['id']; ?>" >
                                    <button type="submit" title="Supprimer le Stock" class="action-supprimer">
                                        <i class="fas fa-trash-alt"></i> Supprimer
                                    </button>
                                </form>
                            </div>
                        </div> 
                    <?php endforeach;?>
                </div>
            <?php else:?> 
                <p class="no-records"><i class="fas fa-info-circle"></i> Aucun stock d'intrant enregistré. Cliquez sur "**Ajouter un Stock**" pour commencer.</p> 
            <?php endif;?>
        </div> 
    </div>
</body>
</html>
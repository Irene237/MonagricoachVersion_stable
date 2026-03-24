<?php
// Démarre la session au tout début du script
session_start();

// Inclut le fichier de connexion à la base de données (db.php)
require_once 'db.php'; 

// --- Bloc de Vérification de Connexion ---
// Vérifie si l'utilisateur est connecté. Sinon, le redirige vers la page de connexion.
if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) {
    header("Location: connexion.php"); // Redirection vers 'connexion.php'
    exit();
}

$id_agriculteur_assiste = $_SESSION['user_id'];
$cons = [];
$errorMessage = '';

try {
    // Requête pour obtenir l'historique complet des messages pour l'agriculteur connecté.
    // CORRECTION LOGIQUE : Ajout de la jointure pour récupérer le nom/prénom du conseiller
    $sql = "SELECT 
                m.id, 
                m.date_debut_assistance,
                m.date_fin_assistance, 
                m.message_agriculteur, 
                m.message_conseiller,
                u.nom AS nom_conseiller,
                u.prenom AS prenom_conseiller
            FROM assistance_conseiller AS m 
            JOIN utilisateur AS u ON m.id_conseiller = u.id 
            WHERE m.id_agriculteur_assiste = :id_agriculteur_assiste 
            ORDER BY m.date_debut_assistance DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_agriculteur_assiste', $id_agriculteur_assiste, PDO::PARAM_INT);
    $stmt->execute();
    $cons = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Gestion d'erreur
    error_log("Erreur de récupération des conseils: " . $e->getMessage());
    $errorMessage = "Erreur lors du chargement de l'historique des conseils. Veuillez réessayer.";
}

// Fonction pour déterminer le statut (Répondu/En attente) avec les NOUVELLES classes CSS
function get_status_tag($message_conseiller) {
    if (!empty(trim($message_conseiller))) {
        // NOUVELLE classe pour statut répondu
        return '<span class="status-tag status-respondu"><i class="fas fa-check-circle"></i> Répondu</span>';
    } else {
        // NOUVELLE classe pour statut en attente
        return '<span class="status-tag status-attente"><i class="fas fa-clock"></i> En attente</span>';
    }
}

// Fonction pour tronquer le message sans couper les mots
function truncate_message($message, $limit = 150) {
    if (empty($message)) return ""; // Correction pour éviter erreur sur message vide
    if (strlen($message) > $limit) {
        // Coupe à la limite
        $truncated = substr($message, 0, $limit);
        // Assurez-vous de ne pas couper un mot
        $last_space = strrpos($truncated, ' ');
        if($last_space !== false) {
            $truncated = substr($truncated, 0, $last_space);
        }
        return $truncated . '...';
    }
    return $message;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Conseils | MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* ----------------------------------------------------- */
        /* --- STYLES GLOBALS & PALETTE --- */
        /* ----------------------------------------------------- */
        :root {
            --color-primary-emerald: #06bdbdff; /* Vert Émeraude Vif */
            --color-primary-dark: #0cb4b4ff; 
            
            --color-secondary-gold: #FF8C00; /* Orange Vif (Déconnexion) */

            --color-accent-danger: #ef4444; /* Rouge pour les erreurs/danger */
            
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

        /* Lien Actif : Mes messages */
        .sidebar li a[href="liste_message_agri.php"] {
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-emerald); 
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald); 
        }
        .sidebar li a[href="liste_message_agri.php"] i {
            color: var(--color-primary-emerald); 
        }
        
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
        
        /* ----------------------------------------------------- */
        /* --- CONTENU PRINCIPAL & HEADER --- */
        /* ----------------------------------------------------- */
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
        
        /* Bouton Ajouter/Demander un conseil (Émeraude) */
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
            box-shadow: 0 6px 15px rgba(6, 189, 189, 0.4);
        }

        .content-container {
            padding: 0; 
            border-radius: 15px; 
            box-shadow: none;
        }
        
        .content-container h2 {
            font-family: var(--font-heading); 
            font-weight: 700;
            font-size: 24px; 
            color: var(--color-heading);
            margin-top: 0;
            margin-bottom: 25px;
            padding-left: 10px;
        }

        /* --- STYLES DU FORMULAIRE DE RECHERCHE --- */
        .search-form {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            align-items: center;
            padding: 15px 20px; 
            border-radius: 12px;
            background-color: var(--color-card-bg); 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); 
        }

        .search-form input[type="text"] {
            flex-grow: 1;
            padding: 12px 15px;
            border: 1px solid #D1D5DB; 
            border-radius: 8px;
            font-size: 15px;
            background-color: var(--color-light-bg); 
            color: var(--color-text-dark);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .search-form input[type="text"]:focus {
            outline: none;
            border-color: var(--color-primary-emerald);
            box-shadow: 0 0 0 3px rgba(6, 189, 189, 0.2);
        }

        .search-form button {
            padding: 10px 18px; 
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .search-form button[type="submit"] {
            background-color: var(--color-primary-emerald); 
            color: var(--color-card-bg); 
            box-shadow: 0 4px 10px rgba(6, 189, 189, 0.3);
        }
        .search-form button[type="submit"]:hover {
            background-color: var(--color-primary-dark);
            box-shadow: 0 6px 15px rgba(6, 189, 189, 0.4);
        }
        
        .search-form button[type="button"] { 
            background-color: #F3F4F6; 
            color: var(--color-text-dark);
            border: 1px solid #D1D5DB;
        }
        .search-form button[type="button"]:hover {
            background-color: #E5E7EB;
        }

        /* Messages d'erreur et d'absence de données */
        .error-message {
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
        
        /* ----------------------------------------------------- */
        /* --- STYLES DE GRILLE DE CARTES --- */
        /* ----------------------------------------------------- */
        
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
            gap: 30px;
            padding: 10px;
        }

        .conseil-card {
            background-color: var(--color-card-bg);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            min-height: 350px; 
        }

        .conseil-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }
        
        /* --- Détails Conseiller/Date --- */
        .card-header {
            margin-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .card-header h3 {
            font-family: var(--font-heading);
            font-size: 18px;
            font-weight: 700;
            color: var(--color-heading);
            margin-top: 0;
            margin-bottom: 5px;
        }
        .card-header p {
            margin: 0;
            font-size: 13px;
            color: var(--color-text-medium);
            line-height: 1.5;
        }

        /* --- Zone Message Agriculteur --- */
        .agriculteur-message {
            margin-bottom: 15px;
            padding: 10px 15px;
            background-color: var(--color-light-bg);
            border-radius: 8px;
            border-left: 3px solid var(--color-primary-emerald);
        }
        .agriculteur-message strong {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--color-text-dark);
            margin-bottom: 5px;
        }
        .agriculteur-message p {
            font-size: 13px;
            color: var(--color-text-medium);
            margin: 0;
            max-height: 60px; 
            overflow: hidden;
            line-height: 1.4;
        }
        
        /* --- Zone Réponse Conseiller --- */
        .conseiller-reponse {
            flex-grow: 1; 
            margin-bottom: 20px;
            padding: 10px 15px;
            border-radius: 8px;
            background-color: #e6f7ff; 
            border-left: 3px solid var(--color-primary-dark);
        }
        .conseiller-reponse strong {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--color-text-dark);
            margin-bottom: 5px;
        }
        .conseiller-reponse p {
            font-size: 13px;
            color: var(--color-text-medium);
            margin: 0;
            max-height: 60px; 
            overflow: hidden;
            line-height: 1.4;
        }
        .conseiller-reponse .pending-message {
            color: #888;
            font-style: italic;
        }

        /* --- Pied de carte (Statut & Actions) --- */
        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto; 
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }
        
        /* Statut Badge (Tag) */
        .status-tag {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-respondu {
            color: #15803d; 
            background-color: #dcfce7; 
        }
        .status-attente {
            color: #b45309; 
            background-color: #fef3c7; 
        }
        .status-tag i {
            margin-right: 5px;
            font-size: 11px;
        }

        /* Actions dans le pied de carte */
        .card-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        /* Bouton Répondre */
        .reply-button { 
            background-color: var(--color-primary-emerald);
            color: white; 
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.2s, box-shadow 0.2s;
        }
        .reply-button:hover {
            background-color: var(--color-primary-dark);
            box-shadow: 0 2px 5px rgba(6, 189, 189, 0.4);
        } 

        /* Bouton Supprimer (Rouge) */
        .delete-button {
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
            background-color: #FEE2E2; 
            color: var(--color-accent-danger); 
        }
        .delete-button:hover {
            background-color: var(--color-accent-danger); 
            color: var(--color-card-bg);
            transform: scale(1.05); 
        }

        /* Message d'absence de données */
        .no-data-message {
            text-align: center;
            padding: 40px;
            font-size: 16px;
            color: var(--color-text-medium);
            background-color: var(--color-card-bg); 
            border-radius: 8px;
            margin-top: 30px;
            font-weight: 500;
            border: 1px dashed var(--color-primary-emerald); 
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
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i> **Messagerie**</a></li>
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
        <h1>Historique de mes demandes de conseil</h1>
        <a href="redige_conseil.php" class="add-new-link"> <i class="fas fa-plus-circle"></i> Demander un conseil</a>
    </div>

    <?php if (!empty($errorMessage)): ?>
            <p class="error-message">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($errorMessage); ?>
            </p>
    <?php endif; ?>
    
    <div class="content-container">
        
        <form action="recherche_mesa_agri.php" method="GET" class="search-form">
            <label for="search_query" style="display:none;">Rechercher par message</label>
            <input type="text" name="recherche" placeholder="Rechercher par nom du conseiller..." id="search_query">
            <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
            <button type="button" onclick="window.location.href='liste_message_agri.php'"><i class="fas fa-sync-alt"></i> Réinitialiser</button>
        </form>

        <?php if (count($cons) > 0): ?>
            <div class="card-grid">
                <?php foreach($cons as $con): ?>
                    <div class="conseil-card">
                        
                        <div class="card-header">
                            <h3><?= get_status_tag($con['message_conseiller']); ?></h3>
                            <p>
                                <strong>Conseiller:</strong> <?= htmlspecialchars($con['prenom_conseiller'] . ' ' . $con['nom_conseiller']); ?><br>
                                <strong>Date Demande:</strong> <?= ($con['date_debut_assistance'] != '0000-00-00') ? date('d/m/Y', strtotime($con['date_debut_assistance'])) : 'N/A'; ?>
                            </p>
                        </div>
                        
                        <div class="agriculteur-message">
                            <strong>Votre message (Demande)</strong>
                            <p>
                                <?= nl2br(htmlspecialchars(truncate_message($con['message_agriculteur']))); ?>
                            </p>
                        </div>
                        
                        <div class="conseiller-reponse">
                            <strong>Réponse du Conseiller</strong>
                            <?php if (!empty(trim($con['message_conseiller']))): ?>
                                <p>
                                    <span style="font-style: italic;">(Répondu le <?= date('d/m/Y', strtotime($con['date_fin_assistance'])); ?>)</span><br>
                                    <?= nl2br(htmlspecialchars(truncate_message($con['message_conseiller']))); ?>
                                </p>
                            <?php else: ?>
                                <p class="pending-message">
                                    En attente de la réponse du conseiller...
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="card-footer">
                            <div class="card-actions">
                                <a href="reponse.php?id=<?= $con['id']; ?>" title="Ajouter une réponse ou faire une nouvelle demande" class="reply-button">
                                    <i class="fas fa-reply"></i> Répondre
                                </a>
                                
                                <form action="supprimer_mesage.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ? Cette action est irréversible.');">
                                    <input type="hidden" name="id" value ="<?= $con['id']; ?>" >
                                    <button type="submit" title="Supprimer la demande" class="delete-button">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div> 
                <?php endforeach;?>
            </div> 
        <?php else:?> 
                <p class="no-data-message"><i class="fas fa-info-circle"></i> <strong>Aucun conseil trouvé pour le moment.</strong> Cliquez sur "Demander un conseil" pour contacter votre conseiller.</p> 
        <?php endif;?>
        
    </div> 
</div>
</body>
</html>
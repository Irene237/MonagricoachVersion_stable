<?php
// Démarre la session au tout début du script
session_start();

// Inclut le fichier de connexion à la base de données (db.php)
require_once 'db.php'; 

// --- Bloc de Vérification de Connexion (inchangé) ---
if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) {
    header("Location: connexion.php"); // Redirection vers 'connexion.php'
    exit();
}

$id_agriculteur_assiste = $_SESSION['user_id'];
$message_id = isset($_GET['id']) ? (int)$_GET['id'] : null; 
$conseiller_id = null;
$nom_conseiller = "Conseiller";
$messages_du_thread = []; 
$errorMessage = '';
$successMessage = '';

// --- 1. Récupération du Message Initial et Sécurité (inchangé) ---
if ($message_id) {
    try {
        $sql = "SELECT r.id_conseiller, u.nom, u.prenom 
                FROM assistance_conseiller AS r
                JOIN utilisateur AS u ON r.id_conseiller = u.id
                WHERE r.id = :message_id AND r.id_agriculteur_assiste = :id_agriculteur_assiste";
                
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':message_id', $message_id, PDO::PARAM_INT);
        $stmt->bindParam(':id_agriculteur_assiste', $id_agriculteur_assiste, PDO::PARAM_INT);
        $stmt->execute();
        $message_initial_clique = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($message_initial_clique) {
            $conseiller_id = $message_initial_clique['id_conseiller'];
            $nom_conseiller = htmlspecialchars($message_initial_clique['prenom'] . ' ' . $message_initial_clique['nom']);

            $sql_thread = "SELECT 
                                id,
                                message_agriculteur, 
                                message_conseiller, 
                                date_debut_assistance AS date_envoi_agri,
                                date_fin_assistance AS date_envoi_conseil
                            FROM assistance_conseiller
                            WHERE id_conseiller = :conseiller_id 
                            AND id_agriculteur_assiste = :id_agriculteur_assiste
                            ORDER BY date_envoi_agri ASC"; 

            $stmt_thread = $pdo->prepare($sql_thread);
            $stmt_thread->bindParam(':conseiller_id', $conseiller_id, PDO::PARAM_INT);
            $stmt_thread->bindParam(':id_agriculteur_assiste', $id_agriculteur_assiste, PDO::PARAM_INT);
            $stmt_thread->execute();
            $messages_du_thread = $stmt_thread->fetchAll(PDO::FETCH_ASSOC);

        } else {
            $errorMessage = "Message introuvable ou non autorisé. Vous ne pouvez accéder qu'à vos propres messages.";
            $message_id = null;
        }

    } catch (PDOException $e) {
        error_log("Erreur de récupération du message: " . $e->getMessage());
        $errorMessage = "Erreur lors du chargement des détails du message.";
        $message_id = null;
    }
} else {
    header("Location: liste_message_agri.php");
    exit();
}


// --- 2. Traitement du Formulaire de Réponse (POST) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && $conseiller_id) {
    $nouveau_message = trim($_POST['nouveau_message'] ?? '');
    $conseiller_id_post = (int)($_POST['conseiller_id'] ?? 0);

    if (empty($nouveau_message)) {
        $errorMessage = "Votre message de suivi ne peut pas être vide.";
    } elseif ($conseiller_id_post !== $conseiller_id) {
        $errorMessage = "Erreur de sécurité : Conseiller invalide pour cette conversation.";
    } else {
        try {
            $sql_insert = "INSERT INTO assistance_conseiller 
                           (id_agriculteur_assiste, id_conseiller, message_agriculteur, date_debut_assistance) 
                           VALUES 
                           (:id_agriculteur_assiste, :id_conseiller, :message_agriculteur, NOW())";
            
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->bindParam(':id_agriculteur_assiste', $id_agriculteur_assiste, PDO::PARAM_INT);
            $stmt_insert->bindParam(':id_conseiller', $conseiller_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(':message_agriculteur', $nouveau_message, PDO::PARAM_STR);
            
            if ($stmt_insert->execute()) {
                // 👇 MODIFICATION APPLIQUÉE ICI 👇
                // Redirection vers liste_message_agri.php
                header("Location: liste_message_agri.php?success=1"); 
                exit();
            } else {
                $errorMessage = "Erreur lors de l'enregistrement de votre message.";
            }

        } catch (PDOException $e) {
            error_log("Erreur d'insertion du message: " . $e->getMessage());
            $errorMessage = "Erreur de base de données. Veuillez réessayer.";
        }
    }
}

// Gérer l'affichage du succès après la redirection (CE BLOC DEVIENT INUTILE ICI car la redirection est externe)
/*
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $successMessage = "Votre message de suivi a été envoyé avec succès.";
}
*/
// Si vous voulez afficher le message de succès, vous devrez le gérer dans liste_message_agri.php
// car la redirection vous emmène là-bas.
// Pour cette page, nous le commentons/supprimons.
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation avec <?= $nom_conseiller; ?> | MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* ----------------------------------------------------- */
        /* --- STYLES GLOBALS & PALETTE --- */
        /* ----------------------------------------------------- */
        :root {
            --color-primary-emerald: #06bdbdff; 
            --color-primary-dark: #0cb4b4ff; 
            --color-secondary-gold: #FF8C00; /* Orange Vif (Déconnexion) */
            --color-accent-danger: #ef4444; 
            --color-accent-success: #10b981;
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

        /* ----------------------------------------------------- */
        /* --- BARRE LATÉRALE (SIDEBAR) --- */
        /* ----------------------------------------------------- */
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

        /* Lien Actif : Mes messages (pour la cohérence, même si on est dans la sous-page) */
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
        /* --- CONTENU PRINCIPAL & HEADER (Centrage) --- */
        /* ----------------------------------------------------- */
        .main-content {
            padding: 50px 40px; 
            flex-grow: 1;
            max-width: 1200px; 
            width: 100%;
            margin-right: auto;
            margin-left: var(--sidebar-width); 
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px; 
            padding-bottom: 20px;
            border-bottom: 1px solid #E5E7EB;
            /* Centrage du header (pour aligner le titre) */
            max-width: 800px; 
            margin-left: auto; 
            margin-right: auto;
        }
        .header h1 {
            font-family: var(--font-heading);
            font-weight: 900; 
            color: var(--color-heading); 
            font-size: 35px; 
            margin: 0;
        }
        
        .back-link {
            text-decoration: none;
            color: var(--color-text-medium);
            font-weight: 500;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: var(--color-primary-emerald);
        }

        /* Messages d'alerte */
        .alert-message {
            padding: 15px; 
            border-radius: 8px; 
            font-weight: 600; 
            /* Centrage des alertes */
            max-width: 800px; 
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 25px; 
            /* Le reste */
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .error-message {
            color: var(--color-accent-danger); 
            background-color: #FEE2E2; 
            border: 1px solid var(--color-accent-danger);
        }
        .success-message {
            color: #065f46; 
            background-color: #D1FAE5; 
            border: 1px solid var(--color-accent-success);
        }

        /* ----------------------------------------------------- */
        /* --- STYLES DE CONVERSATION --- */
        /* ----------------------------------------------------- */
        .conversation-box {
            background-color: var(--color-card-bg);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            /* Centrage de la conversation-box */
            max-width: 800px; 
            margin-left: auto;
            margin-right: auto;
        }
        
        .conversation-history {
            max-height: 600px; 
            overflow-y: auto; 
            padding-right: 10px; 
            margin-bottom: 30px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 20px;
        }

        /* Bulle de message */
        .message-bubble {
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 12px;
            max-width: 80%;
            line-height: 1.5;
            font-size: 15px;
            word-wrap: break-word; 
        }
        .message-bubble strong {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        /* Message de l'agriculteur (Vous) */
        .message-agriculteur {
            background-color: var(--color-primary-emerald);
            color: white;
            margin-left: auto; 
            border-bottom-right-radius: 2px;
        }
        .message-agriculteur strong {
            color: #ffffffd0;
        }

        /* Message du conseiller (Lui) */
        .message-conseiller {
            background-color: #E0F7FA; 
            color: var(--color-text-dark);
            margin-right: auto; 
            border-bottom-left-radius: 2px;
        }
        .message-conseiller strong {
            color: var(--color-primary-dark);
        }
        
        /* --- FORMULAIRE DE RÉPONSE (Bouton à droite) --- */
        .reply-form {
            display: flex;
            flex-direction: column;
        }
        
        .reply-form h3 {
            font-family: var(--font-heading);
            font-size: 20px;
            font-weight: 700;
            color: var(--color-heading);
            margin-bottom: 15px;
        }

        .reply-form textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid #D1D5DB; 
            border-radius: 8px;
            font-size: 15px;
            min-height: 120px;
            resize: vertical;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .reply-form textarea:focus {
            outline: none;
            border-color: var(--color-primary-emerald);
            box-shadow: 0 0 0 3px rgba(6, 189, 189, 0.2);
        }

        /* Conteneur pour aligner le bouton à droite */
        .reply-form-actions {
            display: flex;
            justify-content: flex-end; 
            margin-top: 15px;
        }

        .reply-form button[type="submit"] {
            background-color: var(--color-primary-emerald); 
            color: var(--color-card-bg); 
            padding: 12px 25px; 
            border: none;
            border-radius: 8px;
            font-weight: 600; 
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s ease, box-shadow 0.2s;
            box-shadow: 0 4px 12px rgba(6, 189, 189, 0.4); 
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .reply-form button[type="submit"]:hover { 
            background-color: var(--color-primary-dark); 
            box-shadow: 0 6px 15px rgba(6, 189, 189, 0.6);
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
            <li> <a href="liste_plantation.php"><i class="fas fa-seedling"></i> plantations</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i>  Cultures</a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> Engrais</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i>**Messagerie**</a></li>
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
        <h1>Conversation avec <?= $nom_conseiller; ?></h1>
        
    </div>

    <?php if (!empty($errorMessage)): ?>
        <p class="alert-message error-message">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($errorMessage); ?>
        </p>
    <?php endif; ?>
    
    <div class="conversation-box">

        <div class="conversation-history">
            <?php if (!empty($messages_du_thread)): ?>
                
                <?php foreach ($messages_du_thread as $msg): 
                    // Afficher le message de l'agriculteur (vous)
                    if (!empty($msg['message_agriculteur'])): ?>
                        <div class="message-bubble message-agriculteur">
                            <strong>Vous avez écrit :</strong>
                            <span style="font-size: 11px; font-style: italic; opacity: 0.8;"><?= date('d/m/Y H:i', strtotime($msg['date_envoi_agri'])); ?></span>
                            <hr style="border: none; border-top: 1px solid rgba(255, 255, 255, 0.2); margin: 5px 0;">
                            <?= nl2br(htmlspecialchars($msg['message_agriculteur'])); ?>
                        </div>
                    <?php endif; 

                    // Afficher la réponse du conseiller
                    if (!empty(trim($msg['message_conseiller']))): ?>
                        <div class="message-bubble message-conseiller">
                            <strong>Réponse de <?= $nom_conseiller; ?> :</strong>
                             <span style="font-size: 11px; font-style: italic; opacity: 0.8;"><?= date('d/m/Y H:i', strtotime($msg['date_envoi_conseil'])); ?></span>
                             <hr style="border: none; border-top: 1px solid #d1d5db; margin: 5px 0;">
                            <?= nl2br(htmlspecialchars($msg['message_conseiller'])); ?>
                        </div>
                    <?php endif; ?>
                
                <?php endforeach; ?>

            <?php else: ?>
                <div style="text-align: center; padding: 20px; color: var(--color-text-medium);">
                    <i class="fas fa-comments fa-3x" style="color: #ccc;"></i>
                    <p>Début de la conversation. Envoyez votre premier message de suivi.</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($conseiller_id): ?>
            <form method="POST" class="reply-form">
                <h3>Envoyer un message de suivi à <?= $nom_conseiller; ?></h3>
                
                <input type="hidden" name="conseiller_id" value="<?= $conseiller_id; ?>">
                
                <textarea name="nouveau_message" placeholder="Tapez votre message ici pour continuer la conversation..." required></textarea>
                
                <div class="reply-form-actions">
                    <button type="submit">
                        <i class="fas fa-paper-plane"></i> Envoyer le message
                    </button>
                </div>
            </form>
        <?php else: ?>
             <p class="alert-message error-message"><i class="fas fa-times-circle"></i> Impossible d'envoyer un message : le conseiller n'a pas pu être identifié.</p>
        <?php endif; ?>
        
    </div>
</div>
</body>
</html>
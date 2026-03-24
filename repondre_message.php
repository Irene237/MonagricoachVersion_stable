<?php
// Fichier : repondre_message.php
session_start();
require_once 'db.php'; 

// --- Sécurité ---
if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) {
    header("Location: connexion.php");
    exit();
}

$id_conseiller_connecte = $_SESSION['user_id'];
$message_success = '';
$message_error = '';

// 1. Récupérer l'ID de l'agriculteur
$id_agriculteur_cible = isset($_GET['agri_id']) ? (int)$_GET['agri_id'] : null;

if (!$id_agriculteur_cible) {
    header("Location: liste_message_cons.php"); 
    exit();
}

// 2. Récupérer le nom de l'agriculteur et l'historique des messages
$nom_agriculteur = 'Agriculteur Inconnu';
$messages_du_thread = [];

try {
    // Info agriculteur
    $sql_agri = "SELECT nom, prenom FROM utilisateur WHERE id = :id_agriculteur AND type_utilisateur = 'agriculteur'";
    $stmt_agri = $pdo->prepare($sql_agri);
    $stmt_agri->bindValue(':id_agriculteur', $id_agriculteur_cible, PDO::PARAM_INT);
    $stmt_agri->execute();
    $agri = $stmt_agri->fetch(PDO::FETCH_ASSOC);

    if ($agri) {
        $nom_agriculteur = htmlspecialchars($agri['nom'] . ' ' . $agri['prenom']);

        // RÉCUPÉRATION DE L'HISTORIQUE (Comme dans le thread agriculteur)
        $sql_thread = "SELECT 
                        message_agriculteur, 
                        message_conseiller, 
                        date_debut_assistance AS date_agri,
                        date_fin_assistance AS date_conseiller
                      FROM assistance_conseiller
                      WHERE id_conseiller = :id_cons 
                      AND id_agriculteur_assiste = :id_agri
                      ORDER BY id ASC"; // Ordonné par ID pour suivre la logique temporelle
        
        $stmt_thread = $pdo->prepare($sql_thread);
        $stmt_thread->execute([
            ':id_cons' => $id_conseiller_connecte,
            ':id_agri' => $id_agriculteur_cible
        ]);
        $messages_du_thread = $stmt_thread->fetchAll(PDO::FETCH_ASSOC);

    } else {
        $message_error = "Agriculteur introuvable.";
        $id_agriculteur_cible = null;
    }
} catch (PDOException $e) {
    error_log("Erreur : " . $e->getMessage());
    $message_error = "Erreur de base de données.";
}

// 3. Gestion de l'envoi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id_agriculteur_cible) {
    $message_a_envoyer = trim($_POST['message_conseiller'] ?? '');

    if (empty($message_a_envoyer)) {
        $message_error = "Veuillez saisir votre message.";
    } else {
        try {
            // On insère une nouvelle ligne (logique de fil de discussion)
            $sql_insert = "INSERT INTO assistance_conseiller (id_conseiller, id_agriculteur_assiste, date_fin_assistance, message_conseiller, date_debut_assistance) 
                           VALUES (:id_conseiller, :id_agriculteur, NOW(), :message, '0000-00-00')";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([
                ':id_conseiller' => $id_conseiller_connecte,
                ':id_agriculteur' => $id_agriculteur_cible,
                ':message' => $message_a_envoyer
            ]);
            
            header("Location: liste_message_cons.php?success=1"); 
            exit();
        } catch (PDOException $e) {
            error_log("Erreur insertion : " . $e->getMessage());
            $message_error = "Le message n'a pas pu être envoyé.";
        }
    }
}
$message_initial_form = htmlspecialchars($_POST['message_conseiller'] ?? '');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Répondre à <?= $nom_agriculteur ?> | MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        :root {
            --color-primary-emerald: #0ab9b1; 
            --color-primary-dark: #008080; 
            --color-secondary-gold: #fd9f07; 
            --color-card-bg: #FFFFFF;
            --color-light-bg: #F8F9FA;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-color: var(--color-light-bg);
            display: flex;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            background: white;
            height: 100vh;
            position: fixed;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }
        .sidebar .logo { font-family: 'Montserrat'; color: var(--color-primary-emerald); font-weight: 800; text-align: center; padding: 30px 10px; text-decoration: none; font-size: 20px; }
        .sidebar ul { list-style: none; padding: 0; flex-grow: 1; }
        .sidebar li a { display: flex; align-items: center; padding: 12px 25px; color: #374151; text-decoration: none; transition: 0.3s; }
        .sidebar li a:hover { background: #e0f8f8; color: var(--color-primary-dark); }
        .sidebar li a i { margin-right: 15px; width: 25px; text-align: center; }
        
        .active-link { background: rgba(6, 189, 189, 0.1); color: var(--color-primary-emerald) !important; border-left: 5px solid var(--color-primary-emerald); }

        .disconnect-item { margin: 20px; background: var(--color-secondary-gold); padding: 12px; border-radius: 8px; text-align: center; }
        .disconnect-item a { color: white; text-decoration: none; font-weight: bold; display: block; }

        /* --- CONTENT --- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 50px;
            width: calc(100% - var(--sidebar-width));
        }

        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }

        h1 { font-family: 'Montserrat'; font-size: 28px; margin-bottom: 30px; color: #111827; }

        /* --- HISTORIQUE (Style Conversation) --- */
        .conversation-history {
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 30px;
            padding: 20px;
            background: #F3F4F6;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .bubble {
            max-width: 80%;
            padding: 12px 16px;
            border-radius: 15px;
            font-size: 14px;
            line-height: 1.4;
            position: relative;
        }

        .bubble-agri {
            align-self: flex-start;
            background: #e5e7eb;
            color: #1f2937;
            border-bottom-left-radius: 2px;
        }

        .bubble-cons {
            align-self: flex-end;
            background: var(--color-primary-emerald);
            color: white;
            border-bottom-right-radius: 2px;
        }

        .bubble-date {
            font-size: 10px;
            display: block;
            margin-top: 5px;
            opacity: 0.7;
        }

        /* --- FORMULAIRE --- */
        textarea {
            width: 100%;
            height: 150px;
            padding: 20px;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            font-family: 'Poppins';
            font-size: 15px;
            background: #F9FAFB;
            transition: 0.3s;
            box-sizing: border-box;
        }

        textarea:focus { outline: none; border-color: var(--color-primary-emerald); background: white; box-shadow: 0 0 0 4px rgba(10, 185, 177, 0.1); }

        .button-group { display: flex; justify-content: flex-end; gap: 15px; margin-top: 25px; }

        .btn { padding: 12px 25px; border-radius: 8px; font-weight: 600; text-decoration: none; cursor: pointer; border: none; font-size: 15px; display: flex; align-items: center; gap: 8px; }
        .btn-cancel { background: #F3F4F6; color: #4B5563; }
        .btn-submit { background: var(--color-primary-emerald); color: white; box-shadow: 0 4px 12px rgba(10, 185, 177, 0.3); }
        .btn-submit:hover { background: var(--color-primary-dark); transform: translateY(-2px); }

        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .alert-error { background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA; }
    </style>
</head>

<body> 
    <nav class="sidebar">
       <a href="index.php" class="logo">
            <i class="fas fa-leaf"></i> MonAgriCoach
        </a>
        <ul>
            <li><a href="advisor_dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a></li>
            <li><a href="outiis_analyse.php"><i class="fas fa-flask"></i> Outils d'analyse</a></li>
            <li><a href="rapport.php"><i class="fas fa-file-invoice"></i> Rapports</a></li>
            <li><a href="liste_message_cons.php" class="active-link"><i class="fas fa-comments"></i> Mes messages</a></li>
            <li><a href="liste_agriculteur_cons.php"><i class="fas fa-user-friends"></i> Agriculteurs</a></li>
        </ul>
        <div class="disconnect-item">
            <a href="deconnexion.php">DÉCONNEXION</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="form-container">
            <h1>Conversation avec <?= $nom_agriculteur ?></h1>
            
            <?php if ($message_error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $message_error ?></div>
            <?php endif; ?>

            <div class="conversation-history">
                <?php if (!empty($messages_du_thread)): ?>
                    <?php foreach ($messages_du_thread as $msg): ?>
                        
                        <?php if (!empty(trim($msg['message_agriculteur']))): ?>
                            <div class="bubble bubble-agri">
                                <strong><?= $nom_agriculteur ?> :</strong><br>
                                <?= nl2br(htmlspecialchars($msg['message_agriculteur'])) ?>
                                <span class="bubble-date"><?= date('d/m/Y H:i', strtotime($msg['date_agri'])) ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty(trim($msg['message_conseiller']))): ?>
                            <div class="bubble bubble-cons">
                                <strong>Vous :</strong><br>
                                <?= nl2br(htmlspecialchars($msg['message_conseiller'])) ?>
                                <span class="bubble-date"><?= date('d/m/Y H:i', strtotime($msg['date_conseiller'])) ?></span>
                            </div>
                        <?php endif; ?>

                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align:center; color:#9ca3af;">Aucun historique de message avec cet agriculteur.</p>
                <?php endif; ?>
            </div>

            <?php if ($id_agriculteur_cible): ?>
                <form action="repondre_message.php?agri_id=<?= $id_agriculteur_cible ?>" method="POST">
                    <label style="font-weight: 600; margin-bottom: 10px; display: block;">Votre réponse :</label>
                    <textarea name="message_conseiller" required placeholder="Tapez votre réponse ici..."><?= $message_initial_form ?></textarea>

                    <div class="button-group">
                        <a href="liste_message_cons.php" class="btn btn-cancel">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-submit">
                            <i class="fas fa-paper-plane"></i> Envoyer le message
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
// Fichier : recommandation.php
session_start();
require_once 'db.php'; 

// --- Sécurité : Vérification de la session ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: connexion.php");
    exit();
}

$message = '';
$current_user_id = $_SESSION['user_id'];

// --- 1. Récupération des données pour les listes déroulantes ---
try {
    // Liste des plantations
    $plantations = $pdo->query("SELECT id, quantite_semis_kg_ha, statut_plantation FROM plantation ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

    // Liste des agriculteurs (uniquement le type 'agriculteur')
    $agriculteurs = $pdo->query("SELECT id, nom, prenom FROM utilisateur WHERE type_utilisateur = 'agriculteur' ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

    // Liste des conseillers (pour le sélecteur, avec l'utilisateur actuel présélectionné)
    $conseillers = $pdo->query("SELECT id, nom, prenom FROM utilisateur WHERE type_utilisateur = 'conseiller' ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erreur de chargement : " . $e->getMessage());
}

// --- 2. Traitement du Formulaire à l'envoi ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_plantation = $_POST["id_plantation"] ?? '';
    $id_utilisateur_recom = $_POST["id_utilisateur_recom"] ?? '';
    $id_conseiller = $_POST["id_conseiller"] ?? $current_user_id;
    $date_recom = $_POST["date_recom"] ?? '';
    $statut_recom = $_POST["statut_recom"] ?? '';
    $contenu_recom = trim($_POST["contenu_recom"] ?? '');

    if (empty($id_plantation) || empty($id_utilisateur_recom) || empty($date_recom) || empty($statut_recom) || empty($contenu_recom)) {
        $message = '<p class="message warning">Veuillez remplir tous les champs obligatoires.</p>';
    } else {
        try {
            $sql = "INSERT INTO recommandation(date_recom, statut_recom, contenu_recom, id_plantation, id_utilisateur_recom, id_conseiller) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date_recom, $statut_recom, $contenu_recom, $id_plantation, $id_utilisateur_recom, $id_conseiller]);
            
            $message = '<p class="message success">✅ Recommandation enregistrée avec succès !</p>';
            $contenu_recom = ""; // On vide le champ après succès
        } catch (PDOException $e) {
            error_log("Erreur insertion : " . $e->getMessage());
            $message = '<p class="message error">Erreur lors de l\'enregistrement technique.</p>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Recommandation - MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        :root {
            --color-primary-emerald: #0ab9b1; 
            --color-primary-dark: #008080; 
            --color-secondary-gold: #fd9f07; 
            --color-card-bg: #FFFFFF;
            --color-light-bg: #F8F9FA;
            --color-text-dark: #1F2937;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-color: var(--color-light-bg);
            display: flex;
        }
        
        /* --- SIDEBAR (Identique à l'autre page) --- */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--color-card-bg);
            height: 100vh;
            position: fixed;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }
        .sidebar .logo { font-family: 'Montserrat'; color: var(--color-primary-emerald); font-size: 20px; font-weight: 800; text-align: center; padding: 30px 10px; text-decoration: none; }
        .sidebar ul { list-style: none; padding: 0; flex-grow: 1; }
        .sidebar li a { display: flex; align-items: center; padding: 12px 25px; color: var(--color-text-dark); text-decoration: none; font-size: 15px; transition: 0.3s; }
        .sidebar li a i { margin-right: 15px; width: 25px; text-align: center; }
        .sidebar li a:hover { background-color: #e0f8f8; color: var(--color-primary-dark); }
        
        .active-link {
            background-color: rgba(6, 189, 189, 0.1);
            color: var(--color-primary-emerald) !important;
            border-left: 5px solid var(--color-primary-emerald);
            font-weight: 600;
        }

        .disconnect-item { margin: 20px; background-color: var(--color-secondary-gold); padding: 12px; border-radius: 8px; text-align: center; }
        .disconnect-item a { color: white; text-decoration: none; font-weight: bold; display: block; }

        /* --- CONTENU PRINCIPAL --- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px;
            width: calc(100% - var(--sidebar-width));
        }

        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        h2 { font-family: 'Montserrat'; color: var(--color-primary-dark); margin-top: 0; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; }

        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: var(--color-text-dark); }
        
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins';
            font-size: 14px;
            box-sizing: border-box;
        }

        textarea { min-height: 120px; resize: vertical; }

        .btn-submit {
            background: var(--color-primary-emerald);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            transition: 0.3s;
            font-size: 16px;
            margin-top: 10px;
        }
        .btn-submit:hover { background: var(--color-primary-dark); transform: translateY(-2px); }

        /* Messages d'alerte */
        .message { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    </style>
</head>

<body> 
    <nav class="sidebar">
       <a href="index.php" class="logo">
                <i class="fas fa-leaf"></i> MonAgriCoach
            </a>
        <ul>
            <li><a href="advisor_dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a></li>
            <li><a href="outiis_analyse_sol.php" class="active-link"><i class="fas fa-flask"></i> Outils d'analyse</a></li>
            <li><a href="rapport_personnalise.php"><i class="fas fa-file-invoice"></i> Rapports</a></li>
            <li><a href="liste_message_cons.php"><i class="fas fa-comments"></i> Mes messages</a></li>
            <li><a href="liste_agriculteur_cons.php"><i class="fas fa-user-friends"></i> Agriculteurs</a></li>
        </ul>
        <div class="disconnect-item">
            <a href="deconnexion.php">DÉCONNEXION</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="form-container">
            <h2><i class="fas fa-clipboard-check"></i> Nouvelle recommandation</h2>
            
            <?= $message ?>

            <form action="recommandation.php" method="POST">
                
                <div class="form-group">
                    <label for="id_plantation"><i class="fas fa-seedling"></i> Plantation concernée</label>
                    <select name="id_plantation" id="id_plantation" required>
                        <option value="">Sélectionner la plantation...</option>
                        <?php foreach($plantations as $p): ?>
                            <option value="<?= $p['id'] ?>">
                                ID: <?= $p['id'] ?> - <?= htmlspecialchars($p['statut_plantation']) ?> (<?= $p['quantite_semis_kg_ha'] ?> kg/ha)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_utilisateur_recom"><i class="fas fa-user"></i> Agriculteur bénéficiaire</label>
                    <select name="id_utilisateur_recom" id="id_utilisateur_recom" required>
                        <option value="">Choisir un exploitant...</option>
                        <?php foreach($agriculteurs as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nom'].' '.$a['prenom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_conseiller"><i class="fas fa-user-tie"></i> Conseiller émetteur</label>
                    <select name="id_conseiller" id="id_conseiller" required>
                        <?php foreach($conseillers as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($current_user_id == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nom'].' '.$c['prenom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="date_recom"><i class="fas fa-calendar-alt"></i> Date de recommandation</label>
                        <input type="date" name="date_recom" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="statut_recom"><i class="fas fa-tasks"></i> Statut actuel</label>
                        <select name="statut_recom" id="statut_recom">
                            <option value="en_attente">En attente</option>
                            <option value="Appliquée">Appliquée</option>
                            <option value="Refusée">Refusée</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="contenu_recom"><i class="fas fa-pen"></i> Contenu de la recommandation</label>
                    <textarea name="contenu_recom" id="contenu_recom" placeholder="Quelles sont les étapes à suivre pour l'agriculteur ?" required><?= htmlspecialchars($contenu_recom ?? '') ?></textarea>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-submit" style="flex: 2;">
                        <i class="fas fa-save"></i> Enregistrer la recommandation
                    </button>
                    <button type="reset" class="btn-submit" style="flex: 1; background: #6c757d;">
                        <i class="fas fa-undo"></i> Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
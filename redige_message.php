<?php
// Fichier : redige_message.php
session_start();
require_once 'db.php'; 

// --- Sécurité ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: connexion.php");
    exit();
}

$message = '';
$current_conseiller_id = $_SESSION['user_id'] ?? null;

// --- 1. Récupération des listes déroulantes ---
try {
    // Liste des agriculteurs
    $agriculteur_stmt = $pdo->query("SELECT id, nom, prenom FROM utilisateur WHERE type_utilisateur = 'agriculteur' ORDER BY nom");
    $agriculteurs = $agriculteur_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Liste des conseillers
    $conseiller_stmt = $pdo->query("SELECT id, nom, prenom FROM utilisateur WHERE type_utilisateur = 'conseiller' ORDER BY nom");
    $conseillers = $conseiller_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message = '<p class="message error">Erreur lors du chargement des données.</p>'; 
    error_log("Erreur BDD : " . $e->getMessage());
}

// --- 2. Traitement du Formulaire ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_conseiller = $_POST["id_conseiller"] ?? '';
    $id_agriculteur_assiste = $_POST["id_agriculteur_assiste"] ?? '';
    $date_debut_assistance = $_POST["date_debut_assistance"] ?? '';
    $date_fin_assistance = $_POST["date_fin_assistance"] ?? '';
    $message_conseiller = $_POST["message_conseiller"] ?? '';
    
    if (empty($id_conseiller) || empty($id_agriculteur_assiste) || empty($date_debut_assistance) || empty($date_fin_assistance) || empty($message_conseiller)) { 
        $message = '<p class="message warning">Veuillez remplir tous les champs obligatoires.</p>'; 
    } elseif ($date_fin_assistance < $date_debut_assistance) {
        $message = '<p class="message error">La date de fin ne peut pas être antérieure à la date de début.</p>';
    } else {
        try {
            $sql = "INSERT INTO assistance_conseiller(date_debut_assistance, date_fin_assistance, message_conseiller, id_conseiller, id_agriculteur_assiste) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date_debut_assistance, $date_fin_assistance, $message_conseiller, $id_conseiller, $id_agriculteur_assiste]); 
            
            $message = '<p class="message success">✅ Conseil enregistré avec succès !</p>';
            // On vide les champs texte après succès
            $message_conseiller = "";
        } catch (PDOException $e) {
            error_log("Erreur insertion : " . $e->getMessage());
            $message = '<p class="message error">Erreur lors de l\'enregistrement.</p>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Conseil - MonAgriCoach</title>
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
        
        /* --- SIDEBAR --- */
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

        /* --- CONTENU --- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px;
            width: calc(100% - var(--sidebar-width));
        }

        .form-container {
            max-width: 700px;
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
        }
        .btn-submit:hover { background: var(--color-primary-dark); transform: translateY(-2px); }

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
            <li><a href="outiis_analyse.php"><i class="fas fa-flask"></i> Outils d'analyse</a></li>
            <li><a href="rapport_personnalise.php"><i class="fas fa-file-invoice"></i> Rapports</a></li>
            <li><a href="liste_message_cons.php" class="active-link"><i class="fas fa-comments"></i> Mes messages</a></li>
            <li><a href="liste_agriculteur_cons.php"><i class="fas fa-user-friends"></i> Agriculteurs</a></li>
            
        </ul>
        <div class="disconnect-item">
            <a href="deconnexion.php">DÉCONNEXION</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="form-container">
            <h2>Rédiger un conseil</h2>
            
            <?= $message ?>

            <form action="redige_message.php" method="POST">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Agriculteur concerné</label>
                    <select name="id_agriculteur_assiste" required>
                        <option value="">Sélectionner l'exploitant...</option>
                        <?php foreach($agriculteurs as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nom'].' '.$a['prenom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-user-tie"></i> Conseiller responsable</label>
                    <select name="id_conseiller" required>
                        <?php foreach($conseillers as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($current_conseiller_id == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nom'].' '.$c['prenom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Début assistance</label>
                        <input type="date" name="date_debut_assistance" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Fin assistance</label>
                        <input type="date" name="date_fin_assistance" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Votre message et recommandations</label>
                    <textarea name="message_conseiller" placeholder="Décrivez vos conseils ici..." required><?= htmlspecialchars($message_conseiller ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Enregistrer le conseil
                </button>
            </form>
        </div>
    </div>
</body>
</html>
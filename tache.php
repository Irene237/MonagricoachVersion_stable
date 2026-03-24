<?php
// Fichier : tache.php
// Démarre la session au tout début du script
session_start();

// Inclut le fichier de connexion à la base de données (assurez-vous que 'db.php' est disponible)
include_once 'db.php';

// Redirection sécurisée
if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) {
    header("Location: connexion.php"); // Redirige vers la page de connexion
    exit();
}

$current_user_id = $_SESSION['user_id'];
$message = null;
$message_type = ''; // 'success' ou 'error'

// Initialisation des variables pour conserver les données en cas d'erreur
$titre_val = $_POST['titre'] ?? '';
$statut_val = $_POST['statut'] ?? 'en_attente';
$priorite_val = $_POST['priorite'] ?? '';
$date_debut_val = $_POST['date_debut'] ?? '';
$date_fin_val = $_POST['date_fin'] ?? '';

// Récupère le nom et prénom de l'utilisateur actuel pour affichage
$user_display_name = 'Utilisateur inconnu';
try {
    $stmt_agri = $pdo->prepare("SELECT nom, prenom FROM utilisateur WHERE id = :user_id");
    $stmt_agri->execute([':user_id' => $current_user_id]);
    $agriculteur = $stmt_agri->fetch(PDO::FETCH_ASSOC);
    if ($agriculteur) {
        $user_display_name = htmlspecialchars($agriculteur['prenom'] . ' ' . $agriculteur['nom']);
    }
} catch (PDOException $e) {
    error_log("DB Error fetching user: " . $e->getMessage());
    $user_display_name = 'Erreur de base de données';
}


// Gère l'envoi du formulaire (méthode POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_agriculteur = (int)($_POST["id_agriculteur"] ?? 0);
    $titre = trim($titre_val);
    $statut = $statut_val;
    $priorite = trim($priorite_val);
    
    // Convertit le format HTML datetime-local (Y-m-dTH:i) en format MySQL (Y-m-d H:i:s)
    $date_debut_mysql = $date_debut_val ? str_replace('T', ' ', $date_debut_val) . ':00' : '';
    $date_fin_mysql = $date_fin_val ? str_replace('T', ' ', $date_fin_val) . ':00' : '';

    // --- Validation Côté Serveur ---
    if ($id_agriculteur != $current_user_id) {
        $message = 'Erreur de sécurité : L\'ID de l\'agriculteur ne correspond pas à l\'utilisateur connecté.';
        $message_type = 'error';
    } elseif (empty($titre) || empty($statut) || empty($priorite) || empty($date_debut_mysql) || empty($date_fin_mysql)) {
        $message = 'Veuillez remplir tous les champs.';
        $message_type = 'error';
    } elseif (strtotime($date_debut_mysql) >= strtotime($date_fin_mysql)) {
         $message = 'La date et heure de fin prévue doit être postérieure à la date et heure de début.';
         $message_type = 'error';
    } else {
        try {
            // Requête préparée pour l'insertion dans la table `tache`
            $stmt = $pdo->prepare("INSERT INTO tache (titre, statut, priorite, date_debut, date_fin, id_agriculteur) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$titre, $statut, $priorite, $date_debut_mysql, $date_fin_mysql, $id_agriculteur]);

            // Redirection après succès
            header("Location: liste_taches.php?status=added");
            exit();
        } catch (PDOException $e) {
            error_log("Erreur d'ajout de tâche: " . $e->getMessage());
            $message = "Une erreur de base de données est survenue : " . htmlspecialchars($e->getMessage());
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Nouvelle Tâche | MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- PALETTE & BASE --- */
        :root {
            --color-primary-emerald: #06bdbdff; 
            --color-primary-dark: #0cb4b4ff; 
            --color-secondary-gold: #FF8C00; 
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

        /* --- SIDEBAR --- */
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
            display: block;
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

        /* Lien Actif : Tâches */
        .sidebar li a[href="liste_taches.php"] {
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-emerald); 
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald); 
        }
        .sidebar li a[href="liste_taches.php"] i {
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
        
        /* --- CONTENU PRINCIPAL & FORMULAIRE --- */
        .main-container {
            margin-left: var(--sidebar-width); 
            padding: 50px 40px; 
            flex-grow: 1;
            width: calc(100% - var(--sidebar-width));
            display: flex;
            flex-direction: column;
            align-items: center; 
        }

        .header-content {
            width: 100%; 
            max-width: 800px; 
            margin-bottom: 30px;
        }

        .header-content h2 {
            font-family: var(--font-heading);
            font-weight: 900; 
            color: var(--color-heading); 
            font-size: 32px; 
            margin: 0 0 20px 0;
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 15px;
            text-align: center;
        }
        
        .header-content a {
            display: block;
            text-align: center;
            color: var(--color-secondary-gold);
            text-decoration: none;
            font-weight: 600;
            margin-top: -10px;
            margin-bottom: 15px;
            transition: color 0.2s;
        }
        .header-content a:hover {
            color: #E37D00;
        }

        .form-card {
            background: var(--color-card-bg);
            padding: 40px;
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); 
            width: 100%;
            max-width: 800px; 
            box-sizing: border-box; 
        }
        
        /* Grille pour le formulaire (deux colonnes) */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr; 
            gap: 10px 30px; 
        }
        
        .full-width {
            grid-column: 1 / span 2; 
        }

        form label {
            display: block;
            font-weight: 600;
            margin-top: 18px; 
            margin-bottom: 8px;
            color: var(--color-text-dark);
            font-size: 15px;
        }

        /* Affichage Nom Agriculteur */
        .agriculteur-display {
            padding: 10px 15px;
            background-color: var(--color-light-bg);
            border: 1px solid #D1D5DB; 
            border-radius: 8px;
            color: var(--color-primary-dark);
            font-weight: 700;
            font-size: 15px;
            display: block; 
            margin-bottom: 10px;
        }
        
        form input:not([type="submit"]):not([type="reset"]), 
        form select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.2s, box-shadow 0.2s;
            background-color: var(--color-card-bg);
            color: var(--color-text-dark);
            -webkit-appearance: none; 
            -moz-appearance: none;
            appearance: none;
        }

        form input:focus, 
        form select:focus {
            border-color: var(--color-primary-emerald);
            box-shadow: 0 0 0 3px rgba(6, 189, 189, 0.2);
            outline: none;
        }
        
        /* Style spécifique pour le select */
        form select {
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%234B5563%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13%205.4L146.2%20202.7%2018.8%2074.8c-2.9-2.9-6.7-4.4-10.6-4.4s-7.7%201.5-10.6%204.4c-5.8%205.8-5.8%2015.2%200%2021l130.6%20130.6c2.9%202.9%206.7%204.4%2010.6%204.4s7.7-1.5%2010.6-4.4l130.6-130.6c5.9-5.7%205.9-15.1.1-20.9z%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat;
            background-position: right 15px top 50%;
            background-size: 12px auto;
            padding-right: 35px; 
        }

        /* --- Boutons --- */
        .button-group {
            grid-column: 1 / span 2; 
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center; 
        }

        .button-group input[type="submit"], .button-group input[type="reset"] {
            flex: 1; 
            max-width: 220px; 
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s, box-shadow 0.2s;
        }

        .button-group input[type="submit"] {
            background-color: var(--color-primary-emerald);
            color: var(--color-card-bg);
            box-shadow: 0 4px 10px rgba(6, 189, 189, 0.4);
        }

        .button-group input[type="submit"]:hover {
            background-color: var(--color-primary-dark);
            box-shadow: 0 6px 15px rgba(6, 189, 189, 0.6);
        }

        .button-group input[type="reset"] {
            background-color: #D1D5DB; 
            color: var(--color-text-dark);
        }

        .button-group input[type="reset"]:hover {
            background-color: #BCC0C5; 
        }
        
        /* Messages (succès/erreur) */
        .alert-message {
            padding: 15px;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
            width: 100%;
            max-width: 800px;
            box-sizing: border-box;
        }

        .alert-message.error {
            color: var(--color-accent-danger); 
            background-color: #FEE2E2; 
            border: 1px solid var(--color-accent-danger);
        }

        /* Image latérale (Conservée de l'original) */
        .left-img {
            display: none; /* Cache l'image pour privilégier le formulaire en pleine largeur dans la grille */
        }
        
        /* Media query pour les petits écrans (tablettes/mobiles) */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr; 
            }
            .full-width {
                grid-column: 1 / span 1;
            }
            .main-container {
                padding: 20px;
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
            <li> <a href="liste_plantation.php"><i class="fas fa-seedling"></i> plantations</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i>  Cultures</a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> Engrais</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i>Messagerie</a></li>
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i>Verser l'engrais</a></li>
            <li> <a href="liste_st_intrant.php"><i class="fas fa-warehouse"></i> stock engrais</a></li>
            <li><a href="liste_re.php"><i class="fas fa-chart-bar"></i> Rendement</a></li>
            <li><a href="liste_taches.php"><i class="fas fa-tasks"></i> **Tâches**</a></li>

            <li class="disconnect-item">
                <button>
                    <a href="deconnexion.php">
                        <i class="fas fa-sign-out-alt"></i> DÉCONNEXION
                    </a>
                </button>
            </li>
        </ul>
    </nav>

    <div class="main-container">
        <div class="header-content">
            <h2><i class="fas fa-tasks"></i> Enregistrer une Nouvelle Tâche</h2>
            <a href="liste_taches.php">Voir la liste des tâches existantes</a>
        </div>

        <?php if (isset($message)): ?>
            <div class="alert-message <?= $message_type; ?>">
                <i class="fas <?= ($message_type == 'error') ? 'fa-times-circle' : 'fa-check-circle'; ?>"></i> 
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                
                <input type="hidden" name="id_agriculteur" value="<?php echo htmlspecialchars($current_user_id); ?>">

                <div class="form-grid">

                    <div class="full-width">
                        <label for="agriculteur_info">Agriculteur :</label>
                        <div id="agriculteur_info" class="agriculteur-display">
                            <?= $user_display_name; ?>
                        </div>
                    </div>
                    
                    <div class="full-width">
                        <label for="titre">Titre de la tâche :</label>
                        <input type="text" name="titre" id="titre" required placeholder="Ex: Désherbage de la parcelle A" value="<?= htmlspecialchars($titre_val) ?>">
                    </div>

                    <div>
                        <label for="statut">Statut :</label>
                        <select name="statut" id="statut" required>
                            <option value="en_attente" <?= ($statut_val == 'en_attente') ? 'selected' : ''; ?>>En attente</option>
                            <option value="en_cour" <?= ($statut_val == 'en_cour') ? 'selected' : ''; ?>>En cours</option>
                            <option value="Termine" <?= ($statut_val == 'Termine') ? 'selected' : ''; ?>>Terminée</option>
                        </select>
                    </div>

                    <div>
                        <label for="priorite">Priorité / Assigné à :</label>
                        <input type="text" name="priorite" id="priorite" required placeholder="Ex: Élevée ou Nom de l'ouvrier (Sophie)" value="<?= htmlspecialchars($priorite_val) ?>">
                    </div>

                    <div>
                        <label for="date_debut">Date et heure de début :</label>
                        <input type="datetime-local" name="date_debut" id="date_debut" required value="<?= htmlspecialchars($date_debut_val) ?>">
                    </div>

                    <div>
                        <label for="date_fin">Date et heure de fin prévue :</label>
                        <input type="datetime-local" name="date_fin" id="date_fin" required value="<?= htmlspecialchars($date_fin_val) ?>">
                    </div>
                
                    <div class="button-group">
                        <input type="reset" value="Réinitialiser">
                        <input type="submit" value="Enregistrer la Tâche"/>
                    </div>
                </div> 
            </form>
        </div>
    </div>
</body>
</html>
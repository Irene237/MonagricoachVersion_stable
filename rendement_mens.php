<?php
// Fichier : rendement_mens.php
// Démarre la session au tout début du script
session_start();

// Inclut le fichier de connexion à la base de données
include_once 'db.php';

// Redirection sécurisée
if (!isset($_SESSION['user_id'])) {
    // Remplacer 'login.php' par 'connexion.php' pour la cohérence
    header("Location: connexion.php"); 
    exit();
}

$current_user_id = $_SESSION['user_id'];
$message = null;
$user_display_name = 'Chargement...'; 

// Récupère le nom et prénom de l'utilisateur actuel pour affichage
try {
    $stmt_user = $pdo->prepare("SELECT nom, prenom FROM utilisateur WHERE id = :user_id");
    $stmt_user->execute([':user_id' => $current_user_id]);
    $user = $stmt_user->fetch();
    if ($user) {
        $user_display_name = htmlspecialchars($user['nom'] . ' ' . $user['prenom']);
    } else {
        $user_display_name = 'Utilisateur inconnu';
    }
} catch (PDOException $e) {
    error_log("DB Error fetching user: " . $e->getMessage());
    $user_display_name = 'Erreur de base de données';
}

// Initialisation des valeurs pour les champs du formulaire (pour les conserver après une erreur)
$annee_val = $_POST['annee'] ?? date('Y');
$date_enregis_val = $_POST['date_enregis'] ?? date('Y-m-d');
$mois_val = $_POST['mois'] ?? '';
$valeur_production_val = $_POST['valeur_production'] ?? '';
$unite_val = $_POST['unite'] ?? '';


// Gère l'envoi du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Assigner les valeurs postées (utilisées pour l'insertion et pour conserver les données si erreur)
    $annee = $annee_val;
    $date_enregis = $date_enregis_val;
    $id_agriculteur = $_POST["id_agriculteur"] ?? null;
    $mois = $mois_val;
    $valeur_production = $valeur_production_val;
    $unite = $unite_val;
    
    // Sécurité et validation
    if ($id_agriculteur != $current_user_id) {
        $message = '<p class="message error-message"><i class="fas fa-exclamation-triangle"></i> Erreur de sécurité : L\'ID de l\'agriculteur ne correspond pas à l\'utilisateur connecté.</p>';
    } 
    elseif (empty($annee) || empty($date_enregis) || empty($id_agriculteur) || empty($mois) || !is_numeric($valeur_production) || $valeur_production <= 0 || empty($unite)) { 
        $message = '<p class="message error-message"><i class="fas fa-times-circle"></i> Veuillez remplir correctement tous les champs. La valeur de production doit être un nombre positif.</p>'; 
    } 
    else {
        try {
            // Requête d'insertion sécurisée avec placeholders nommés
            $sql = "INSERT INTO rendement_mens(mois, valeur_production, unite, annee, date_enregis, id_agriculteur) 
                    VALUES (:mois, :valeur_production, :unite, :annee, :date_enregis, :id_agriculteur)";
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':mois' => $mois,
                ':valeur_production' => $valeur_production,
                ':unite' => $unite,
                ':annee' => $annee,
                ':date_enregis' => $date_enregis,
                ':id_agriculteur' => $id_agriculteur
            ]); 
            
            // Redirection vers le dashboard avec un message de succès
            header("Location: farmer_dashboard.php?success=rendement_added");
            exit();
        } catch (PDOException $e) {
            error_log("Erreur d'insertion de rendement: " . $e->getMessage());
            $message = '<p class="message error-message"><i class="fas fa-database"></i> Une erreur de base de données est survenue : ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un rendement mensuel | MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- PALETTE & BASE --- */
        :root {
            --color-primary-emerald: #06bdbdff; 
            --color-primary-dark: #0cb4b4ff; 
            --color-secondary-gold: #FF8C00; 
            --color-accent-danger: #ef4444; 
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

        /* --- SIDEBAR (Styles précédents) --- */
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

        /* Lien Actif : Rendement */
        .sidebar li a[href="liste_re.php"] {
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-emerald); 
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald); 
        }
        .sidebar li a[href="liste_re.php"] i {
             color: var(--color-primary-emerald); 
        }
        
        /* Bouton Déconnexion */
        .sidebar li.disconnect-item {
            margin-top: auto; 
            padding: 25px; 
        }
        
        .sidebar li.disconnect-item button { 
             border: none; 
             padding: 0; 
             background: none; 
             width: 100%; 
        }
        
        .sidebar li.disconnect-item button a {
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
        .sidebar li.disconnect-item button a:hover { 
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
            max-width: 700px; /* Largeur adaptée au formulaire */
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

        .form-card {
            background: var(--color-card-bg);
            padding: 40px;
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); 
            width: 100%;
            max-width: 700px; 
            box-sizing: border-box; 
        }
        
        /* --- GRILLE : FORMAT COURT 2 COLONNES --- */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr; /* 2 colonnes égales */
            gap: 10px 30px; /* Espace vertical et horizontal */
        }
        
        /* Les champs qui doivent occuper une ligne entière */
        .full-width {
            grid-column: 1 / span 2; 
        }
        /* Fin de la zone de grille */

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
            grid-column: 1 / span 2; /* Les boutons prennent toute la largeur en bas */
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
        .message {
            padding: 15px;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
            width: 100%;
            max-width: 700px;
            box-sizing: border-box;
        }

        .message.error-message {
            color: var(--color-accent-danger); 
            background-color: #FEE2E2; 
            border: 1px solid var(--color-accent-danger);
        }
        
        /* Media query pour les petits écrans (tablettes/mobiles) */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr; /* Revenir à une seule colonne */
            }
            .full-width {
                grid-column: 1 / span 1;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar">
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
            <li> <a href="liste_st_intrant.php"><i class="fas fa-warehouse"></i> **stock engrais**</a></li>
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
    </div>

    <div class="main-container">
        <div class="header-content">
            <h2><i class="fas fa-chart-area"></i> Enregistrer un rendement mensuel</h2>
        </div>

        <?php if (isset($message)) echo $message; ?>

        <div class="form-card">
            <form action="rendement_mens.php" method="POST">
                <input type="hidden" name="id_agriculteur" value="<?php echo htmlspecialchars($current_user_id); ?>">

                <div class="form-grid">

                    <div class="full-width">
                        <label for="agriculteur_display">Agriculteur concerné :</label>
                        <div id="agriculteur_display" class="agriculteur-display">
                            <?= $user_display_name; ?>
                        </div>
                    </div>

                    <div>
                        <label for="annee">Année de production :</label>
                        <input type="number" name="annee" id="annee" placeholder="Ex: <?= date('Y') ?>" value="<?= htmlspecialchars($annee_val) ?>" required min="2000" max="<?= date('Y') + 5 ?>">
                    </div>

                    <div>
                        <label for="mois">Mois de production :</label>
                        <select name="mois" id="mois" required>
                            <option value="">Sélectionner le mois</option>
                            <?php
                            $mois_francais = [
                                'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                                'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
                            ];
                            foreach ($mois_francais as $m) {
                                $selected = ($mois_val == $m) ? 'selected' : '';
                                echo "<option value=\"$m\" $selected>$m</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label for="valeur_production">Valeur de la production :</label>
                        <input type="number" step="0.01" name="valeur_production" id="valeur_production" placeholder="Ex: 50.75" value="<?= htmlspecialchars($valeur_production_val) ?>" required min="0.01">
                    </div>

                    <div>
                        <label for="unite">Unité de mesure :</label>
                        <input type="text" name="unite" id="unite" placeholder="Ex: Tonnes, Sacs, Kg..." value="<?= htmlspecialchars($unite_val) ?>" required>
                    </div>

                    <div class="full-width">
                        <label for="date_enregis">Date d'enregistrement de la donnée :</label>
                        <input type="date" name="date_enregis" id="date_enregis" value="<?= htmlspecialchars($date_enregis_val) ?>" required>
                    </div>

                    <div class="button-group">
                        <input type="submit" value="Enregistrer le rendement"/>
                        <input type="reset" value="Réinitialiser">
                    </div>
                </div> </form>
        </div> </div> </body>
</html>
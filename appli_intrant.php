<?php
// Fichier : appli_intrant.php
// Démarre la session au tout début du script
session_start();

// Inclut le fichier de connexion à la base de données
include_once 'db.php';

// Redirection sécurisée si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) {
    header("Location: connexion.php"); // Redirige vers la page de connexion
    exit();
}

$current_user_id = $_SESSION['user_id'];
$message = null;
$message_type = ''; 

// Initialisation des variables pour conserver les données en cas d'erreur
$id_plantation_val = $_POST['id_plantation'] ?? '';
$id_intrant_val = $_POST['id_intrant'] ?? '';
$date_aplication_val = $_POST['date_aplication'] ?? date('Y-m-d');
$quantite_appliquee_val = $_POST['quantite_appliquee'] ?? '';
$methode_application_val = $_POST['methode_application'] ?? '';
$notes_val = $_POST['notes'] ?? '';

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


// Gère l'envoi du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Assurez-vous que toutes les variables sont définies et nettoyées
    $id_plantation = (int)($id_plantation_val);
    $id_agriculteur = (int)($_POST["id_agriculteur"] ?? 0);
    $id_intrant = (int)($id_intrant_val);
    $date_aplication = $date_aplication_val;
    $quantite_appliquee = trim($quantite_appliquee_val);
    $methode_application = trim($methode_application_val);
    $notes = trim($notes_val);
    
    // --- Validation Côté Serveur ---
    if ($id_agriculteur != $current_user_id) {
        $message = 'Erreur de sécurité : L\'ID de l\'agriculteur ne correspond pas à l\'utilisateur connecté.';
        $message_type = 'error';
    } elseif (empty($id_plantation) || empty($id_intrant) || empty($date_aplication) || empty($quantite_appliquee) || empty($methode_application)) { 
        $message = 'Veuillez remplir tous les champs requis (seules les Notes sont facultatives).'; 
        $message_type = 'error';
    } elseif (!is_numeric($quantite_appliquee) || (float)$quantite_appliquee <= 0) {
        $message = 'La quantité appliquée doit être un nombre positif.'; 
        $message_type = 'error';
    } else {
        try {
            // 1. Vérification de sécurité (Plantation et Intrant appartiennent à l'utilisateur)
            $stmt_check = $pdo->prepare("SELECT 1 FROM plantation WHERE id = ? AND id_agriculteur = ?");
            $stmt_check->execute([$id_plantation, $current_user_id]);
            $is_plantation_valid = $stmt_check->fetch();

            $stmt_check = $pdo->prepare("SELECT 1 FROM intrant WHERE id = ? AND id_agriculteur = ?");
            $stmt_check->execute([$id_intrant, $current_user_id]);
            $is_intrant_valid = $stmt_check->fetch();

            if (!$is_plantation_valid || !$is_intrant_valid) {
                 $message = 'Erreur de sécurité : Plantation ou intrant non trouvé(e) ou n\'appartient pas à cet agriculteur.';
                 $message_type = 'error';
            } else {
                // Début de la Transaction
                $pdo->beginTransaction(); 
                
                // 2. Insertion dans la table application_intrant
                $sql_insert_appli = "
                    INSERT INTO application_intrant(id_intrant, date_aplication, quantite_appliquee, methode_application, notes, id_plantation, id_agriculteur) 
                    VALUES (:id_intrant, :date_aplication, :quantite_appliquee, :methode_application, :notes, :id_plantation, :id_agriculteur)
                ";
                
                $stmt = $pdo->prepare($sql_insert_appli);
                $stmt->execute([
                    ':id_intrant' => $id_intrant, 
                    ':date_aplication' => $date_aplication, 
                    ':quantite_appliquee' => (float)$quantite_appliquee, 
                    ':methode_application' => $methode_application, 
                    ':notes' => $notes, 
                    ':id_plantation' => $id_plantation, 
                    ':id_agriculteur' => $id_agriculteur
                ]); 
                
                // 3. --- DÉCRÉMENTATION DU STOCK ---
                $id_intrant_applique = $id_intrant;
                $quantite_utilisee = (float)$quantite_appliquee;

                // 3.1 Décrémenter la quantité actuelle et mettre à jour la date
                $sql_update_stock = "
                    UPDATE stock_intrant 
                    SET 
                        quantite_actuelle = quantite_actuelle - :quantite_utilisee, 
                        date_derniere_mise_a_jour = NOW()
                    WHERE id_intrant = :id_intrant_applique
                ";
                $stmt_update = $pdo->prepare($sql_update_stock);
                $stmt_update->bindParam(':quantite_utilisee', $quantite_utilisee); 
                $stmt_update->bindParam(':id_intrant_applique', $id_intrant_applique, PDO::PARAM_INT);
                $stmt_update->execute();

                // 3.2 Vérifier si le stock est passé sous le seuil d'alerte
                $sql_check_alert = "
                    SELECT 
                        s.quantite_actuelle, s.seuil_alerte, i.nom_intrant 
                    FROM stock_intrant AS s
                    JOIN intrant AS i ON s.id_intrant = i.id
                    WHERE s.id_intrant = :id_intrant_applique
                ";
                $stmt_check = $pdo->prepare($sql_check_alert);
                $stmt_check->bindParam(':id_intrant_applique', $id_intrant_applique, PDO::PARAM_INT);
                $stmt_check->execute();
                $stock_data = $stmt_check->fetch(PDO::FETCH_ASSOC);

                // 3.3 Définition du message de session
                if ($stock_data && $stock_data['quantite_actuelle'] <= $stock_data['seuil_alerte']) {
                    $_SESSION['flash_message'] = [
                        'type' => 'warning',
                        'message' => "ATTENTION : Le stock de l'intrant **" . htmlspecialchars($stock_data['nom_intrant']) . "** est maintenant **bas** ({$stock_data['quantite_actuelle']} restant) et a atteint son seuil d'alerte ({$stock_data['seuil_alerte']}).",
                        'is_low_stock' => true
                    ];
                } else {
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'message' => "Application d'intrant enregistrée avec succès. Le stock a été mis à jour.",
                        'is_low_stock' => false
                    ];
                }
                
                // Fin de la Transaction
                $pdo->commit();

                // Succès : Redirection
                header("Location: liste_appli_intrant.php");
                exit();
            }

        } catch (PDOException $e) {
            // Annuler toutes les opérations en cas d'erreur
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Erreur de transaction application intrant: " . $e->getMessage());
            $_SESSION['flash_message'] = [
                 'type' => 'danger',
                 'message' => "Erreur lors de l'enregistrement de l'application : " . htmlspecialchars($e->getMessage()),
                 'is_low_stock' => false
            ];
            header("Location: liste_appli_intrant.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enregistrer Application d'Intrant | MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- DÉBUT DU BLOC CSS --- */
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

        /* --- DEBOGAGE (Stylé mais retiré dans ce code) --- */
        /* Note : Le CSS pour .debug-panel n'est plus utilisé car le HTML correspondant a été retiré. */

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

        /* Lien Actif : Application Intrants */
        .sidebar li a[href="liste_appli_intrant.php"] {
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-emerald); 
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald); 
        }
        .sidebar li a[href="liste_appli_intrant.php"] i {
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
        }
        
        form input:not([type="submit"]):not([type="reset"]):not([type="hidden"]), 
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
        
        /* Ajustement pour le champ Notes qui doit être grand */
        form textarea {
            width: 100%;
            min-height: 100px;
            padding: 12px 15px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.2s, box-shadow 0.2s;
            background-color: var(--color-card-bg);
            color: var(--color-text-dark);
            resize: vertical;
        }


        form input:focus, 
        form select:focus,
        form textarea:focus {
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
        /* --- FIN DU BLOC CSS --- */
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
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i> **Verser l'engrais**</a></li>
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

    <div class="main-container">
        <div class="header-content">
            <h2><i class="fas fa-cogs"></i> Gestion de la Fertilisation</h2>
        </div>

        <?php 
        // Gestion des messages d'erreur de formulaire (avant redirection)
        if (isset($message)): 
        ?>
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
                        <label for="agriculteur_display">Agriculteur :</label>
                        <div id="agriculteur_display" class="agriculteur-display">
                            <?= $user_display_name; ?>
                        </div>
                    </div>
                    
                    <div class="full-width">
                        <label for="id_plantation">Plantation concernée :</label>
                        <select name="id_plantation" id="id_plantation" required>
                            <option value="">Sélectionner une plantation</option>
                            <?php
                            // REQUÊTE CORRIGÉE (et nettoyée du statut de débogage)
                            $sql_plantations = "SELECT id, id_parcelle FROM plantation WHERE id_agriculteur = :user_id ORDER BY id DESC";

                            try {
                                $stmt_plantations = $pdo->prepare($sql_plantations);
                                $stmt_plantations->execute([':user_id' => $current_user_id]);
                                
                                if ($stmt_plantations->rowCount() === 0) {
                                    echo "<option value='' disabled>--- Aucune plantation trouvée pour cet ID d'agriculteur ({$current_user_id}) ---</option>";
                                }

                                while ($p = $stmt_plantations->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = ($id_plantation_val == $p['id']) ? 'selected' : '';
                                    // Affichage sans le statut
                                    echo "<option value='{$p['id']}' {$selected}>Plantation N°{$p['id']} (Parcelle: {$p['id_parcelle']})</option>";
                                }
                            } catch (PDOException $e) {
                                error_log("DB Error fetching plantations: " . $e->getMessage());
                                echo "<option value='' disabled>Erreur de base de données. Contactez l'administrateur.</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="full-width">
                        <label for="id_intrant">engrais appliqué :</label>
                        <select name="id_intrant" id="id_intrant" required>
                            <option value="">Sélectionner un engrais</option>
                            <?php
                            try {
                                // Récupère uniquement les intrants définis par l'agriculteur connecté
                                $stmt_intrants = $pdo->prepare("SELECT id, nom_intrant, type_intrant FROM intrant WHERE id_agriculteur = ? ORDER BY nom_intrant ASC");
                                $stmt_intrants->execute([$current_user_id]);
                                
                                if ($stmt_intrants->rowCount() === 0) {
                                    echo "<option value='' disabled>--- Aucun intrant défini n'a été trouvé ---</option>";
                                }
                                
                                while ($i = $stmt_intrants->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = ($id_intrant_val == $i['id']) ? 'selected' : '';
                                    echo "<option value='{$i['id']}' {$selected}>{$i['nom_intrant']} ({$i['type_intrant']})</option>";
                                }
                            } catch (PDOException $e) {
                                error_log("DB Error fetching intrants: " . $e->getMessage());
                                echo "<option value='' disabled>Erreur de chargement des intrants: " . htmlspecialchars($e->getMessage()) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="date_aplication">Date de l'application :</label>
                        <input type="date" name="date_aplication" id="date_aplication" value="<?= htmlspecialchars($date_aplication_val) ?>" required>
                    </div>

                    <div>
                        <label for="methode_application">Méthode d'application :</label>
                        <input type="text" name="methode_application" id="methode_application" placeholder="Ex: Pulvérisation foliaire" value="<?= htmlspecialchars($methode_application_val) ?>" required>
                    </div>
                    
                    <div class="full-width">
    <label for="quantite_appliquee">Quantité utilisée :</label>
    <div style="display: flex; gap: 10px;">
        <input type="number" step="0.01" name="quantite_appliquee" id="quantite_appliquee" placeholder="Ex: 10" required>
        <select name="unite_appliquee" style="width: 100px;">
            <option value="kg">kg</option>
            <option value="L">Litre(s)</option>
            <option value="sacs">Sac(s)</option>
            <option value="unites">Unité(s)</option>
        </select>
    </div>
</div>

                    <div class="full-width">
                        <label for="notes">Notes/Observations :</label>
                        <textarea name="notes" id="notes" placeholder="Détails sur l'état de la culture, la météo, ou l'équipement utilisé."><?= htmlspecialchars($notes_val) ?></textarea>
                    </div>
                
                    <div class="button-group">
                        <input type="reset" value="Réinitialiser">
                        <input type="submit" value="Enregistrer l'application"/>
                    </div>
                </div> 
            </form>
        </div> 
    </div> 

    </body>
</html>
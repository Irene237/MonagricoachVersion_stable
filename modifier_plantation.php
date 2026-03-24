<?php
// Démarre la session au tout début du script (Décommentez si vous l'utilisez dans votre environnement)
// session_start(); 

require_once 'db.php'; // Assurez-vous que ce fichier initialise $pdo

// --- (Vérification de Connexion - Recommandé) ---
/*
if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) { 
    header("Location: connexion.php"); 
    exit(); 
} 
$userId = $_SESSION['user_id'];
*/

// Récupération et validation de l'ID depuis l'URL
$id = (isset($_GET["id"]) && is_numeric($_GET["id"])) ? (int) $_GET["id"] : null; 
$plantation = null;
$message = null;

if ($id === null) {
    $message = "ID de plantation manquant ou invalide.";
} else {
    try {
        // En production, vous DEVRIEZ ajouter une jointure pour vérifier la propriété 
        // via id_parcelle et id_agriculteur_proprietaire pour la sécurité.
        $sql = "SELECT id, date_semis, date_recolte_prevue, quantite_semis_kg_ha, statut_plantation, rendement_final_kg, unite_rendement, id_parcelle, id_culture 
                FROM plantation 
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $plantation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$plantation) {
            $message = "Plantation non trouvée.";
        } else {
            // Message d'information pour la confirmation du chargement
            $message = "Données de la plantation chargées avec succès. Modifiez les champs ci-dessous.";
        }
        
    } catch (PDOException $e) {
        $message = "Erreur de base de données : " . $e->getMessage();
        // Loggez l'erreur pour le débogage, mais n'affichez pas les détails bruts à l'utilisateur final
        error_log("Erreur PDO: " . $e->getMessage()); 
    }
}
?>

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Modifier Plantation | MonAgriCoach</title> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- PALETTE OPTIMISÉE --- */
        :root {
            --color-primary-emerald: #06bdbdff; /* Vert Émeraude Vif */
            --color-primary-dark: #0cb4b4ff; 
            
            --color-secondary-gold: #FF8C00; /* Orange Vif (Déconnexion) */
            --color-secondary-gold-hover: #E37D00;

            --color-accent-danger: #ef4444; 
            
            --color-heading: #111827; 
            --color-card-bg: #FFFFFF; 
            --color-light-bg: #F5F8F5; 
            --color-text-dark: #374151; 
            --color-text-medium: #4B5563; 
            
            --color-disconnect-bg: var(--color-secondary-gold); 

            --font-main: 'Poppins', sans-serif;
            --font-heading: 'Montserrat', sans-serif;
            --sidebar-width: 260px; 
            --content-max-width: 700px; /* Réduit encore la largeur max pour un meilleur centrage visuel */
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
            font-family: 'Font Awesome 6 Free', 'Poppins', sans-serif; 
            font-weight: 900; 
        }
        
        .sidebar li a:hover {
            background-color: #f5f8f8ff;
            color: var(--color-primary-dark);
        }

        /* Lien Actif : Plantation */
        .sidebar li a[href="liste_plantation.php"] {
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-emerald); 
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald); 
        }
        .sidebar li a[href="liste_plantation.php"] i {
             color: var(--color-primary-emerald); 
        }
        
        /* Bouton Déconnexion */
        .disconnect-item-top {
            padding: 0 25px; 
            margin-top: 20px; 
        }
        
        .disconnect-item-top a {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            background-color: var(--color-disconnect-bg); 
            color: var(--color-card-bg) !important; 
            padding: 12px 20px;
            border-radius: 8px; 
            font-size: 15px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(255, 140, 0, 0.4); 
            transition: all 0.3s; 
            text-decoration: none;
            border-left: none; 
        }
        
        /* Correction du Survol : Reste orange/foncé et blanc */
        .disconnect-item-top a:hover { 
              background-color: var(--color-secondary-gold-hover) !important; 
              box-shadow: 0 6px 20px rgba(255, 140, 0, 0.6);
              color: var(--color-card-bg) !important; 
        } 
        
        .disconnect-item-top a i {
             color: inherit; 
             margin-right: 0; 
             font-size: 18px; 
        }

        /* --- CONTENU PRINCIPAL ET FORMULAIRE (CENTRAGE AMÉLIORÉ) --- */
        .main-content-wrapper {
             /* Nécessaire pour contenir le main-content qui se décale */
             display: flex;
             width: 100%;
        }
        
        .main-content {
            margin-left: var(--sidebar-width); 
            padding: 50px 100px; 
            flex-grow: 1;
            display: block; 
            width: calc(100% - var(--sidebar-width)); 
            box-sizing: border-box; /* Assure que le padding est inclus dans la largeur */
        }
        
        .header {
            max-width: var(--content-max-width); 
            width: 100%;
            display: flex;
            justify-content: center; 
            align-items: center;
            /* Centrage horizontal garanti */
            margin: 0 auto 40px auto; 
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
            text-align: center; 
        }

        .form-container {
            max-width: var(--content-max-width); 
            width: 100%;
            background: var(--color-card-bg);
            padding: 40px;
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            /* Centrage horizontal garanti (fonctionne si max-width est inférieur à la largeur parente) */
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 50px; 
        }
        
        form {
            display: grid;
            grid-template-columns: repeat(2, 1fr); 
            gap: 20px;
        }
        
        form div {
            display: flex;
            flex-direction: column;
        }
        
        form label {
            font-weight: 600;
            color: var(--color-heading);
            margin-bottom: 8px;
            font-size: 15px;
        }
        
        form input:not([type="submit"]),
        form select {
            padding: 12px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        form input:focus,
        form select:focus {
            border-color: var(--color-primary-emerald);
            box-shadow: 0 0 0 3px rgba(6, 189, 189, 0.2);
            outline: none;
        }
        
        /* Champs d'ID en lecture seule */
        .form-container input[type="text"][id$="_display"] {
            background-color: #F8F9FA; 
            color: var(--color-text-medium);
        }

        /* Bouton de soumission */
        .submit-button-wrapper {
             grid-column: 1 / -1; 
             margin-top: 20px;
        }

        form input[type="submit"] {
            width: 100%;
            padding: 14px;
            background-color: var(--color-primary-emerald); 
            color: var(--color-card-bg); 
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s ease, box-shadow 0.2s;
            box-shadow: 0 4px 12px rgba(6, 189, 189, 0.4); 
        }

        form input[type="submit"]:hover { 
            background-color: var(--color-primary-dark); 
            box-shadow: 0 6px 15px rgba(6, 189, 189, 0.6);
        }
        
        /* Message d'erreur/info */
        .message-box {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-message {
            color: var(--color-accent-danger); 
            background-color: #FEE2E2; 
            border: 1px solid var(--color-accent-danger);
        }
        
        .info-message {
            color: var(--color-primary-dark); 
            background-color: #E0F7F7; 
            border: 1px solid var(--color-primary-emerald);
        }
        
        /* --- CORRECTION MAJEURE : RESPONSIVE DESIGN (Centrage sur Mobile) --- */
        @media (max-width: 1024px) {
            /* 1. Cache la sidebar (ou la fait glisser hors écran) */
            .sidebar {
                left: -260px; /* Cache la sidebar */
                box-shadow: none;
                z-index: 999; 
            }

            /* 2. Le contenu principal prend toute la largeur et supprime le décalage */
            .main-content {
                margin-left: 0; /* Suppression du décalage de la sidebar */
                padding: 30px 20px; /* Réduction du padding pour les mobiles */
                width: 100%; 
            }
            
            /* 3. Le formulaire passe en une seule colonne */
            form {
                grid-template-columns: 1fr; /* Une seule colonne */
                gap: 15px;
            }
            
            /* Les conteneurs d'ID passent aussi en une seule colonne */
            div[style*="grid-column: span 2"] { 
                 grid-template-columns: 1fr;
                 gap: 15px !important; 
            }
            
            .header h1 {
                font-size: 28px;
            }
        }
    </style>
</head>

<body> 
    <div class="main-content-wrapper"> 
        <nav class="sidebar">
            <a href="index.php" class="logo">
                <i class="fas fa-leaf"></i> MonAgriCoach
            </a>
            
            <ul>
                <li><a href="farmer_dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a></li>
            <li> <a href="liste_parcelle.php"><i class="fas fa-map-marker-alt"></i> Parcelles</a></li> 
            <li> <a href="liste_plantation.php"><i class="fas fa-seedling"></i> **Plantations**</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i>  Cultures </a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> Engrais</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i> Messagerie</a></li>
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i>Verser l'engrais</a></li>
            <li> <a href="liste_st_intrant.php"><i class="fas fa-warehouse"></i> Stock engrais</a></li>
            <li><a href="liste_re.php"><i class="fas fa-chart-bar"></i> Rendement</a></li>
            <li><a href="liste_taches.php"><i class="fas fa-tasks"></i> Tâches</a></li>

                <li class="disconnect-item-top"> 
                    <a href="deconnexion.php">
                        <i class="fas fa-sign-out-alt"></i> DÉCONNEXION
                    </a>
                </li>
            </ul>
        </nav>

        <div class="main-content">
            <div class="header">
                <h1>Modification d'une Plantation</h1>
            </div>
            
            <div class="form-container">
                <?php if ($message): ?>
                    <div class="message-box <?= $plantation ? 'info-message' : 'error-message'; ?>">
                        <i class="fas <?= $plantation ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i> <?= htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($plantation): ?>
                    <form action="update_plantation.php" method="POST">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($plantation['id']); ?>">
                        
                        <div>
                            <label for="date_semis">Date de Semis</label>
                            <input type="date" id="date_semis" name="date_semis" value="<?= htmlspecialchars($plantation['date_semis']); ?>" required>
                        </div>

                        <div>
                            <label for="date_recolte_prevue">Date Récolte Prévue</label>
                            <input type="date" id="date_recolte_prevue" name="date_recolte_prevue" value="<?= htmlspecialchars($plantation['date_recolte_prevue']); ?>" required>
                        </div>
                        
                        <div>
                            <label for="quantite_semis_kg_ha">Quantité Semis (kg/ha)</label>
                            <input type="number" step="0.01" id="quantite_semis_kg_ha" name="quantite_semis_kg_ha" value="<?= htmlspecialchars($plantation['quantite_semis_kg_ha']); ?>" placeholder="Ex: 150.00" required>
                        </div>

                        <div>
                            <label for="statut_plantation">Statut</label>
                            <select id="statut_plantation" name="statut_plantation" required>
                                <option value="Semée" <?= $plantation['statut_plantation'] == 'Semée' ? 'selected' : ''; ?>>Semée</option>
                                <option value="En cours" <?= $plantation['statut_plantation'] == 'En cours' ? 'selected' : ''; ?>>En cours</option>
                                <option value="Récoltée" <?= $plantation['statut_plantation'] == 'Récoltée' ? 'selected' : ''; ?>>Récoltée</option>
                                <option value="Abandonnée" <?= $plantation['statut_plantation'] == 'Abandonnée' ? 'selected' : ''; ?>>Abandonnée</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="rendement_final_kg">Rendement Final (kg)</label>
                            <input type="number" step="0.01" id="rendement_final_kg" name="rendement_final_kg" value="<?= htmlspecialchars($plantation['rendement_final_kg'] ?? ''); ?>" placeholder="Rendement total (laissez vide si en cours)">
                        </div>

                        <div>
                            <label for="unite_rendement">Unité de Rendement</label>
                            <select id="unite_rendement" name="unite_rendement" required>
                                <option value="Kg" <?= $plantation['unite_rendement'] == 'Kg' ? 'selected' : ''; ?>>Kilogrammes (Kg)</option>
                                <option value="T" <?= $plantation['unite_rendement'] == 'T' ? 'selected' : ''; ?>>Tonnes (T)</option>
                                <option value="Qx" <?= $plantation['unite_rendement'] == 'Qx' ? 'selected' : ''; ?>>Quintaux (Qx)</option>
                            </select>
                        </div>
                        
                        <div style="grid-column: span 2; display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                            <div>
                                <label for="id_parcelle_display">ID Parcelle (Non Modifiable)</label>
                                <input type="text" id="id_parcelle_display" value="<?= htmlspecialchars($plantation['id_parcelle']); ?>" readonly>
                                <input type="hidden" name="id_parcelle" value="<?= htmlspecialchars($plantation['id_parcelle']); ?>">
                            </div>
                            <div>
                                <label for="id_culture_display">ID Culture (Non Modifiable)</label>
                                <input type="text" id="id_culture_display" value="<?= htmlspecialchars($plantation['id_culture']); ?>" readonly>
                                <input type="hidden" name="id_culture" value="<?= htmlspecialchars($plantation['id_culture']); ?>">
                            </div>
                        </div>

                        <div class="submit-button-wrapper">
                            <input type="submit" value="Enregistrer les Modifications">
                        </div>
                    </form>
                <?php else: ?>
                    <div class="message-box error-message">
                        <i class="fas fa-exclamation-triangle"></i> Impossible de charger les données de la plantation.
                    </div>
                    <p style="text-align: center; margin-top: 20px;">
                        <a href="liste_plantation.php" style="color: var(--color-primary-emerald); font-weight: 600;">Retour à la liste des plantations</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
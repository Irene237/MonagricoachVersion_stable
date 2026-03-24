<?php
// Inclure la connexion à la base de données
require_once 'db.php';

// Initialisation des variables
$id = $_GET["id"] ?? 0; 
$id = (int) $id; 
$apintrant = null;
$message = null;

// --- 1. FONCTIONS DE RÉCUPÉRATION DE DONNÉES DE BASE ---
try {
    // Récupérer la liste des plantations (pour la liste déroulante)
    $stmt_plantations = $pdo->query("SELECT id, nom_plantation FROM plantation ORDER BY nom_plantation");
    $plantations = $stmt_plantations->fetchAll(PDO::FETCH_KEY_PAIR); // [id => nom]

    // Récupérer la liste des intrants (pour la liste déroulante)
    $stmt_intrants = $pdo->query("SELECT id, nom_intrant FROM intrant ORDER BY nom_intrant");
    $intrants = $stmt_intrants->fetchAll(PDO::FETCH_KEY_PAIR); // [id => nom]
} catch (PDOException $e) {
    error_log("Erreur PDO lors du chargement des listes : " . $e->getMessage());
    $message = "Erreur lors du chargement des listes de plantations ou d'intrants.";
    $plantations = [];
    $intrants = [];
}

// --- 2. CHARGEMENT DES DONNÉES DE L'APPLICATION D'INTRANT ---
if ($id > 0) {
    try {
        $sql = "SELECT id, date_aplication, quantite_appliquee, unite_appliquee, methode_application, notes, id_plantation, id_agriculteur, id_intrant 
                FROM application_intrant 
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $apintrant = $stmt->fetch(PDO::FETCH_ASSOC); 
        
        if (!$apintrant) {
            $message = "Application d'intrant non trouvée."; 
        } else {
            $message = "Données de l'application chargées avec succès. Modifiez les champs ci-dessous.";
        }
    } catch (PDOException $e) {
        error_log("Erreur PDO lors de la récupération de l'application : " . $e->getMessage());
        $message = "Une erreur est survenue lors du chargement des données. Veuillez réessayer.";
    }
} else {
    $message = "ID d'application d'intrant invalide ou manquant.";
}
?>

<!DOCTYPE html>
<html lang="fr"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Application Intrant | MonAgriCoach</title> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- STYLE GLOBAL ET VARIABLES (Identique aux autres pages) --- */
        :root {
            --color-primary-emerald: #06bdbdff; 
            --color-primary-dark: #0cb4b4ff; 
            
            --color-secondary-gold: #FF8C00; 
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
            --content-max-width: 800px; /* Légèrement plus large pour les clés étrangères */
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

        /* --- CONTENU PRINCIPAL ET FORMULAIRE --- */
        .main-content-wrapper {
             display: flex;
             width: 100%;
        }
        
        .main-content {
            margin-left: var(--sidebar-width); 
            padding: 50px 100px; 
            flex-grow: 1;
            display: block; 
            width: calc(100% - var(--sidebar-width)); 
            box-sizing: border-box;
        }
        
        .header {
            max-width: var(--content-max-width); 
            width: 100%;
            display: flex;
            justify-content: center; 
            align-items: center;
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
        form select,
        form textarea {
            padding: 12px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        form input:focus,
        form select:focus,
        form textarea:focus {
            border-color: var(--color-primary-emerald);
            box-shadow: 0 0 0 3px rgba(6, 189, 189, 0.2);
            outline: none;
        }
        
        /* Champ Informations Générales sur toute la largeur */
        .full-width {
            grid-column: 1 / -1;
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
        
        /* Style des champs en lecture seule */
        .readonly-field {
            background-color: #F8F9FA !important; 
            color: var(--color-text-medium) !important;
            cursor: not-allowed;
        }
        
        /* --- RESPONSIVE DESIGN --- */
        @media (max-width: 1024px) {
            .sidebar {
                left: -260px;
                box-shadow: none;
            }

            .main-content {
                margin-left: 0;
                padding: 30px 20px;
                width: 100%; 
            }
            
            form {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .full-width {
                grid-column: 1 / 1;
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
            <li> <a href="liste_plantation.php"><i class="fas fa-seedling"></i> Plantations</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i> Cultures</a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> Engrais</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i> Messagerie</a></li>
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i> **Verser l'engrais**</a></li>
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
                <h1>Modifier une Application d'Intrant</h1>
            </div>
            
            <div class="form-container">
                <?php if ($message): ?>
                    <div class="message-box <?= $apintrant ? 'info-message' : 'error-message'; ?>">
                        <i class="fas <?= $apintrant ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i> <?= htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($apintrant): ?>
                    <form action="update_ap_intrant.php" method="POST">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($apintrant['id']); ?>">
                        <input type="hidden" name="id_agriculteur" value="<?= htmlspecialchars($apintrant['id_agriculteur']); ?>">

                        <div>
                            <label for="id_plantation"><i class="fas fa-seedling"></i> Plantation Cible</label>
                            <select id="id_plantation" name="id_plantation" required>
                                <option value="">-- Choisir une Plantation --</option>
                                <?php 
                                    $current_plantation_id = htmlspecialchars($apintrant['id_plantation']);
                                    foreach ($plantations as $id_p => $nom_p) {
                                        $selected = ($id_p == $current_plantation_id) ? 'selected' : '';
                                        echo "<option value=\"$id_p\" $selected>" . htmlspecialchars($nom_p) . "</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="id_intrant"><i class="fas fa-flask"></i> Intrant Appliqué</label>
                            <select id="id_intrant" name="id_intrant" required>
                                <option value="">-- Choisir un Intrant --</option>
                                <?php 
                                    $current_intrant_id = htmlspecialchars($apintrant['id_intrant']);
                                    foreach ($intrants as $id_i => $nom_i) {
                                        $selected = ($id_i == $current_intrant_id) ? 'selected' : '';
                                        echo "<option value=\"$id_i\" $selected>" . htmlspecialchars($nom_i) . "</option>";
                                    }
                                ?>
                            </select>
                        </div>

                        <div>
                            <label for="date_aplication"><i class="fas fa-calendar-alt"></i> Date d'Application</label>
                            <input type="date" id="date_aplication" name="date_aplication" value="<?= htmlspecialchars($apintrant['date_aplication']); ?>" required>
                        </div>

                        <div>
                            <label for="methode_application"><i class="fas fa-wrench"></i> Méthode d'Application</label>
                            <select id="methode_application" name="methode_application" required>
                                <?php 
                                    $methodes = ['Foliaire', 'Sol', 'Irrigation', 'Manuelle', 'Mécanique'];
                                    $current_methode = htmlspecialchars($apintrant['methode_application']);
                                    foreach ($methodes as $m) {
                                        $selected = ($m == $current_methode) ? 'selected' : '';
                                        echo "<option value=\"$m\" $selected>$m</option>";
                                    }
                                    // Si la méthode actuelle n'est pas dans la liste, l'ajouter
                                     if (!in_array($current_methode, $methodes) && $current_methode) {
                                        echo "<option value=\"$current_methode\" selected>$current_methode</option>";
                                    }
                                ?>
                            </select>
                        </div>

                        <div>
                            <label for="quantite_appliquee"><i class="fas fa-balance-scale-right"></i> Quantité Appliquée</label>
                            <input type="number" step="0.01" id="quantite_appliquee" name="quantite_appliquee" value="<?= htmlspecialchars($apintrant['quantite_appliquee']); ?>" placeholder="Ex: 50.5" required>
                        </div>
                        
                        <div>
                            <label for="unite_appliquee"><i class="fas fa-box-open"></i> Unité Appliquée</label>
                            <input type="text" id="unite_appliquee" name="unite_appliquee" value="<?= htmlspecialchars($apintrant['unite_appliquee']); ?>" placeholder="Ex: Kg/Ha, Litres, etc." required>
                        </div>

                        <div class="full-width">
                            <label for="notes"><i class="fas fa-clipboard"></i> Notes / Observations</label>
                            <textarea id="notes" name="notes" rows="3" placeholder="Conditions météo, difficultés rencontrées, zone précise..."><?= htmlspecialchars($apintrant['notes']); ?></textarea>
                        </div>
                        
                        <div class="submit-button-wrapper">
                            <input type="submit" value="Mettre à jour l'Application">
                        </div>
                    </form>
                <?php else: ?>
                    <p style="text-align: center; margin-top: 20px;">
                        <a href="liste_appli_intrant.php" style="color: var(--color-primary-emerald); font-weight: 600;">Retour à la liste des applications</a>
                    </p>
                <?php endif; ?>
            </div>
            
            
        </div>
    </div>
</body>
</html>
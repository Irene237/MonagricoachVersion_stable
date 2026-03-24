<?php
// Inclure la connexion à la base de données
require_once 'db.php';

// Initialisation des variables
$id = $_GET["id"] ?? 0; 
$id = (int) $id; 
$stintrant = null;
$message = null;

// --- 1. FONCTIONS DE RÉCUPÉRATION DE DONNÉES DE BASE ---
try {
    // Récupérer la liste des intrants (pour la liste déroulante)
    $stmt_intrants = $pdo->query("SELECT id, nom_intrant, unite_standard FROM intrant ORDER BY nom_intrant");
    // Utiliser une fonction pour formater le nom de l'intrant avec son unité
    $intrants = [];
    while ($row = $stmt_intrants->fetch(PDO::FETCH_ASSOC)) {
        $intrants[$row['id']] = $row['nom_intrant'] . " (" . $row['unite_standard'] . ")";
    }
} catch (PDOException $e) {
    error_log("Erreur PDO lors du chargement des listes : " . $e->getMessage());
    $message = "Erreur lors du chargement de la liste des intrants.";
    $intrants = [];
}

// --- 2. CHARGEMENT DES DONNÉES DU STOCK D'INTRANT ---
if ($id > 0) {
    try {
        $sql = "SELECT id, quantite_actuelle, seuil_alerte, date_derniere_mise_a_jour, id_agriculteur, id_intrant 
                FROM stock_intrant 
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $stintrant = $stmt->fetch(PDO::FETCH_ASSOC); 
        
        if (!$stintrant) {
            $message = "Stock d'intrant non trouvé."; 
        } else {
            $message = "Données du stock chargées avec succès. Modifiez les champs ci-dessous.";
        }
    } catch (PDOException $e) {
        error_log("Erreur PDO lors de la récupération du stock : " . $e->getMessage());
        $message = "Une erreur est survenue lors du chargement des données. Veuillez réessayer.";
    }
} else {
    $message = "ID de stock d'intrant invalide ou manquant.";
}
?>

<!DOCTYPE html>
<html lang="fr"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Stock Intrant | MonAgriCoach</title> 
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
            --content-max-width: 700px;
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
        /* Lien Actif : Stock Intrants */
        .sidebar li a[href="liste_st_intrant.php"] { 
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-emerald); 
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald); 
        }
        .sidebar li a[href="liste_st_intrant.php"] i {
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
        
        /* Champ sur toute la largeur (si nécessaire) */
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
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i>  Cultures </a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> Engrais</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i> Messagerie</a></li>
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i>Verser l'engrais</a></li>
            <li> <a href="liste_st_intrant.php"><i class="fas fa-warehouse"></i> **Stock engrais**</a></li>
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
                <h1>Modifier le Stock d'Intrant</h1>
            </div>
            
            <div class="form-container">
                <?php if ($message): ?>
                    <div class="message-box <?= $stintrant ? 'info-message' : 'error-message'; ?>">
                        <i class="fas <?= $stintrant ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i> <?= htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($stintrant): ?>
                    <form action="update_st_intrant.php" method="POST">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($stintrant['id']); ?>">
                        <input type="hidden" name="id_agriculteur" value="<?= htmlspecialchars($stintrant['id_agriculteur']); ?>">

                        <div class="full-width">
                            <label for="id_intrant"><i class="fas fa-flask"></i> Intrant Stocké (Nom et Unité)</label>
                            <select id="id_intrant" name="id_intrant" required>
                                <option value="">-- Choisir un Intrant --</option>
                                <?php 
                                    $current_intrant_id = htmlspecialchars($stintrant['id_intrant']);
                                    foreach ($intrants as $id_i => $nom_unite_i) {
                                        $selected = ($id_i == $current_intrant_id) ? 'selected' : '';
                                        echo "<option value=\"$id_i\" $selected>" . htmlspecialchars($nom_unite_i) . "</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="quantite_actuelle"><i class="fas fa-box-open"></i> Quantité Actuelle en Stock</label>
                            <input type="number" step="0.01" id="quantite_actuelle" name="quantite_actuelle" value="<?= htmlspecialchars($stintrant['quantite_actuelle']); ?>" placeholder="Ex: 50.00" required>
                        </div>

                        <div>
                            <label for="seuil_alerte"><i class="fas fa-bell"></i> Seuil d'Alerte (Quantité Min.)</label>
                            <input type="number" step="0.01" id="seuil_alerte" name="seuil_alerte" value="<?= htmlspecialchars($stintrant['seuil_alerte']); ?>" placeholder="Ex: 5.00" required>
                        </div>

                        <div class="full-width">
                            <label for="date_derniere_mise_a_jour"><i class="fas fa-calendar-alt"></i> Date de Dernière Mise à Jour</label>
                            <input type="date" id="date_derniere_mise_a_jour" name="date_derniere_mise_a_jour" value="<?= htmlspecialchars($stintrant['date_derniere_mise_a_jour']); ?>" required>
                        </div>
                        
                        <div class="submit-button-wrapper">
                            <input type="submit" value="Mettre à jour le Stock">
                        </div>
                    </form>
                <?php else: ?>
                    <p style="text-align: center; margin-top: 20px;">
                        <a href="liste_st_intrant.php" style="color: var(--color-primary-emerald); font-weight: 600;">Retour à la liste des stocks d'intrants</a>
                    </p>
                <?php endif; ?>
            </div>
            
            
        </div>
    </div>
</body>
</html>
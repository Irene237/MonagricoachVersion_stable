<?php
// Démarre la session au tout début du script
session_start();
require_once 'db.php';

// --- Vérification de Connexion ---
if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) { 
    header("Location: connexion.php"); 
    exit(); 
} 

$parcelle = null;
$message = null;
$userId = $_SESSION['user_id'];

// 1. Vérifier si l'ID de la parcelle est passé dans l'URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $message = "ID de parcelle manquant ou invalide.";
} else {
    $id_parcelle = (int) $_GET['id'];
    
    try {
        // 2. Récupération de la parcelle. Vérifie aussi que la parcelle appartient bien à l'utilisateur connecté.
        $sql = "SELECT id, nom_parcelle, superficie_ha, localisation_gps, typ_sol, date_creation, statut, id_agriculteur_proprietaire 
                FROM parcelle 
                WHERE id = :id_parcelle AND id_agriculteur_proprietaire = :userId";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_parcelle', $id_parcelle, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $parcelle = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$parcelle) {
            $message = "Parcelle non trouvée ou vous n'avez pas l'autorisation de la modifier.";
        }
        
    } catch (PDOException $e) {
        $message = "Erreur de base de données : " . $e->getMessage();
        error_log("Erreur PDO: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Modifier Parcelle | MonAgriCoach</title> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- PALETTE OPTIMISÉE (Identique aux listes) --- */
        :root {
            --color-primary-emerald: #06bdbdff; /* Vert Émeraude Vif */
            --color-primary-dark: #0cb4b4ff; 
            
            --color-secondary-gold: #FF8C00; /* Orange Vif (Déconnexion) */
            --color-secondary-gold-hover: #E37D00;

            --color-accent-danger: #ef4444; /* Rouge pour les erreurs/danger */
            
            --color-heading: #111827; 
            --color-card-bg: #FFFFFF; 
            --color-light-bg: #F5F8F5; 
            --color-text-dark: #374151; 
            --color-text-medium: #4B5563; 
            
            --color-disconnect-bg: var(--color-secondary-gold); 

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
        }
        
        .sidebar li a:hover {
            background-color: #f5f8f8ff;
            color: var(--color-primary-dark);
        }

        /* Lien Actif : Parcelle */
        .sidebar li a[href="liste_parcelle.php"] {
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-emerald); 
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald); 
        }
        .sidebar li a[href="liste_parcelle.php"] i {
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
        
        .disconnect-item a {
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
        .disconnect-item a:hover { 
              background-color: var(--color-secondary-gold-hover);
              box-shadow: 0 6px 20px rgba(255, 140, 0, 0.6);
        } 

        /* --- CONTENU PRINCIPAL ET FORMULAIRE --- */
        .main-content {
            margin-left: var(--sidebar-width); 
            padding: 50px 100px; 
            flex-grow: 1;
            /* Flexbox pour centrer verticalement/horizontalement le contenu si nécessaire, mais on utilise margin: auto pour le form-container */
           
            flex-direction: column;
            align-items: center; 
        }
        
        .header {
            /* Centrage du titre */
            width: 850px; /* Aligné avec la largeur du formulaire */
            display: flex;
            justify-content: center; 
            align-items: center;
            margin-bottom: 40px; 
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
            max-width: 950px; /* Augmenté à 850px */
            width: 190%;
            background: var(--color-card-bg);
            padding: 40px;
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin: 0; /* Supprimer l'ancienne marge */
        }
        
        form {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* Deux colonnes */
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
        
        /* Bouton de soumission */
        .submit-button-wrapper {
             grid-column: 1 / -1; /* Prend toute la largeur */
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
            <li> <a href="liste_parcelle.php"><i class="fas fa-map-marker-alt"></i> **Parcelles**</a></li> 
            <li> <a href="liste_plantation.php"><i class="fas fa-seedling"></i> Plantations</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i>  Cultures </a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> Engrais</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i> Messagerie</a></li>
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i>Verser l'engrais</a></li>
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

        <div class="main-content">
            <div class="header">
                <h1>Modification d'une Parcelle</h1>
            </div>
            
            <div class="form-container">
                <?php if ($message): ?>
                    <div class="message-box <?= $parcelle ? 'info-message' : 'error-message'; ?>">
                        <i class="fas fa-info-circle"></i> <?= htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($parcelle): ?>
                    <form action="update_parcelle.php" method="POST">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($parcelle['id']); ?>">
                        <input type="hidden" name="id_agriculteur_proprietaire" value="<?= htmlspecialchars($parcelle['id_agriculteur_proprietaire']); ?>">
                        
                        <div>
                            <label for="nom_parcelle">Nom de la Parcelle</label>
                            <input type="text" id="nom_parcelle" name="nom_parcelle" value="<?= htmlspecialchars($parcelle['nom_parcelle']); ?>" placeholder="Ex: Champ Ouest" required>
                        </div>

                        <div>
                            <label for="superficie_ha">Superficie (ha)</label>
                            <input type="number" step="0.01" id="superficie_ha" name="superficie_ha" value="<?= htmlspecialchars($parcelle['superficie_ha']); ?>" placeholder="Ex: 5.50" required>
                        </div>
                        
                        <div>
                            <label for="localisation_gps">Localisation GPS (Lat, Long)</label>
                            <input type="text" id="localisation_gps" name="localisation_gps" value="<?= htmlspecialchars($parcelle['localisation_gps']); ?>" placeholder="Ex: 48.8584, 2.2945" required>
                        </div>

                        <div>
                            <label for="typ_sol">Type de Sol</label>
                            <select id="typ_sol" name="typ_sol" required>
                                <option value="Argileux" <?= $parcelle['typ_sol'] == 'Argileux' ? 'selected' : ''; ?>>Argileux</option>
                                <option value="Sableux" <?= $parcelle['typ_sol'] == 'Sableux' ? 'selected' : ''; ?>>Sableux</option>
                                <option value="Limoneux" <?= $parcelle['typ_sol'] == 'Limoneux' ? 'selected' : ''; ?>>Limoneux</option>
                                <option value="Calcaire" <?= $parcelle['typ_sol'] == 'Calcaire' ? 'selected' : ''; ?>>Calcaire</option>
                                <option value="Humifère" <?= $parcelle['typ_sol'] == 'Humifère' ? 'selected' : ''; ?>>Humifère</option>
                                <option value="Autre" <?= $parcelle['typ_sol'] == 'Autre' ? 'selected' : ''; ?>>Autre</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="date_creation">Date de Création</label>
                            <input type="date" id="date_creation" name="date_creation" value="<?= htmlspecialchars($parcelle['date_creation']); ?>" required>
                        </div>

                        <div>
                            <label for="statut">Statut</label>
                            <select id="statut" name="statut" required>
                                <option value="Actif" <?= $parcelle['statut'] == 'Actif' ? 'selected' : ''; ?>>Actif</option>
                                <option value="Inactif" <?= $parcelle['statut'] == 'Inactif' ? 'selected' : ''; ?>>Inactif</option>
                                <option value="En prépa" <?= $parcelle['statut'] == 'En prépa' ? 'selected' : ''; ?>>En préparation</option>
                            </select>
                        </div>

                        <div class="submit-button-wrapper">
                            <input type="submit" value="Enregistrer les Modifications">
                        </div>
                    </form>
                <?php else: ?>
                    <div class="message-box error-message">
                        <i class="fas fa-exclamation-triangle"></i> Impossible de charger les données de la parcelle.
                    </div>
                    <p style="text-align: center; margin-top: 20px;">
                        <a href="liste_parcelle.php" style="color: var(--color-primary-emerald); font-weight: 600;">Retour à la liste des parcelles</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
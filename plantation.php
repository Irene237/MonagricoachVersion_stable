<?php
// Démarre la session au tout début
session_start();

// Inclut le fichier de connexion à la base de données
include_once 'db.php';

// Vérifie si l'utilisateur est connecté, sinon le redirige
if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) {
    header("Location: connexion.php"); 
    exit();
}

$current_user_id = $_SESSION['user_id'];
$message = '';
$message_type = ''; // Utilisé pour le style (success/error)

// --- 1. Gère l'envoi du formulaire ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && 
    isset($_POST['id_agriculteur']) && 
    isset($_POST['id_parcelle']) && 
    isset($_POST['id_culture']) && 
    isset($_POST['date_semis']) && 
    isset($_POST['date_recolte_prevue']) && 
    isset($_POST['quantite_semis_kg_ha']) && 
    isset($_POST['statut_plantation']) && 
    isset($_POST['rendement_final_kg']) && 
    isset($_POST['unite_rendement'])) {
    
    // Assainissement et typage des données
    $id_agriculteur = (int)($_POST["id_agriculteur"] ?? 0);
    $id_parcelle = (int)($_POST["id_parcelle"] ?? 0);
    $id_culture = (int)($_POST["id_culture"] ?? 0);
    $date_semis = trim($_POST["date_semis"]);
    $date_recolte_prevue = trim($_POST["date_recolte_prevue"]);
    $quantite_semis_kg_ha = (float)($_POST["quantite_semis_kg_ha"] ?? 0.0);
    $statut_plantation = trim($_POST["statut_plantation"]);
    $rendement_final_kg = (float)($_POST["rendement_final_kg"] ?? 0.0); // Rendement prévu (estimation)
    $unite_rendement = trim($_POST["unite_rendement"]);
    
    // Validation de sécurité et des champs
    if ($id_agriculteur != $current_user_id) {
        $message = 'Erreur de sécurité : L\'ID de l\'agriculteur ne correspond pas à l\'utilisateur connecté.';
        $message_type = 'error';
    } elseif (empty($id_parcelle) || empty($id_culture) || empty($date_semis) || empty($date_recolte_prevue) || empty($quantite_semis_kg_ha) || empty($statut_plantation) || empty($unite_rendement)) { 
        $message = 'Veuillez remplir tous les champs obligatoires.'; 
        $message_type = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO plantation(id_agriculteur, date_semis, date_recolte_prevue, quantite_semis_kg_ha, statut_plantation, rendement_final_kg, unite_rendement, id_parcelle, id_culture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$id_agriculteur, $date_semis, $date_recolte_prevue, $quantite_semis_kg_ha, $statut_plantation, $rendement_final_kg, $unite_rendement, $id_parcelle, $id_culture])) {
                // Redirection vers la liste des plantations après un enregistrement réussi
                header("Location: liste_plantation.php?success=new_plantation");
                exit();
            } else {
                $message = "Erreur lors de l'enregistrement de la plantation.";
                $message_type = 'error';
            }
        } catch (PDOException $e) {
            error_log("Erreur d'insertion : " . $e->getMessage());
            $message = "Une erreur est survenue lors de l'enregistrement : " . $e->getMessage();
            $message_type = 'error';
        }
    }
}


// --- 2. Récupération des données pour les listes déroulantes ---

// a. Agriculteur pour l'affichage
$agriculteur_nom_complet = '';
try {
    $stmt_agriculteur = $pdo->prepare("SELECT nom, prenom FROM utilisateur WHERE id = ?");
    $stmt_agriculteur->execute([$current_user_id]);
    $agriculteur = $stmt_agriculteur->fetch(PDO::FETCH_ASSOC);
    if ($agriculteur) {
        $agriculteur_nom_complet = htmlspecialchars($agriculteur['prenom'] . ' ' . $agriculteur['nom']);
    }
} catch (PDOException $e) {
    error_log("Erreur de récupération de l'agriculteur: " . $e->getMessage());
}

// b. Parcelles de l'agriculteur
$parcelles = [];
try {
    $stmt_parcelles = $pdo->prepare("SELECT id, nom_parcelle, superficie_ha FROM parcelle WHERE id_agriculteur_proprietaire = ?");
    $stmt_parcelles->execute([$current_user_id]);
    $parcelles = $stmt_parcelles->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur de récupération des parcelles: " . $e->getMessage());
}

// c. Cultures de l'agriculteur
$cultures = [];
try {
    $stmt_cultures = $pdo->prepare("SELECT id, nom_commun, cycle_de_vie_jours FROM culture WHERE id_agriculteur = ?");
    $stmt_cultures->execute([$current_user_id]);
    $cultures = $stmt_cultures->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur de récupération des cultures: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planifier une Plantation | MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* ----------------------------------------------------- */
        /* --- STYLES GLOBALS & PALETTE --- */
        /* ----------------------------------------------------- */
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

        /* ----------------------------------------------------- */
        /* --- BARRE LATÉRALE (SIDEBAR) --- */
        /* ----------------------------------------------------- */
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

        /* Lien Actif: Plantation */
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

        /* ----------------------------------------------------- */
        /* --- CONTENU PRINCIPAL & FORMULAIRE --- */
        /* ----------------------------------------------------- */
        .main-content {
            padding: 50px 40px; 
            flex-grow: 1;
            max-width: 1300px; 
            width: 100%;
            margin-right: auto;
            margin-left: var(--sidebar-width); 
        }
        
        .form-container {
            max-width: 850px; 
            margin: 0 auto; 
            background-color: var(--color-card-bg);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .header-content h2 {
            font-family: var(--font-heading);
            font-weight: 900; 
            color: var(--color-heading); 
            font-size: 30px; 
            margin-top: 0;
            margin-bottom: 25px;
            text-align: center;
        }

        /* Messages d'alerte */
        .alert-message {
            padding: 15px; 
            border-radius: 8px; 
            font-weight: 600; 
            margin-bottom: 25px; 
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-message.error {
            color: var(--color-accent-danger); 
            background-color: #FEE2E2; 
            border: 1px solid var(--color-accent-danger);
        }
        
        form {
            width: 100%; 
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        /* Groupement des champs sur la même ligne (pour les dates et les quantités) */
        .form-row {
            display: flex;
            gap: 20px;
        }

        .form-group {
            flex: 1;
            display: flex;
            flex-direction: column;
        }


        label {
            font-weight: 600;
            color: var(--color-text-dark);
            margin-top: 5px;
        }

        input[type="text"], input[type="date"], input[type="number"], select {
            width: 100%;
            padding: 12px;
            border: 1px solid #D1D5DB; 
            border-radius: 6px;
            font-size: 15px;
            color: var(--color-text-dark);
            box-sizing: border-box; 
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: var(--color-primary-emerald);
            box-shadow: 0 0 0 3px rgba(6, 189, 189, 0.2);
        }
        
        /* Affichage de l'agriculteur (non-input) */
        #agriculteur_display {
            padding: 10px 12px;
            border: 1px solid #E5E7EB;
            border-radius: 6px;
            background-color: #F9FAFB;
            color: var(--color-text-medium);
            font-style: italic;
            font-size: 15px;
            margin-bottom: 10px;
        }

        .button {
            display: flex;
            justify-content: flex-end; 
            gap: 15px;
            margin-top: 20px;
        }

        .button input[type="submit"], .button input[type="reset"] {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 120px;
        }

        .button input[type="submit"] {
            background-color: var(--color-primary-emerald);
            color: var(--color-card-bg);
            box-shadow: 0 4px 10px rgba(6, 189, 189, 0.3);
        }

        .button input[type="submit"]:hover {
            background-color: var(--color-primary-dark);
            box-shadow: 0 6px 12px rgba(6, 189, 189, 0.4);
        }

        .button input[type="reset"] {
            background-color: #E5E7EB;
            color: var(--color-text-dark);
        }

        .button input[type="reset"]:hover {
            background-color: #D1D5DB;
        }
        
        /* Style spécifique pour les dropdowns */
        select {
            appearance: none; /* Cache la flèche par défaut */
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2306bdbdff%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13.6-6.4H19.5c-5.8%200-11.1%203.6-13.4%209.7-2.3%206.1-1.3%2013.3%202.4%2018.6l128%20127.9c3.8%203.8%209%205.7%2014.2%205.7s10.4-1.9%2014.2-5.7l128-127.9c3.7-5.3%204.7-12.5%202.4-18.6z%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 10px;
            padding-right: 30px; /* Espace pour la flèche personnalisée */
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
            <li> <a href="liste_plantation.php"><i class="fas fa-seedling"></i> **Plantations**</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i>  Cultures </a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> Engrais</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i> Messagerie</a></li>
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i>Verser l'engrais</a></li>
            <li> <a href="liste_st_intrant.php"><i class="fas fa-warehouse"></i> stock engrais</a></li>
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
        <div class="form-container">
            <div class="header-content">
                <h2>Planifier une Nouvelle Plantation </h2>
                <?php if (!empty($message)): ?>
                    <p class="alert-message <?= $message_type; ?>">
                        <?php if ($message_type == 'error'): ?>
                            <i class="fas fa-exclamation-triangle"></i>
                        <?php else: ?>
                            <i class="fas fa-check-circle"></i>
                        <?php endif; ?>
                        <?= htmlspecialchars($message); ?>
                    </p>
                <?php endif; ?>
            </div>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                
                <input type="hidden" name="id_agriculteur" value="<?= htmlspecialchars($current_user_id); ?>">
                
                <div class="form-group">
                    <label for="agriculteur_display">Agriculteur :</label>
                    <div id="agriculteur_display">
                        <?= $agriculteur_nom_complet; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="id_parcelle">Parcelle concernée :</label>
                        <select name="id_parcelle" id="id_parcelle" required>
                            <option value="">-- Sélectionner une parcelle --</option>
                            <?php foreach ($parcelles as $p): ?>
                                <option value="<?= htmlspecialchars($p['id']); ?>">
                                    <?= htmlspecialchars($p['nom_parcelle']); ?> (<?= htmlspecialchars($p['superficie_ha']); ?> ha)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($parcelles)): ?>
                            <small style="color: var(--color-accent-danger); margin-top: 5px;">*Aucune parcelle trouvée. Veuillez en ajouter une d'abord.</small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="id_culture">Culture concernée :</label>
                        <select name="id_culture" id="id_culture" required>
                            <option value="">-- Sélectionner une culture --</option>
                            <?php foreach ($cultures as $c): ?>
                                <option value="<?= htmlspecialchars($c['id']); ?>">
                                    <?= htmlspecialchars($c['nom_commun']); ?> (Cycle: <?= htmlspecialchars($c['cycle_de_vie_jours']); ?> j)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($cultures)): ?>
                            <small style="color: var(--color-accent-danger); margin-top: 5px;">*Aucune culture trouvée. Veuillez en ajouter une d'abord.</small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="date_semis">Date de semence :</label>
                        <input type="date" id="date_semis" name="date_semis" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_recolte_prevue">Date prévue de la récolte :</label>
                        <input type="date" id="date_recolte_prevue" name="date_recolte_prevue" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="quantite_semis_kg_ha">Quantité semée (kg/ha) :</label>
                        <input type="number" id="quantite_semis_kg_ha" name="quantite_semis_kg_ha" required step="0.01" min="0" placeholder="Ex: 150"/>
                    </div>
                    
                    <div class="form-group">
                        <label for="statut_plantation">Statut de la plantation :</label>
                        <select name="statut_plantation" id="statut_plantation" required>
                            <option value="Planifiée">Planifiée (Prévision)</option>
                            <option value="En cours">En cours (Semée)</option>
                            <option value="Récoltée">Récoltée (Terminée)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="rendement_final_kg">Rendement total prévu (kg) :</label>
                        <input type="number" id="rendement_final_kg" name="rendement_final_kg" required step="0.01" min="0" placeholder="Ex: 5000 (Estimation basée sur la superficie)"/>
                    </div>
                    
                    <div class="form-group">
                        <label for="unite_rendement">Unité du rendement :</label>
                        <input type="text" id="unite_rendement" name="unite_rendement" required placeholder="Ex: kg, tonnes, sacs"/>
                    </div>
                </div>
                
                <div class="button">
                    <input type="reset" value="Réinitialiser">
                    <input type="submit" value="Enregistrer"/>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
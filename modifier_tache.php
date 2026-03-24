<?php
// Démarre la session et vérifie la connexion si nécessaire, comme dans votre autre code
session_start();
// Vérifiez si l'utilisateur est connecté et autorisé si besoin.

require_once 'db.php'; // Fichier de connexion PDO

$id_tache = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$tache = null;
$message = null;

// Fonction utilitaire pour convertir le format DATETIME MySQL (Y-m-d H:i:s) au format HTML (Y-m-dTH:i)
function toDatetimeLocal($mysql_datetime) {
    if (!$mysql_datetime || $mysql_datetime === '0000-00-00 00:00:00') {
        return ''; // Retourne vide si la date est nulle ou non valide
    }
    return date('Y-m-d\TH:i', strtotime($mysql_datetime));
}

// Options pour le statut et la priorité
$statut_options = [
    'en_attente' => 'En Attente', 
    'en_cour' => 'En Cours', 
    'termine' => 'Terminée', 
    'annule' => 'Annulée'
];

$priorite_options = [
    'basse' => 'Basse',
    'normale' => 'Normale',
    'haute' => 'Haute',
    'urgente' => 'Urgente'
];


// --- CHARGEMENT DES DONNÉES DE LA TÂCHE ---
if ($id_tache === 0) {
    $message = "ID de tâche manquant ou invalide.";
} else {
    try {
        // Requête pour récupérer les données de la tâche
        $sql = "SELECT id, titre, statut, priorite, date_debut, date_fin, id_agriculteur FROM tache WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id_tache, PDO::PARAM_INT);
        $stmt->execute();
        
        $tache = $stmt->fetch(PDO::FETCH_ASSOC); // Utilisation de FETCH_ASSOC pour la clarté

        if (!$tache) {
            $message = "Tâche non trouvée.";
        } else {
            $message = "Tâche chargée. Modifiez les informations ci-dessous.";
        }
    } catch (PDOException $e) {
        error_log("Erreur PDO lors de la récupération de la tâche: " . $e->getMessage());
        $message = "Une erreur est survenue lors du chargement de la tâche.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Tâche | MonAgriCoach</title> 
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
        .btn_lien {
            padding: 0 25px; 
            margin-top: 20px; 
        }
        .btn_lien button a {
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
            border: none;
            cursor: pointer;
        }
        .btn_lien button {
            width: 100%;
            border: none;
            padding: 0;
            background: none;
        }
        .btn_lien button a:hover { 
              background-color: var(--color-secondary-gold-hover) !important;
              box-shadow: 0 6px 20px rgba(255, 140, 0, 0.6);
              color: var(--color-card-bg) !important; 
        } 
        .btn_lien button a i {
             color: inherit; 
             margin-right: 0; 
             font-size: 18px; 
        }

        /* --- CONTENU PRINCIPAL ET FORMULAIRE --- */
        .main-content-wrapper {
             display: flex;
             width: 100%;
        }
        
        .form-container {
            margin-left: var(--sidebar-width); 
            padding: 50px 100px; 
            flex-grow: 1;
            display: block; 
            width: calc(100% - var(--sidebar-width)); 
            box-sizing: border-box;
        }
        
        .header-content {
            max-width: var(--content-max-width); 
            width: 100%;
            margin: 0 auto 40px auto; 
            padding-bottom: 20px;
            border-bottom: 1px solid #E5E7EB;
            text-align: center;
        }

        .header-content h2 {
            font-family: var(--font-heading);
            font-weight: 900; 
            color: var(--color-heading); 
            font-size: 35px; 
            margin: 0 0 10px 0;
        }
        
        /* Conteneur principal du formulaire */
        .content-box {
            max-width: var(--content-max-width); 
            width: 100%;
            background: var(--color-card-bg);
            padding: 40px;
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-left: auto;
            margin-right: auto;
            display: flex; /* Utilisation de flex pour l'image à gauche/droite */
            gap: 30px;
        }

        .left-img {
            display: none; /* Cache l'image par défaut */
        }
        
        form {
            flex-grow: 1;
            display: grid;
            grid-template-columns: repeat(2, 1fr); 
            gap: 20px;
        }
        
        form > * {
            display: flex;
            flex-direction: column;
        }
        
        form label {
            font-weight: 600;
            color: var(--color-heading);
            margin-bottom: 8px;
            font-size: 15px;
        }
        
        form input:not([type="submit"]):not([type="hidden"]),
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
            max-width: var(--content-max-width); 
            margin: 0 auto 20px auto; 
            padding: 15px;
            border-radius: 8px;
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

        /* --- RESPONSIVE DESIGN --- */
        @media (max-width: 1024px) {
            .sidebar {
                left: -260px;
                box-shadow: none;
            }

            .form-container {
                margin-left: 0;
                padding: 30px 20px;
                width: 100%; 
            }
            
            .content-box {
                padding: 20px;
            }
            
            form {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .header-content h2 {
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
            <li> <a href="liste_st_intrant.php"><i class="fas fa-warehouse"></i> stock engrais</a></li>
            <li><a href="liste_re.php"><i class="fas fa-chart-bar"></i> Rendement</a></li>
            <li><a href="liste_taches.php"><i class="fas fa-tasks"></i> **Tâches**</a></li>
      
                
                <div class="btn_lien">
                    <button>
                        <a href="deconnexion.php">
                            <i class="fas fa-sign-out-alt"></i> DÉCONNEXION
                        </a>
                    </button>
                </div>
            </ul>
        </nav>
    
        <div class="form-container">
            <div class="header-content">
                <h2>Modifier une Tâche</h2>
            </div>
            
            <?php if($message): ?>
                <div class="message-box <?= $tache ? 'info-message' : 'error-message'; ?>">
                    <i class="fas <?= $tache ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i> <?= htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if($tache): ?>
                <div class="content-box">
                    <form action="update_tache.php" method="POST">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($tache['id']); ?>">
                        <input type="hidden" name="id_agriculteur" value="<?= htmlspecialchars($tache['id_agriculteur']); ?>">
                        
                        <div class="full-width">
                            <label for="titre"><i class="fas fa-tag"></i> Titre de la Tâche</label>
                            <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($tache['titre']); ?>" placeholder="Ex: Traitement fongicide Plantation N°5" required>
                        </div>

                        <div>
                            <label for="statut"><i class="fas fa-list-check"></i> Statut de la Tâche</label>
                            <select id="statut" name="statut" required>
                                <?php 
                                $current_statut = strtolower(htmlspecialchars($tache['statut']));
                                foreach ($statut_options as $key => $label): 
                                    $selected = ($key == $current_statut) ? 'selected' : '';
                                ?>
                                    <option value="<?= $key; ?>" <?= $selected; ?>><?= $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="priorite"><i class="fas fa-exclamation-circle"></i> Priorité</label>
                            <select id="priorite" name="priorite" required>
                                <?php 
                                $current_priorite = strtolower(htmlspecialchars($tache['priorite']));
                                foreach ($priorite_options as $key => $label): 
                                    $selected = ($key == $current_priorite) ? 'selected' : '';
                                ?>
                                    <option value="<?= $key; ?>" <?= $selected; ?>><?= $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="date_debut"><i class="fas fa-clock"></i> Date et Heure de Début</label>
                            <input type="datetime-local" id="date_debut" name="date_debut" 
                                value="<?= toDatetimeLocal($tache['date_debut']); ?>" required>
                        </div>

                        <div>
                            <label for="date_fin"><i class="fas fa-calendar-times"></i> Date et Heure de Fin Prévue</label>
                            <input type="datetime-local" id="date_fin" name="date_fin" 
                                value="<?= toDatetimeLocal($tache['date_fin']); ?>" required>
                        </div>
                        
                        <div class="submit-button-wrapper">
                            <input type="submit" value="Mettre à jour la Tâche">
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            
        </div>
    </div>
</body>
</html>
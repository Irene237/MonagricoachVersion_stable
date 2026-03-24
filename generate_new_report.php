<?php
// Fichier : generate_new_report.php
session_start();
// Assurez-vous que db.php est présent pour la connexion
require_once 'db.php'; 

// --- Sécurité ---
if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    header("Location: connexion.php");
    exit();
}

$message = '';
$agriculteurs_liste = [];
$types_rapports = [
    'Rendement', 
    'Fertilisation', 
    'Diagnostic Parasitaire', 
    'Bilan Annuel'
];

// --- 1. RÉCUPÉRATION DES DONNÉES (Agriculteurs) ---
try {
    // Récupération des agriculteurs pour la liste déroulante
    $sql_agriculteurs = "SELECT id, nom, prenom FROM utilisateur 
                         WHERE type_utilisateur = 'agriculteur' 
                         ORDER BY nom, prenom";
    $stmt_agriculteurs = $pdo->query($sql_agriculteurs);
    $agriculteurs_liste = $stmt_agriculteurs->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erreur BDD: Impossible de récupérer les agriculteurs : " . $e->getMessage());
    $message = "Attention : Erreur de BDD. Chargement des données simulées.";
    
    // Données simulées en cas d'échec de BDD
    if (empty($agriculteurs_liste)) {
        $agriculteurs_liste = [
            ['id' => 999, 'nom' => 'Simulé', 'prenom' => 'Agriculteur 1'],
            ['id' => 998, 'nom' => 'Simulé', 'prenom' => 'Agriculteur 2']
        ];
    }
}

// --- 2. LOGIQUE DE TRAITEMENT DU FORMULAIRE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agriculteur_id = $_POST['agriculteur_id'] ?? null;
    $type_rapport = $_POST['type_rapport'] ?? null;
    $periode_debut = $_POST['periode_debut'] ?? null;
    $periode_fin = $_POST['periode_fin'] ?? null;

    if ($agriculteur_id && $type_rapport) {
        
        // --- REMPLACER CE BLOC PAR VOTRE VRAIE LOGIQUE D'INSERTION BDD ---
        /*
        $sql_insert = "INSERT INTO rapports (agriculteur_id, type_rapport, date_debut, date_fin, conseiller_id, statut)
                       VALUES (:agri_id, :type, :debut, :fin, :cons_id, 'En Cours')";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([
            ':agri_id' => $agriculteur_id, 
            ':type' => $type_rapport,
            ':debut' => $periode_debut,
            ':fin' => $periode_fin,
            ':cons_id' => $_SESSION['user_id']
        ]);
        */
        
        // --- REDIRECTION VERS L'HISTORIQUE APRÈS L'ENREGISTREMENT ---
        header("Location: rapport.php?creation=success&type=" . urlencode($type_rapport));
        exit();

    } else {
        $message = "Erreur : Veuillez sélectionner l'agriculteur et le type de rapport.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Générer un Rapport - MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- STYLES GÉNÉRAUX ET PALETTE --- */
        :root {
            --color-primary-emerald: #0ab9b1ff; 
            --color-primary-dark: #008080; 
            --color-secondary-gold: #fd9f07ff; 
            --color-secondary-beige-light: #f5f8f8ff;
            --color-card-bg: #FFFFFF;
            --color-light-bg: #F8F9FA;
            --color-text-dark: #1F2937;
            --sidebar-width: 260px;
            --border-radius-lg: 15px;
            --border-radius-sm: 8px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--color-light-bg);
            color: var(--color-text-dark);
            display: flex;
            min-height: 100vh;
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
        .sidebar .logo { font-family: 'Montserrat', sans-serif; color: var(--color-primary-emerald); font-size: 20px; font-weight: 800; text-align: center; padding: 0 20px 40px 20px; text-decoration : none; display: block; }
        .sidebar .logo i { color: var(--color-primary-emerald); font-size: 28px; margin-right: 5px; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; flex-grow: 1; display: flex; flex-direction: column; }
        .sidebar li a { display: flex; align-items: center; padding: 12px 25px; color: var(--color-text-dark); text-decoration: none; font-size: 15px; font-weight: 500; transition: all 0.2s ease-in-out; border-left: 0px solid transparent; }
        .sidebar li a i { margin-right: 15px; font-size: 18px; color: #4B5563; width: 25px; text-align: center; }
        .sidebar li a:hover { background-color: var(--color-secondary-beige-light); color: var(--color-primary-dark); }
        
        /* Lien Actif: Rapports */
        .sidebar li a[href="rapport_personnalise.php"] {
            background-color: rgba(6, 189, 189, 0.1);
            color: var(--color-primary-emerald);
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald);
        }
        .sidebar li a[href="rapport_personnalise.php"] i {
            color: var(--color-primary-emerald);
        }
        
        /* Bouton de déconnexion stylisé */
        .disconnect-item { 
            margin-top: auto; 
            padding: 25px; 
            display: block; 
        }
        .disconnect-item a { 
            display: flex; justify-content: center; align-items: center; gap: 10px; 
            background-color: var(--color-secondary-gold); 
            color: var(--color-card-bg) !important; 
            padding: 12px 20px; 
            border-radius: var(--border-radius-sm); 
            font-size: 15px; 
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(255, 140, 0, 0.4); 
            text-decoration: none; 
            border-left: none !important; 
            transition: background-color 0.2s;
        }
        .disconnect-item a i { 
            color: var(--color-card-bg) !important; 
        }
        .disconnect-item a:hover { 
            background-color: #E37D00 !important; 
        }

        /* --- CONTENU PRINCIPAL --- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px;
            flex-grow: 1;
            min-width: 0;
        }

        /* --- FORM STYLES --- */
        .form-container { 
            max-width: 700px; 
            margin: 40px auto; 
            background: var(--color-card-bg); 
            padding: 30px; 
            border-radius: var(--border-radius-lg); 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        }
        .form-container h2 { 
            font-family: 'Montserrat', sans-serif;
            color: var(--color-primary-emerald); 
            border-bottom: 2px solid var(--color-secondary-gold); 
            padding-bottom: 10px; 
            margin-top: 0;
            margin-bottom: 30px; 
            font-size: 24px;
        }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--color-text-dark); font-size: 15px; }
        select, input[type="date"] { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #D1D5DB; 
            border-radius: var(--border-radius-sm); 
            box-sizing: border-box; 
            font-size: 15px;
            background-color: #F9FAFB;
        }
        button[type="submit"] { 
            background-color: var(--color-primary-emerald); 
            color: var(--color-card-bg); 
            padding: 12px 25px; 
            border: none; 
            border-radius: var(--border-radius-sm); 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: 700;
            transition: background-color 0.3s; 
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        button[type="submit"]:hover { background-color: var(--color-primary-dark); }
        .message { 
            padding: 15px; 
            border-radius: var(--border-radius-sm); 
            margin-bottom: 20px; 
            background-color: #FFF3E0; 
            color: #E37D00; 
            border: 1px solid #FFC107;
        }
    </style>
</head>

<body>
    <nav class="sidebar">
        <a href="advisor_dashboard.php" class="logo">
             <i class="fas fa-microchip"></i> MonAgriCoach
        </a>

        <ul>
            <li><a href="advisor_dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a></li>
            <li><a href="outiis_analyse.php"><i class="fas fa-flask"></i> Outils d'analyse</a></li>
            <li><a href="rapport.php"><i class="fas fa-file-invoice"></i> **Rapports**</a></li>
            <li><a href="liste_message_cons.php"><i class="fas fa-comments"></i> Mes messages</a></li>
            <li><a href="liste_agriculteur_cons.php"><i class="fas fa-user-friends"></i> Agriculteurs</a></li>
            
          

            <li class="disconnect-item">
                <a href="deconnexion.php">
                    <i class="fas fa-sign-out-alt"></i> DÉCONNEXION
                </a>
            </li>
        </ul>
    </nav>


    <div class="main-content">
        
        <div class="form-container">
            <h2><i class="fas fa-plus-circle"></i> Création d'un Nouveau Rapport</h2>

            <?php if ($message): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form action="generate_new_report.php" method="POST">
                
                <div class="form-group">
                    <label for="agriculteur_id">Sélectionner l'Agriculteur :</label>
                    <select id="agriculteur_id" name="agriculteur_id" required>
                        <option value="">-- Choisir un agriculteur --</option>
                        <?php 
                        // Affichage des agriculteurs récupérés
                        foreach ($agriculteurs_liste as $agri): ?>
                            <option value="<?php echo htmlspecialchars($agri['id']); ?>">
                                <?php echo htmlspecialchars($agri['nom'] . ' ' . $agri['prenom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="type_rapport">Type de Rapport :</label>
                    <select id="type_rapport" name="type_rapport" required>
                        <option value="">-- Choisir le type --</option>
                        <?php foreach ($types_rapports as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>">
                                <?php echo htmlspecialchars($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="periode_debut">Période de Début (Optionnel) :</label>
                    <input type="date" id="periode_debut" name="periode_debut" value="<?php echo date('Y-m-d', strtotime('-1 month')); ?>">
                </div>
                
                <div class="form-group">
                    <label for="periode_fin">Période de Fin (Optionnel) :</label>
                    <input type="date" id="periode_fin" name="periode_fin" value="<?php echo date('Y-m-d'); ?>">
                </div>

                <button type="submit">
                    <i class="fas fa-save"></i> Enregistrer et Retourner à la Liste
                </button>
                
            </form>
        </div>

    </div>

</body>
</html>
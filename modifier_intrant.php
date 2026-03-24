<?php
// Inclure la connexion à la base de données
require_once 'db.php';

// Initialisation des variables
$id = $_GET["id"] ?? 0; 
$id = (int) $id; 
$intrant = null;
$message = null;

// Vérifier si l'ID est valide et charger les données
if ($id > 0) {
    try {
        // Préparer la requête pour récupérer les données de l'intrant
        // NOTE: J'ai corrigé les majuscules des colonnes pour correspondre à votre requête
        $sql = "SELECT id, nom_intrant, type_intrant, unite_standard, descriptions 
                FROM intrant 
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $intrant = $stmt->fetch(PDO::FETCH_ASSOC); 
        
        if (!$intrant) {
            $message = "Intrant non trouvé."; 
        } else {
            $message = "Données de l'intrant chargées avec succès. Modifiez les champs ci-dessous.";
        }
    } catch (PDOException $e) {
        error_log("Erreur PDO lors de la récupération de l'intrant : " . $e->getMessage());
        $message = "Une erreur est survenue lors du chargement des données. Veuillez réessayer.";
    }
} else {
    $message = "ID d'intrant invalide ou manquant.";
}
?>

<!DOCTYPE html>
<html lang="fr"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Intrant | MonAgriCoach</title> 
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
        /* Lien Actif : Intrant */
        .sidebar li a[href="liste_intrant.php"] { 
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-emerald); 
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald); 
        }
        .sidebar li a[href="liste_intrant.php"] i {
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
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> **Engrais**</a></li>
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
                <h1>Modification de l'Engrais : <?= htmlspecialchars($intrant['nom_intrant'] ?? 'N/A'); ?></h1>
            </div>
            
            <div class="form-container">
                <?php if ($message): ?>
                    <div class="message-box <?= $intrant ? 'info-message' : 'error-message'; ?>">
                        <i class="fas <?= $intrant ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i> <?= htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($intrant): ?>
                    <form action="update_intrant.php" method="POST">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($intrant['id']); ?>">
                        
                        <div>
                            <label for="nom_intrant"><i class="fas fa-tag"></i> Nom de l'Intrant</label>
                            <input type="text" id="nom_intrant" name="nom_intrant" value="<?= htmlspecialchars($intrant['nom_intrant']); ?>" placeholder="Ex: Urée 46%, Anti-parasite X" required>
                        </div>

                        <div>
                            <label for="type_intrant"><i class="fas fa-filter"></i> Type d'Intrant</label>
                            <select id="type_intrant" name="type_intrant" required>
                                <?php 
                                    // Liste des types d'intrants courants. Vous pouvez ajuster cette liste.
                                    $types_intrant = ['Engrais', 'Pesticide', 'Herbicide', 'Fongicide', 'Semence', 'Autre'];
                                    $current_type = htmlspecialchars($intrant['type_intrant']);
                                    foreach ($types_intrant as $type) {
                                        $selected = ($type == $current_type) ? 'selected' : '';
                                        echo "<option value=\"$type\" $selected>$type</option>";
                                    }
                                    // Si la valeur actuelle n'est pas dans la liste, l'ajouter comme option sélectionnée
                                    if (!in_array($current_type, $types_intrant) && $current_type) {
                                        echo "<option value=\"$current_type\" selected>$current_type</option>";
                                    }
                                ?>
                            </select>
                        </div>

                        <div>
                            <label for="unite_standard"><i class="fas fa-balance-scale"></i> Unité Standard</label>
                            <select id="unite_standard" name="unite_standard" required>
                                <?php 
                                    // Liste des unités courantes. Vous pouvez ajuster cette liste.
                                    $unites = ['Kg', 'Litre', 'Unité', 'Sac', 'm2', 'Ha'];
                                    $current_unite = htmlspecialchars($intrant['unite_standard']);
                                    foreach ($unites as $unite) {
                                        $selected = ($unite == $current_unite) ? 'selected' : '';
                                        echo "<option value=\"$unite\" $selected>$unite</option>";
                                    }
                                    // Si la valeur actuelle n'est pas dans la liste, l'ajouter comme option sélectionnée
                                    if (!in_array($current_unite, $unites) && $current_unite) {
                                        echo "<option value=\"$current_unite\" selected>$current_unite</option>";
                                    }
                                ?>
                            </select>
                        </div>

                        <div>
                            <label for="id_display"><i class="fas fa-fingerprint"></i> ID Intrant (Non Modifiable)</label>
                            <input type="text" id="id_display" value="<?= htmlspecialchars($intrant['id']); ?>" readonly style="background-color: #F8F9FA; color: var(--color-text-medium);">
                        </div>
                        
                        <div class="full-width">
                            <label for="descriptions"><i class="fas fa-file-alt"></i> Descriptions Détaillées</label>
                            <textarea id="descriptions" name="descriptions" rows="4" placeholder="Indiquer les composants, le dosage recommandé, les précautions..."><?= htmlspecialchars($intrant['descriptions']); ?></textarea>
                        </div>
                        
                        <div class="submit-button-wrapper">
                            <input type="submit" value="Enregistrer les Modifications">
                        </div>
                    </form>
                <?php else: ?>
                    <p style="text-align: center; margin-top: 20px;">
                        <a href="liste_intrant.php" style="color: var(--color-primary-emerald); font-weight: 600;">Retour à la liste des intrants</a>
                    </p>
                <?php endif; ?>
            </div>
            
           
        </div>
    </div>
</body>
</html>
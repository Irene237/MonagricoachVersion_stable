<?php
// Démarre la session au tout début du script
session_start();

// Inclut le fichier de connexion à la base de données
require_once 'db.php';

// Vérifie si l'utilisateur est connecté, sinon le redirige
if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) {
    header("Location: connexion.php"); // Corrigé à 'connexion.php' pour la cohérence
    exit();
}

$current_user_id = $_SESSION['user_id'];
$message = '';
$message_type = ''; // Utilisé pour le style (success/error)

// --- 1. Gère l'envoi du formulaire ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && 
    isset($_POST['id_agriculteur']) && 
    isset($_POST['nom_intrant']) && 
    isset($_POST['type_intrant']) && 
    isset($_POST['unite_standard']) && 
    isset($_POST['descriptions'])) {
    
    $nom_intrant = trim($_POST["nom_intrant"]);
    $id_agriculteur = (int)($_POST["id_agriculteur"] ?? 0);
    $type_intrant = trim($_POST["type_intrant"]);
    $unite_standard = trim($_POST["unite_standard"]);
    $descriptions = trim($_POST["descriptions"]);
    
    // Vérification de l'ID de l'agriculteur soumis pour des raisons de sécurité
    if ($id_agriculteur != $current_user_id) {
        $message = 'Erreur de sécurité : L\'ID de l\'agriculteur ne correspond pas à l\'utilisateur connecté.';
        $message_type = 'error';
    } elseif (empty($id_agriculteur) || empty($nom_intrant) || empty($type_intrant) || empty($unite_standard) || empty($descriptions)) { 
        $message = 'Veuillez remplir tous les champs.'; 
        $message_type = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO intrant(id_agriculteur, nom_intrant, type_intrant, unite_standard, descriptions) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$id_agriculteur, $nom_intrant, $type_intrant, $unite_standard, $descriptions])) {
                // Redirection vers la liste des intrants après un enregistrement réussi
                header("Location: liste_intrant.php?success=new_intrant");
                exit();
            } else {
                 $message = "Erreur lors de l'enregistrement de l'intrant.";
                 $message_type = 'error';
            }
        } catch (PDOException $e) {
            error_log("Erreur: " . $e->getMessage());
            $message = "Une erreur est survenue : " . $e->getMessage();
            $message_type = 'error';
        }
    }
}


// --- 2. Récupération des données de l'agriculteur pour l'affichage ---
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

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enregistrer un Intrant | MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* ----------------------------------------------------- */
        /* --- STYLES GLOBALS & PALETTE --- */
        /* ----------------------------------------------------- */
        :root {
            --color-primary-emerald: #06bdbdff; 
            --color-primary-dark: #0cb4b4ff; 
            --color-secondary-gold: #FF8C00; /* Orange Vif (Déconnexion) */
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

        /* Lien Actif: Intrants */
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
        /* --- CONTENU PRINCIPAL & FORMULAIRE (AGRANDI) --- */
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
            /* Agrandissement de la container pour le formulaire */
            max-width: 800px; 
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
        .alert-message.success {
            color: #065f46; 
            background-color: #D1FAE5; 
            border: 1px solid var(--color-accent-success);
        }

        form {
            width: 100%; 
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: 600;
            color: var(--color-text-dark);
            margin-top: 5px;
        }

        input[type="text"], select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #D1D5DB; 
            border-radius: 6px;
            font-size: 15px;
            color: var(--color-text-dark);
            box-sizing: border-box; 
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        input[type="text"]:focus, select:focus, textarea:focus {
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
        }

        /* Champ de description */
        textarea[name="descriptions"] {
            min-height: 100px;
            resize: vertical;
        }

        .button {
            display: flex;
            justify-content: flex-end; /* Alignement à droite des boutons */
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
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> **Engrais**</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i> Messagerie</a></li>
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i> Verser l'engrais</a></li>
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
        <div class="form-container">
            <div class="header-content">
                <h2>Enregistrer un Intrant 🧪</h2>
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

                <label for="agriculteur_display">Agriculteur :</label>
                <div id="agriculteur_display">
                    <?= $agriculteur_nom_complet; ?>
                </div>

                <label for="nom_intrant">Nom de l'intrant :</label>
                <input type="text" id="nom_intrant" name="nom_intrant" required placeholder="Ex: Urée 46%, Anti-puceron, etc."/>
                
                <label for="type_intrant">Type de l'intrant :</label>
                <select id="type_intrant" name="type_intrant" required>
                    <option value="">-- Sélectionnez un type --</option>
                    <option value="Fertilisant">Fertilisant</option>
                    <option value="Pesticide">Pesticide (Insecticide, Fongicide)</option>
                    <option value="Herbicide">Herbicide</option>
                    <option value="Semence">Semence/Plant (si l'intrant est une culture)</option>
                    <option value="Autre">Autre</option>
                </select>
                
                <label for="unite_standard">Unité standard :</label>
                <input type="text" id="unite_standard" name="unite_standard" required placeholder="Ex: kg, Litre, Sac de 50kg, Unité"/>
                
                <label for="descriptions">Description :</label>
                <textarea id="descriptions" name="descriptions" required placeholder="Détaillez la composition, la marque, ou son usage principal (facultatif)."></textarea>
                
                <div class="button">
                    <input type="reset" value="Réinitialiser">
                    <input type="submit" value="Enregistrer"/>
                </div>
            </form>

        </div>
    </div>
</body>
</html>
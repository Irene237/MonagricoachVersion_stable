<?php
// Fichier : outiis_analyse.php
session_start();
// **INCLUSION DE LA BASE DE DONNÉES**
require_once 'db.php'; // Assurez-vous que ce fichier initialise $pdo

// --- Bloc de Vérification de Connexion (Sécurité minimale) ---
if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    header("Location: connexion.php");
    exit();
}

$user_email = htmlspecialchars($_SESSION['user_email'] ?? 'Conseiller Agricole');
$current_time = date('H:i');

$agriculteurs_simules = []; 

try {
    // Récupération des agriculteurs (utilisant les colonnes : id, nom, prenom)
    $sql_agriculteurs = "SELECT id, nom, prenom FROM utilisateur 
                         WHERE type_utilisateur = 'agriculteur' 
                         ORDER BY nom, prenom";
    $stmt_agriculteurs = $pdo->query($sql_agriculteurs);
    $agriculteurs_bdd = $stmt_agriculteurs->fetchAll(PDO::FETCH_ASSOC);

    // Préparation des données pour le formulaire
    foreach ($agriculteurs_bdd as $agri) {
        $nom_complet = htmlspecialchars($agri['nom'] . ' ' . $agri['prenom']);
        $agriculteurs_simules[] = [
            'id' => $agri['id'],
            'nom' => $nom_complet,
            'parcelles' => ['P1 - Maïs', 'P2 - Manioc'] 
        ];
    }

} catch (PDOException $e) {
    error_log("Erreur de BDD lors de la récupération des agriculteurs (analyse) : " . $e->getMessage());
    $agriculteurs_simules = [
        ['id' => 999, 'nom' => 'Erreur de Base de Données', 'parcelles' => ['P-Maïs']],
    ];
}

// Données statiques de diagnostic (maintenues pour l'affichage de démo)
$diagnostic_resultat = [
    'pH' => 5.5,
    'azote' => 'Faible',
    'phosphore' => 'Moyen',
    'potassium' => 'Bon',
    'recommendation' => 'Ajouter 150 kg/ha de NPK (10-20-10) pour améliorer l\'apport en Azote et Phosphore.',
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Outils d'Analyse Technique - MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* --- PALETTE & GÉNÉRAL --- */
        :root {
            --color-primary-emerald: #0ab9b1ff; 
            --color-primary-dark: #008080; 
            --color-secondary-gold: #fd9f07ff; 
            --color-secondary-beige-light: #f5f8f8ff;
            --color-secondary-beige-hover: #e0f8f8;

            --color-heading: #1F2937;
            --color-accent-warning: #fc8e08ff;
            --color-accent-danger: #df1313ff;
            --color-accent-blue: #1E90FF;

            --color-light-bg: #F8F9FA;
            --color-card-bg: #FFFFFF;
            --color-text-dark: #1F2937;
            --color-text-medium: #4B5563;
            --color-text-light: #9CA3AF;
            --color-success: #34A853;

            --font-main: 'Poppins', sans-serif;
            --font-heading: 'Montserrat', sans-serif;
            --sidebar-width: 260px;
            --border-radius-lg: 15px;
            --border-radius-sm: 8px;
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
            background-color: var(--color-secondary-beige-light);
            color: var(--color-primary-dark);
        }
        
        /* Lien Actif (outiis_analyse.php) */
        .sidebar li a[href="outiis_analyse.php"] {
            background-color: rgba(6, 189, 189, 0.1);
            color: var(--color-primary-emerald);
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald);
        }
        .sidebar li a[href="outiis_analyse.php"] i {
            color: var(--color-primary-emerald);
        }


        /* Bouton Déconnexion dans la Sidebar (Orange, en bas) */
        .disconnect-item {
            margin-top: auto; 
            padding: 25px;
            display: block; 
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
            background-color: var(--color-secondary-gold); 
            color: var(--color-card-bg) !important; 
            padding: 12px 20px;
            border-radius: var(--border-radius-sm);
            font-size: 15px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(255, 140, 0, 0.4);
            transition: all 0.3s;
            text-decoration: none;
            border-left: none !important; 
        }
        .disconnect-item a i {
             color: var(--color-card-bg) !important; 
        }
        /* Style de survol spécifique au bouton Déconnexion */
        .disconnect-item a:hover {
            background-color: #E37D00 !important; 
            color: var(--color-card-bg) !important; 
            box-shadow: 0 6px 20px rgba(255, 140, 0, 0.6);
        }
        .disconnect-item a:hover i {
            color: var(--color-card-bg) !important; 
        }

        /* --- CONTENU PRINCIPAL --- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px;
            flex-grow: 1;
            min-width: 0;
        }
        
        /* En-tête de la Page (Nouveau style technique) */
        .header-tools {
            background-color: var(--color-card-bg);
            border-bottom: 3px solid var(--color-primary-emerald);
            padding: 20px 30px;
            margin-bottom: 40px;
            border-radius: var(--border-radius-lg);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .header-tools h2 {
            font-family: var(--font-heading);
            font-weight: 800;
            margin-top: 0;
            font-size: 28px;
            color: var(--color-primary-dark);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .header-tools p {
            color: var(--color-text-medium);
            font-size: 15px;
            margin: 5px 0 0 0;
        }

        /* --- SÉLECTION DE L'AGRICULTEUR/PARCELLE --- */
        .selection-bar {
            background-color: #E0F8F8; /* Léger fond pour la barre de sélection */
            padding: 20px;
            border-radius: var(--border-radius-lg);
            margin-bottom: 30px;
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .selection-bar label {
            font-weight: 600;
            color: var(--color-primary-dark);
        }

        .selection-bar select, .selection-bar button {
            padding: 10px 15px;
            border-radius: var(--border-radius-sm);
            border: 1px solid #B0D9D9;
            font-size: 15px;
            transition: all 0.3s;
        }

        .selection-bar select {
            background-color: var(--color-card-bg);
            flex-grow: 1;
            min-width: 180px;
            max-width: 300px;
        }

        .selection-bar button {
            background-color: var(--color-primary-emerald);
            color: var(--color-card-bg);
            font-weight: 700;
            cursor: pointer;
            border: none;
        }
        .selection-bar button:hover {
            background-color: var(--color-primary-dark);
        }


        /* --- OUTILS DE CALCULS ET DIAGNOSTICS (Grille principale) --- */
        .tools-grid {
            display: grid;
            grid-template-columns: 1fr; 
            gap: 30px;
        }
        
        .tool-card {
            background: var(--color-card-bg);
            padding: 30px;
            border-radius: var(--border-radius-lg);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .tool-card h3 {
            font-family: var(--font-heading);
            font-size: 20px;
            color: var(--color-secondary-gold);
            border-bottom: 2px solid #EEE;
            padding-bottom: 10px;
            margin-top: 0;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Formulaire de saisie */
        .form-analyse label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--color-text-dark);
        }

        .form-analyse input[type="number"], .form-analyse textarea {
            width: 100%;
            padding: 10px;
            border-radius: var(--border-radius-sm);
            border: 1px solid #D1D5DB;
            box-sizing: border-box;
            font-size: 15px;
        }
        .form-analyse textarea {
            min-height: 80px;
        }

        .form-analyse button {
            margin-top: 25px;
            padding: 12px 25px;
            background-color: var(--color-secondary-gold);
            color: var(--color-card-bg);
            font-weight: 700;
            border: none;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .form-analyse button:hover {
            background-color: #E37D00;
        }

        /* Section Résultats/Diagnostic */
        .results-section {
            background-color: #F0F9FF; /* Fond bleu très léger */
            border-left: 5px solid var(--color-accent-blue);
            padding: 25px;
            border-radius: var(--border-radius-sm);
            margin-top: 20px;
        }
        .results-section h4 {
            font-family: var(--font-heading);
            color: var(--color-accent-blue);
            margin-top: 0;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .result-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dotted #D1D5DB;
        }
        .result-item:last-child {
            border-bottom: none;
        }

        .result-item span:first-child {
            font-weight: 500;
            color: var(--color-text-medium);
        }
        .result-item span:last-child {
            font-weight: 700;
            color: var(--color-heading);
        }
        .recommendation-box {
            margin-top: 20px;
            padding: 15px;
            background-color: var(--color-success);
            color: var(--color-card-bg);
            border-radius: var(--border-radius-sm);
            font-weight: 600;
        }
        .recommendation-box i {
            margin-right: 10px;
        }

        /* --- Réactivité --- */
        @media (min-width: 1024px) {
            .tools-grid {
                grid-template-columns: 2fr 1fr; 
            }
        }
        
        @media (max-width: 768px) {
            body { display: block; }
            .sidebar { position: relative; height: auto; }
            .sidebar ul { flex-direction: column; }
            .sidebar li a { justify-content: flex-start; }
            .disconnect-item { display: block; }
            
            .main-content { margin-left: 0; padding: 20px; }
            .selection-bar { flex-direction: column; align-items: stretch; }
            .selection-bar select, .selection-bar button { max-width: 100%; }
        }
    </style>
</head>

<body>
    <nav class="sidebar">
       <a href="index.php" class="logo">
                <i class="fas fa-leaf"></i> MonAgriCoach
            </a>

        <ul>
            <li><a href="advisor_dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a></li>
            <li><a href="outiis_analyse.php"><i class="fas fa-flask"></i> **Outils d'analyse**</a></li>
            <li><a href="rapport.php"><i class="fas fa-file-invoice"></i> Rapports</a></li>
            <li><a href="liste_message_cons.php"><i class="fas fa-comments"></i> Mes messages</a></li>
            <li><a href="liste_agriculteur_cons.php"><i class="fas fa-user-friends"></i> Agriculteurs</a></li>
              <li><a href="modifier_profile.php" class="active"><i class="fas fa-user-edit"></i> Modifier mon compte</a></li>
            


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
        
        <div class="header-tools">
            <h2><i class="fas fa-calculator"></i> Outils d'Analyse des Plantations</h2> 
            <p>Utilisez les outils de simulation pour effectuer des diagnostics agronomiques avancés et générer des recommandations.</p>
        </div>

        <div class="selection-bar">
            <label for="select-agriculteur">Agriculteur :</label>
            <select id="select-agriculteur">
                <?php 
                if (!empty($agriculteurs_simules)): 
                    foreach ($agriculteurs_simules as $agri): ?>
                        <option value="<?php echo $agri['id']; ?>"><?php echo htmlspecialchars($agri['nom']); ?></option>
                    <?php endforeach; 
                else: ?>
                    <option value="">(Aucun agriculteur trouvé)</option>
                <?php endif; ?>
            </select>
            
            <label for="select-parcelle">Parcelle :</label>
            <select id="select-parcelle">
                <option value="P1">P1 - Maïs</option>
                <option value="P2">P2 - Manioc</option>
                <option value="P3">P3 - Cacao</option>
            </select>

            <button><i class="fas fa-search"></i> Charger l'Historique</button>
        </div>

        <div class="tools-grid">

            <div class="calculation-tools">

                <div class="tool-card">
                    <h3><i class="fas fa-chart-line"></i> Simulation de Rendement Potentiel</h3>
                    <form class="form-analyse">
                        <label for="surface">Surface de la parcelle (Ha) :</label>
                        <input type="number" id="surface" name="surface" value="1.5" step="0.1" required>
                        
                        <label for="densite">Densité de plantation (Plants/Ha) :</label>
                        <input type="number" id="densite" name="densite" value="45000" required>

                        <label for="facteur_clim">Facteur Climatique (0.5 à 1.0) :</label>
                        <input type="number" id="facteur_clim" name="facteur_clim" value="0.85" step="0.01" min="0.5" max="1.0" required>

                        <button type="submit"><i class="fas fa-calculator"></i> Calculer le Rendement</button>
                    </form>
                    <div class="results-section" style="margin-top: 25px;">
                        <h4><i class="fas fa-truck-loading"></i> Résultat Simulé</h4>
                        <div class="result-item">
                            <span>Rendement estimé (Kg) :</span>
                            <span style="color: var(--color-success);">6 885 Kg</span>
                        </div>
                        <div class="result-item">
                            <span>Rendement potentiel (Kg/Ha) :</span>
                            <span style="color: var(--color-heading);">4 590 Kg/Ha</span>
                        </div>
                    </div>
                </div>

                <div class="tool-card" style="margin-top: 30px;">
                    <h3><i class="fas fa-balance-scale"></i> Diagnostic Rapide des Nutriments</h3>
                    <form class="form-analyse">
                        <label for="ph_value">Valeur pH du Sol (Mesurée) :</label>
                        <input type="number" id="ph_value" name="ph_value" value="5.5" step="0.1" min="4.0" max="8.0" required>
                        
                        <label for="symptomes">Symptômes/Observations :</label>
                        <textarea id="symptomes" name="symptomes">Feuilles jaunissantes et croissance ralentie.</textarea>

                        <button type="submit"><i class="fas fa-microscope"></i> Analyser les Données</button>
                    </form>
                </div>
            </div>

            <div class="diagnostic-results">
                <div class="tool-card">
                    <h3><i class="fas fa-clipboard-check"></i> Rapport de Diagnostic (Basé sur P1 - Maïs)</h3>
                    
                    <div class="results-section">
                        <h4><i class="fas fa-atom"></i> Synthèse de l'Analyse Sol</h4>
                        <div class="result-item">
                            <span>pH actuel :</span>
                            <span style="color: <?php echo ($diagnostic_resultat['pH'] < 6.0) ? 'var(--color-accent-danger)' : 'var(--color-success)'; ?>;">
                                <?php echo htmlspecialchars($diagnostic_resultat['pH']); ?> (Acide)
                            </span>
                        </div>
                        <div class="result-item">
                            <span>Niveau Azote (N) :</span>
                            <span style="color: <?php echo ($diagnostic_resultat['azote'] == 'Faible') ? 'var(--color-accent-danger)' : 'var(--color-success)'; ?>;">
                                <?php echo htmlspecialchars($diagnostic_resultat['azote']); ?>
                            </span>
                        </div>
                         <div class="result-item">
                            <span>Niveau Phosphore (P) :</span>
                            <span style="color: <?php echo ($diagnostic_resultat['phosphore'] == 'Moyen') ? 'var(--color-secondary-gold)' : 'var(--color-success)'; ?>;">
                                <?php echo htmlspecialchars($diagnostic_resultat['phosphore']); ?>
                            </span>
                        </div>
                         <div class="result-item">
                            <span>Niveau Potassium (K) :</span>
                            <span style="color: var(--color-success);">
                                <?php echo htmlspecialchars($diagnostic_resultat['potassium']); ?>
                            </span>
                        </div>

                        <div class="recommendation-box">
                            <i class="fas fa-hand-holding-medical"></i>
                            **Recommandation d'Action :** <?php echo htmlspecialchars($diagnostic_resultat['recommendation']); ?>
                        </div>
                        
                    </div>

                    <p style="margin-top: 25px; font-size: 13px; color: var(--color-text-light);">
                        *Ceci est un diagnostic simulé. Les résultats réels dépendent des données analytiques soumises.*
                    </p>
                </div>
            </div>

        </div>

    </div>

</body>
</html>
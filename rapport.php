<?php
// Fichier : rapport.php
session_start();
require_once 'db.php'; // Assurez-vous que ce fichier initialise $pdo

// --- Bloc de Vérification de Connexion (Sécurité minimale) ---
if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    header("Location: connexion.php");
    exit();
}

$user_email = htmlspecialchars($_SESSION['user_email'] ?? 'Conseiller Agricole');
$current_time = date('H:i');

$agriculteurs_a_filtrer = [];

try {
    // Récupération des agriculteurs (utilisant les colonnes : nom, prenom)
    $sql_agriculteurs = "SELECT nom, prenom FROM utilisateur 
                         WHERE type_utilisateur = 'agriculteur' 
                         ORDER BY nom, prenom";
    $stmt_agriculteurs = $pdo->query($sql_agriculteurs);
    $agriculteurs_bdd = $stmt_agriculteurs->fetchAll(PDO::FETCH_ASSOC);

    foreach ($agriculteurs_bdd as $agri) {
        $agriculteurs_a_filtrer[] = htmlspecialchars($agri['nom'] . ' ' . $agri['prenom']);
    }

} catch (PDOException $e) {
    error_log("Erreur de BDD lors de la récupération des agriculteurs pour rapport : " . $e->getMessage());
    $agriculteurs_a_filtrer = ['Erreur BDD - Talla K.', 'Erreur BDD - Ngomo A.']; 
}

// Données statiques de Démonstration pour les rapports 
$rapports_simules = [
    [
        'id' => 1,
        'agriculteur' => 'Romari rin', 
        'type' => 'Rapport de Rendement',
        'date' => '2025-12-10',
        'statut' => 'Généré',
        'lien_voir' => 'view_report.php?id=1',
        'lien_export_pdf' => 'export_pdf.php?id=1'
    ],
    [
        'id' => 2,
        'agriculteur' => 'helsinky james',
        'type' => 'Bilan de Fertilisation',
        'date' => '2025-11-28',
        'statut' => 'Archivé',
        'lien_voir' => 'view_report.php?id=2',
        'lien_export_pdf' => 'export_pdf.php?id=2'
    ],
    [
        'id' => 3,
        'agriculteur' => 'riu tyty',
        'type' => 'Diagnostic Parasitaire',
        'date' => '2025-11-15',
        'statut' => 'Généré',
        'lien_voir' => 'view_report.php?id=3',
        'lien_export_pdf' => 'export_pdf.php?id=3'
    ],
    [
        'id' => 4,
        'agriculteur' => 'riso tyty',
        'type' => 'Bilan de Fertilisation',
        'date' => '2025-11-01',
        'statut' => 'En Cours',
        'lien_voir' => 'view_report.php?id=4',
        'lien_export_pdf' => 'export_pdf.php?id=4'
    ],
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapports Personnalisés - MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* --- PALETTE & GÉNÉRAL (Identique aux autres pages) --- */
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
        
        /* Lien Actif (rapport.php) */
        .sidebar li a[href="rapport_personnalise.php"] {
            background-color: rgba(6, 189, 189, 0.1);
            color: var(--color-primary-emerald);
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald);
        }
        .sidebar li a[href="rapport_personnalise.php"] i {
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
        
        /* En-tête de la Page */
        .header-reports {
            background-color: var(--color-card-bg);
            border-bottom: 3px solid var(--color-secondary-gold);
            padding: 20px 30px;
            margin-bottom: 40px;
            border-radius: var(--border-radius-lg);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .header-reports h2 {
            font-family: var(--font-heading);
            font-weight: 800;
            margin-top: 0;
            font-size: 28px;
            color: var(--color-secondary-gold);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .header-reports p {
            color: var(--color-text-medium);
            font-size: 15px;
            margin: 5px 0 0 0;
        }

        /* --- FILTRES ET BOUTONS DE CONTRÔLE --- */
        .reports-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--color-card-bg);
            padding: 20px;
            border-radius: var(--border-radius-lg);
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            flex-wrap: wrap;
            gap: 15px;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group label {
            font-weight: 600;
            color: var(--color-text-dark);
            font-size: 14px;
        }
        .filter-group select {
            padding: 8px 12px;
            border-radius: var(--border-radius-sm);
            border: 1px solid #D1D5DB;
            font-size: 14px;
        }

        /* Conteneur des Boutons */
        .button-group-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        /* Style général des boutons d'action en haut */
        .btn-action-top {
            padding: 10px 20px;
            font-weight: 700;
            border: none;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none; 
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* Bouton "Générer un Nouveau Rapport" */
        .btn-generate {
            background-color: var(--color-primary-emerald);
            color: var(--color-card-bg);
        }
        .btn-generate:hover {
            background-color: var(--color-primary-dark);
        }

        /* Bouton "Exporter la liste (CSV)" */
        .btn-export-list {
            background-color: var(--color-secondary-gold);
            color: var(--color-card-bg);
        }
        .btn-export-list:hover {
            background-color: #E37D00;
        }


        /* --- TABLEAU DES RAPPORTS --- */
        .reports-table-container {
            background-color: var(--color-card-bg);
            border-radius: var(--border-radius-lg);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            padding: 0 30px 30px 30px;
            overflow-x: auto; 
        }
        .reports-table-container h3 {
            font-family: var(--font-heading);
            margin-top: 0;
            color: var(--color-heading);
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 15px;
            padding-top: 30px; 
            margin-bottom: 25px;
            font-weight: 700;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .data-table {
            width: 100%;
            min-width: 750px; 
            border-collapse: separate; 
            border-spacing: 0;
            overflow: hidden; 
        }

        .data-table th, .data-table td {
            text-align: left;
            padding: 15px 18px; 
            font-size: 14px;
        }

        .data-table th {
            background-color: var(--color-secondary-beige-light);
            color: var(--color-primary-dark);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--color-secondary-gold); 
        }

        .data-table tbody tr:nth-child(even) { background-color: #FDFEFE; }
        .data-table tbody tr:nth-child(odd) { background-color: var(--color-card-bg); }
        .data-table td { border-bottom: 1px solid #E5E7EB; color: var(--color-text-dark); }
        .data-table tbody tr:last-child td { border-bottom: none; }
        
        .data-table tbody tr:hover {
            background-color: var(--color-secondary-beige-hover);
            box-shadow: inset 3px 0 0 0 var(--color-primary-emerald);
        }

        /* Styles des badges */
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
            min-width: 80px; 
            text-align: center;
        }

        .status-Généré { background-color: #E0F0E0; color: var(--color-success); } 
        .status-Archivé { background-color: #F0F0F0; color: var(--color-text-medium); }
        .status-En_Cours { background-color: #FFF3E0; color: var(--color-secondary-gold); }

        /* Styles des boutons d'action par ligne */
        .action-group {
            display: flex;
            gap: 5px;
        }
        .btn-action {
            padding: 6px 10px;
            border: 1px solid #D1D5DB;
            background-color: #F9FAFB;
            color: var(--color-primary-dark);
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            font-size: 13px;
            transition: all 0.2s;
            white-space: nowrap; 
        }
        /* Style pour le bouton PDF par ligne (Rouge) */
        .btn-export-pdf {
            background-color: var(--color-accent-danger);
            color: var(--color-card-bg);
            border: 1px solid var(--color-accent-danger);
        }

        .btn-action:hover {
            background-color: var(--color-secondary-beige-light);
            border-color: var(--color-primary-emerald);
            color: var(--color-primary-emerald);
        }
        .btn-export-pdf:hover {
            background-color: #B21F1F;
            border-color: #B21F1F;
            color: var(--color-card-bg);
        }


        /* --- Réactivité --- */
        @media (max-width: 768px) {
            body { display: block; }
            .sidebar { position: relative; height: auto; }
            .sidebar ul { flex-direction: column; }
            .sidebar li a { justify-content: flex-start; }
            .disconnect-item { display: block; }
            
            .main-content { margin-left: 0; padding: 20px; }
            .reports-controls { flex-direction: column; align-items: stretch; }
            .filter-group { justify-content: space-between; width: 100%; }
            .button-group-actions { justify-content: space-between; width: 100%; }
            .btn-action-top { flex-grow: 1; justify-content: center; }

            .reports-table-container { padding: 0 10px 20px 10px; }
            .data-table { min-width: 600px; } 
            .action-group { flex-direction: column; gap: 8px; align-items: stretch; }
            .btn-action { text-align: center; }
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
            <li><a href="outiis_analyse.php"><i class="fas fa-flask"></i> Outils d'analyse</a></li>
            <li><a href="rapport.php"><i class="fas fa-file-invoice"></i> **Rapports**</a></li>
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
        
        <div class="header-reports">
            <h2><i class="fas fa-file-pdf"></i> Rapports Personnalisés</h2> 
            <p>Générez, consultez et archivez les bilans détaillés pour vos agriculteurs.</p>
        </div>

        <div class="reports-controls">
            <div class="filter-group">
                <label for="filtre-agriculteur">Filtrer par Agriculteur :</label>
                <select id="filtre-agriculteur">
                    <option value="">Tous les Agriculteurs</option>
                    <?php 
                    if (!empty($agriculteurs_a_filtrer)):
                        foreach ($agriculteurs_a_filtrer as $nom_agri): ?>
                            <option value="<?php echo $nom_agri; ?>"><?php echo $nom_agri; ?></option>
                        <?php endforeach;
                    endif; ?>
                </select>
                
                <label for="filtre-type">Type de Rapport :</label>
                <select id="filtre-type">
                    <option value="">Tous les types</option>
                    <option value="Rendement">Rapport de Rendement</option>
                    <option value="Fertilisation">Bilan de Fertilisation</option>
                    <option value="Parasitaire">Diagnostic Parasitaire</option>
                </select>
            </div>
            
            <div class="button-group-actions">
                <a href="generate_new_report.php" class="btn-action-top btn-generate">
                    <i class="fas fa-plus"></i> Générer Rapport
                </a>

                <a href="export_list.php" class="btn-action-top btn-export-list" title="Exporter le tableau complet en format CSV">
                    <i class="fas fa-file-csv"></i> Exporter la liste
                </a>
            </div>
        </div>

        <div class="reports-table-container">
            <h3><i class="fas fa-history"></i> Historique des Rapports</h3>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Agriculteur</th>
                        <th>Type de Rapport</th>
                        <th>Date de Génération</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rapports_simules as $rapport): ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($rapport['id']); ?></td>
                        <td>**<?php echo htmlspecialchars($rapport['agriculteur']); ?>**</td>
                        <td><?php echo htmlspecialchars($rapport['type']); ?></td>
                        <td><?php echo htmlspecialchars($rapport['date']); ?></td>
                        <td>
                            <?php 
                                $status_class = 'status-' . str_replace(' ', '_', htmlspecialchars($rapport['statut']));
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo htmlspecialchars($rapport['statut']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-group">
                                <a href="<?php echo $rapport['lien_voir']; ?>" class="btn-action" target="_blank" title="Voir le rapport">
                                    <i class="fas fa-eye"></i> Voir
                                </a>
                                <a href="<?php echo $rapport['lien_export_pdf']; ?>" class="btn-action btn-export-pdf" title="Exporter ce rapport en PDF">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p style="margin-top: 25px; font-size: 13px; color: var(--color-text-light); text-align: center;">
                Affichage de <?php echo count($rapports_simules); ?> rapports.
            </p>
        </div>

    </div>

</body>
</html>
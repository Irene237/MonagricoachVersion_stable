<?php
// Fichier : view_report.php
session_start();
require_once 'db.php'; 

// --- Sécurité et Récupération de l'ID ---
if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    header("Location: connexion.php");
    exit();
}

// 1. Récupérer l'ID du rapport dans l'URL
$rapport_id = $_GET['id'] ?? null;
$rapport_details = null;
$message = '';
$rapport_trouve = false;

// 2. Initialiser les données simulées (en cas de non-connexion BDD)
$simulated_data = [
    // Rapport 1 (Rendement)
    1 => [
        'id' => 1,
        'agriculteur' => 'Romari rin (ID: 999)',
        'type' => 'Rapport de Rendement',
        'conseiller' => 'Consultant Démo',
        'date_creation' => '2025-12-10',
        'statut' => 'Généré',
        'periode' => '01/01/2025 au 01/12/2025',
        'resume' => "Bilan positif. Le rendement net est supérieur de 12% à l'année précédente grâce à l'optimisation de la fertilisation. Cependant, une surveillance est nécessaire dans la parcelle Ouest (risque de mildiou).",
        'donnees_cles' => [
            'Rendement Total' => '54.2 T',
            'Marge Nette' => '12 500 000 FCFA',
            'Croissance / An-1' => '+12%',
            'Statut Plantation' => 'En Récolte'
        ]
    ],
    // Rapport 2 (Fertilisation)
    2 => [
        'id' => 2,
        'agriculteur' => 'helsinky james (ID: 998)',
        'type' => 'Bilan de Fertilisation',
        'conseiller' => 'Consultant Démo',
        'date_creation' => '2025-11-28',
        'statut' => 'Archivé',
        'periode' => 'Période 2025/2026',
        'resume' => "Analyse des sols P1 et P3 terminée. Un déficit important en Potasse (K) est noté. Recommandation d'appliquer l'engrais 14-7-21 avant le début de la saison des pluies.",
        'donnees_cles' => [
            'Déficit Principal' => 'Potasse (K)',
            'pH Moyen' => '6.2',
            'Dernier Amendement' => '2025-10-15',
            'Recommandation' => 'Engrais 14-7-21'
        ]
    ]
    // ... ajoutez d'autres données simulées si nécessaire
];

if (!$rapport_id) {
    $message = "Erreur: Aucun identifiant de rapport fourni.";
} else {
    // 3. Récupération des données BDD
    try {
        // --- REMPLACEZ CE BLOC PAR VOTRE VRAIE LOGIQUE BDD ---
        /* $sql = "SELECT r.*, u.nom, u.prenom FROM rapports r 
                JOIN utilisateur u ON r.agriculteur_id = u.id 
                WHERE r.id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $rapport_id, PDO::PARAM_INT);
        $stmt->execute();
        $rapport_bdd = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($rapport_bdd) {
            // Mappez les données BDD dans $rapport_details
            $rapport_details = $rapport_bdd;
            $rapport_trouve = true;
        } else {
            $message = "Aucun rapport trouvé avec l'ID : " . htmlspecialchars($rapport_id);
        }
        */
        
        // --- UTILISATION DES DONNÉES SIMULÉES ---
        if (isset($simulated_data[$rapport_id])) {
            $rapport_details = $simulated_data[$rapport_id];
            $rapport_trouve = true;
            $message = "Affichage du rapport ID: " . htmlspecialchars($rapport_id) . " (Mode Démo)";
        } else {
            $message = "Aucun rapport trouvé (même en mode Démo) avec l'ID : " . htmlspecialchars($rapport_id);
        }

    } catch (PDOException $e) {
        error_log("Erreur BDD lors de la visualisation du rapport : " . $e->getMessage());
        $message = "Erreur de base de données. Impossible de charger le rapport.";
    }
}

// Fonction utilitaire pour le style du badge
function get_badge_class($statut) {
    $statut = strtolower($statut);
    if (strpos($statut, 'généré') !== false || strpos($statut, 'archivé') !== false) {
        return 'success';
    } elseif (strpos($statut, 'cours') !== false) {
        return 'warning';
    } else {
        return 'secondary';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail du Rapport #<?php echo htmlspecialchars($rapport_id); ?> - MonAgriCoach</title>
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
        
        /* --- SIDEBAR (Comme dans les autres fichiers) --- */
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
        .disconnect-item { margin-top: auto; padding: 25px; display: block; }
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
        .disconnect-item a i { color: var(--color-card-bg) !important; }
        .disconnect-item a:hover { background-color: #E37D00 !important; }

        /* --- CONTENU PRINCIPAL --- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px;
            flex-grow: 1;
            min-width: 0;
        }

        /* --- STYLES SPÉCIFIQUES À view_report.php --- */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #E5E7EB;
            padding-bottom: 15px;
        }
        .report-header h1 {
            font-family: 'Montserrat', sans-serif;
            color: var(--color-primary-emerald);
            font-size: 28px;
            margin: 0;
        }
        .report-info-box {
            background-color: var(--color-card-bg);
            padding: 25px;
            border-radius: var(--border-radius-lg);
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .info-item {
            padding: 15px;
            border: 1px solid #E5E7EB;
            border-radius: var(--border-radius-sm);
            background-color: #F9FAFB;
        }
        .info-item label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: #6B7280;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        .info-item p {
            font-size: 16px;
            font-weight: 700;
            color: var(--color-text-dark);
            margin: 0;
        }
        .info-item.resume p {
            font-weight: 400;
            font-size: 15px;
            color: #4B5563;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: inline-block;
        }
        .badge-success { background-color: #D1FAE5; color: #065F46; } /* Vert */
        .badge-warning { background-color: #FEF3C7; color: #92400E; } /* Jaune/Orange */
        .badge-secondary { background-color: #E5E7EB; color: #4B5563; } /* Gris */
        
        .btn-return {
            background-color: #4B5563;
            color: white;
            padding: 10px 15px;
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .btn-return:hover { background-color: #374151; }
        
        /* Bouton Télécharger PDF */
        .btn-download-pdf {
            background-color: var(--color-secondary-gold);
            color: white;
            padding: 10px 15px;
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.2s;
            margin-left: 15px;
        }
        .btn-download-pdf:hover {
            background-color: #E37D00;
        }
    </style>
</head>

<body>
    <nav class="sidebar">
        <a href="index.php" class="logo">
             <i class="fas fa-microchip"></i> MonAgriCoach
        </a>

        <ul>
            <li><a href="advisor_dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a></li>
            <li><a href="outiis_analyse.php"><i class="fas fa-flask"></i> Outils d'analyse</a></li>
            <li><a href="rapport.php"><i class="fas fa-file-invoice"></i> **Rapports**</a></li>
            <li><a href="liste_message_cons.php"><i class="fas fa-comments"></i> Mes messages</a></li>
            <li><a href="liste_agriculteur_cons.php"><i class="fas fa-user-friends"></i> Agriculteurs</a></li>
             <li><a href="modifier_profile.php" class="active"><i class="fas fa-user-edit"></i> Modifier mon compte</a></li>
         

            <li class="disconnect-item">
                <a href="deconnexion.php">
                    <i class="fas fa-sign-out-alt"></i> DÉCONNEXION
                </a>
            </li>
        </ul>
    </nav>


    <div class="main-content">
        
        <div class="report-header">
            <h1>Rapport ID #<?php echo htmlspecialchars($rapport_id); ?></h1>
            <div class="actions">
                
                <?php if ($rapport_trouve): ?>
                    <a href="export_report_detail.php?id=<?php echo htmlspecialchars($rapport_id); ?>" class="btn-download-pdf" target="_blank">
                        <i class="fas fa-download"></i> Télécharger PDF
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($rapport_trouve): ?>
            <div class="report-info-box">
                
                <h2><?php echo htmlspecialchars($rapport_details['type']); ?></h2>

                <div class="info-grid">
                    <div class="info-item">
                        <label>Agriculteur</label>
                        <p><?php echo htmlspecialchars($rapport_details['agriculteur']); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Conseiller</label>
                        <p><?php echo htmlspecialchars($rapport_details['conseiller']); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Période</label>
                        <p><?php echo htmlspecialchars($rapport_details['periode']); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Statut</label>
                        <p>
                            <span class="badge badge-<?php echo get_badge_class($rapport_details['statut']); ?>">
                                <?php echo htmlspecialchars($rapport_details['statut']); ?>
                            </span>
                        </p>
                    </div>
                </div>
                
                <hr style="margin: 25px 0;">

                <div class="info-item resume">
                    <label>Résumé / Constat Principal</label>
                    <p><?php echo nl2br(htmlspecialchars($rapport_details['resume'])); ?></p>
                </div>
                
                <hr style="margin: 25px 0;">

                <h3>Données Clés et Indicateurs</h3>
                <?php if (!empty($rapport_details['donnees_cles'])): ?>
                    <div class="info-grid">
                        <?php foreach ($rapport_details['donnees_cles'] as $label => $value): ?>
                            <div class="info-item">
                                <label><?php echo htmlspecialchars($label); ?></label>
                                <p><?php echo htmlspecialchars($value); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #6B7280;">Aucun indicateur clé détaillé disponible pour ce rapport.</p>
                <?php endif; ?>

                </div>
        <?php else: ?>
            <div class="report-info-box" style="background-color: #FEE2E2; border: 1px solid #EF4444; color: #EF4444;">
                <p style="font-weight: 700; margin: 0;"><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
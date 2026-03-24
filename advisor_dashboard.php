<?php
// Fichier : advisor_dashboard.php
session_start();
require_once 'db.php';

// --- Bloc de Vérification de Connexion ---
if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    header("Location: connexion.php");
    exit();
}

$message = '';
$user_id = $_SESSION['user_id'];
$current_time = date('H:i');
$user_email = htmlspecialchars($_SESSION['user_email'] ?? 'Conseiller Agricole');

// --- Initialisation des variables ---
$total_agriculteurs = 0;
$total_messages = 0;
$total_recom_attente = 0;
$rendement_nets_actuels = 0;
$rendement_nets_an_dernier = 0;
$pourcentage_rendement_nets = 0;

$labels_chart = ['Agriculteurs Actifs', 'Nouveaux Inscrits', 'Agriculteurs Inactifs'];
$data_chart = [75, 15, 10]; 

try {
    $sql_agriculteurs ="SELECT COUNT(*) AS nom FROM utilisateur WHERE type_utilisateur = 'agriculteur'";
    $stmt_agriculteurs = $pdo->query($sql_agriculteurs);
    $total_agriculteurs = $stmt_agriculteurs->fetchColumn() ?? 0;

    $sql_messages ="SELECT COUNT(*) FROM assistance_conseiller WHERE id_conseiller = ?";
    $stmt_messages = $pdo->prepare($sql_messages);
    $stmt_messages->execute([$user_id]);
    $total_messages = $stmt_messages->fetchColumn() ?? 0;

    $sql_recom ="SELECT COUNT(*) FROM recommandation WHERE statut_recom='en_attente' AND id_utilisateur_recom = ?";
    $stmt_recom = $pdo->prepare($sql_recom);
    $stmt_recom->execute([$user_id]);
    $total_recom_attente = $stmt_recom->fetchColumn() ?? 0;

    $annee_actuelle = date('Y');
    $annee_derniere = date('Y') - 1;

    $stmt_net_actuel = $pdo->prepare("SELECT SUM(quantite - cout) FROM observation WHERE YEAR(date_re) = ?");
    $stmt_net_actuel->execute([$annee_actuelle]);
    $rendement_nets_actuels = $stmt_net_actuel->fetchColumn() ?? 0;

    $stmt_net_dernier = $pdo->prepare("SELECT SUM(quantite - cout) FROM observation WHERE YEAR(date_re) = ?");
    $stmt_net_dernier->execute([$annee_derniere]);
    $rendement_nets_an_dernier = $stmt_net_dernier->fetchColumn() ?? 0;

    if ($rendement_nets_an_dernier > 0) {
        $pourcentage_rendement_nets = (($rendement_nets_actuels - $rendement_nets_an_dernier) / $rendement_nets_an_dernier) * 100;
    } else {
        $pourcentage_rendement_nets = ($rendement_nets_actuels > 0) ? 100 : 0;
    }
} catch (PDOException $e) {
    error_log("Erreur de BDD : " .$e->getMessage());
    $message = "Une erreur technique est survenue.";
}

$total_agriculteurs_format = number_format($total_agriculteurs, 0, ',', ' ');
$total_messages_format = number_format($total_messages, 0, ',', ' ');
$total_recom_attente_format = number_format($total_recom_attente, 0, ',', ' ');
$rendement_nets_actuels_format = number_format($rendement_nets_actuels, 0, ',', ' ');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Conseiller Agricole - MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* --- PALETTE & GÉNÉRAL (Vos styles originaux) --- */
        :root {
            --color-primary-emerald: #0ab9b1ff;
            --color-primary-dark: #008080;
            --color-secondary-gold: #fd9f07ff;
            --color-secondary-beige-light: #f5f8f8ff;
            --color-heading: #1F2937;
            --color-accent-danger: #df1313ff;
            --color-light-bg: #F8F9FA;
            --color-card-bg: #FFFFFF;
            --color-text-dark: #1F2937;
            --color-text-medium: #4B5563;
            --color-text-light: #9CA3AF;
            --color-success: #34A853;
            --sidebar-width: 260px;
            --border-radius-lg: 15px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-color: var(--color-light-bg);
            display: flex;
            min-height: 100vh;
        }

        /* --- BARRE LATÉRALE --- */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--color-card-bg);
            padding: 25px 0;
            height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            z-index: 1000;
        }

        .logo {
            font-family: 'Montserrat', sans-serif;
            color: var(--color-primary-emerald);
            font-size: 20px;
            font-weight: 800;
            text-align: center;
            text-decoration: none;
            padding: 0 20px 20px 20px;
        }

        /* --- NOUVEAU : STYLE PHOTO PROFIL --- */
        .profile-box {
            text-align: center;
            padding: 15px 0;
            border-bottom: 1px solid #f1f1f1;
            margin-bottom: 15px;
        }
        .profile-box img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid var(--color-primary-emerald);
            object-fit: cover;
            margin-bottom: 10px;
        }
        .profile-box .advisor-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--color-text-dark);
            display: block;
        }

        .sidebar ul { list-style: none; padding: 0; flex-grow: 1; }
        .sidebar li a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: var(--color-text-dark);
            text-decoration: none;
            font-size: 15px;
            transition: 0.2s;
        }
        .sidebar li a:hover { background-color: var(--color-secondary-beige-light); color: var(--color-primary-dark); }
        .sidebar li a[href="advisor_dashboard.php"] {
            background-color: rgba(6, 189, 189, 0.1);
            color: var(--color-primary-emerald);
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald);
        }
        .sidebar li a i { margin-right: 15px; font-size: 18px; width: 25px; text-align: center; }

        .disconnect-item { padding: 25px; }
        .disconnect-item a {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            background-color: var(--color-secondary-gold);
            color: var(--color-card-bg);
            padding: 12px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
        }

        /* --- CONTENU & CARTES (Restauration de votre style) --- */
        .main-content { margin-left: var(--sidebar-width); padding: 40px; flex-grow: 1; }

        .welcome-section {
            background: linear-gradient(135deg, var(--color-primary-emerald) 0%, #4cddd7 100%);
            padding: 35px 50px;
            border-radius: var(--border-radius-lg);
            color: white;
            margin-bottom: 40px;
        }
        .welcome-section h2 { font-size: 38px; margin: 0; font-family: 'Montserrat'; }

        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .card-link { text-decoration: none; color: inherit; display: flex; }
        .card {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 25px;
            flex-grow: 1;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            border-left: 4px solid transparent;
            display: flex;
            flex-direction: column;
            transition: 0.3s;
        }
        .card:hover { transform: translateY(-5px); }
        .card:nth-child(1) { border-left-color: var(--color-primary-emerald); }
        .card:nth-child(2) { border-left-color: var(--color-secondary-gold); }
        .card:nth-child(3) { border-left-color: var(--color-accent-danger); }
        .card:nth-child(4) { border-left-color: var(--color-primary-dark); }

        .card h4 { font-size: 13px; color: var(--color-text-light); text-transform: uppercase; margin: 0 0 15px 0; }
        .metric-value { display: flex; justify-content: space-between; align-items: flex-end; }
        .metric-value p { font-family: 'Montserrat'; font-size: 28px; font-weight: 900; margin: 0; color: var(--color-primary-dark); }
        .metric-value i { font-size: 40px; opacity: 0.15; color: var(--color-primary-emerald); }

        .card-footer { margin-top: auto; padding-top: 10px; border-top: 1px solid #F3F4F6; font-size: 12px; display: flex; justify-content: space-between; align-items: center; }
        .trend-up { color: var(--color-success); font-weight: 600; }
        .trend-down { color: var(--color-accent-danger); font-weight: 600; }

        /* --- LAYOUT GRAPHIQUE/TABLEAU --- */
        .dashboard-layout { display: flex; gap: 25px; }
        .chart-container { flex: 1; background: white; padding: 30px; border-radius: var(--border-radius-lg); border: 1px solid #E5E7EB; }
        .table-container { flex: 2; background: white; padding: 30px; border-radius: var(--border-radius-lg); border: 1px solid #E5E7EB; }
        .chart-canvas-wrapper { height: 300px; position: relative; }

        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { text-align: left; padding: 15px; background: var(--color-secondary-beige-light); color: var(--color-primary-dark); border-bottom: 2px solid var(--color-primary-emerald); }
        .data-table td { padding: 15px; border-bottom: 1px solid #E5E7EB; font-size: 14px; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status-new { background: #E6F3FF; color: #1E90FF; }
    </style>
</head>

<body>
    <nav class="sidebar">
        <a href="index.php" class="logo"><i class="fas fa-leaf"></i> MonAgriCoach</a>

        <div class="profile-box">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_email); ?>&background=0ab9b1&color=fff" alt="Profil">
            <span class="advisor-name"><?php echo $user_email; ?></span>
        </div>

        <ul>
            <li><a href="advisor_dashboard.php"><i class="fas fa-chart-line"></i> <strong>Tableau de bord</strong></a></li>
            <li><a href="outiis_analyse.php"><i class="fas fa-flask"></i> Outils d'analyse</a></li>
            <li><a href="rapport.php"><i class="fas fa-file-invoice"></i> Rapports</a></li>
            <li><a href="liste_message_cons.php"><i class="fas fa-comments"></i> Mes messages</a></li>
            <li><a href="liste_agriculteur_cons.php"><i class="fas fa-user-friends"></i> Agriculteurs</a></li>
              <li><a href="modifier_profile.php" class="active"><i class="fas fa-user-edit"></i> Modifier mon compte</a></li>
            
            <li class="disconnect-item">
                <a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i> DÉCONNEXION</a>
            </li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="welcome-section">
            <h2>Tableau de bord Conseiller</h2> 
            <p>Bonjour, <strong><?php echo $user_email; ?></strong> ! Aperçu de vos indicateurs clés.</p>
            <p style="font-size: 14px; opacity: 0.9;">Heure Locale : <strong><?php echo $current_time; ?></strong></p>
        </div>

        <div class="cards">
            <a href="liste_agriculteur_cons.php" class="card-link">
                <div class="card">
                    <h4>Agriculteurs suivis</h4>
                    <div class="metric-value">
                        <p><?php echo $total_agriculteurs_format; ?></p>
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-footer">
                        <span>Voir la liste complète</span> <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </a>

            <a href="liste_message_cons.php" class="card-link">
                <div class="card">
                    <h4>Messages Récents</h4>
                    <div class="metric-value">
                        <p><?php echo $total_messages_format; ?></p>
                        <i class="fas fa-inbox"></i>
                    </div>
                    <div class="card-footer">
                        <span>Aller à la messagerie</span> <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </a>

            <a href="liste_recom.php" class="card-link">
                <div class="card">
                    <h4>Recommandations en attente</h4>
                    <div class="metric-value">
                        <p><?php echo $total_recom_attente_format; ?></p>
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="card-footer">
                        <span class="<?php echo ($total_recom_attente > 0) ? 'trend-down' : ''; ?>">
                            <?php echo ($total_recom_attente > 0) ? 'Action requise' : 'Aucune en attente'; ?>
                        </span>
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </a>

            <a href="rapport_personnalise.php" class="card-link">
                <div class="card">
                    <h4>Rendement Net Global (FCFA)</h4>
                    <div class="metric-value">
                        <p><?php echo $rendement_nets_actuels_format; ?></p>
                        <i class="fas fa-chart-area"></i>
                    </div>
                    <div class="card-footer">
                        <?php
                            $trend_class = ($pourcentage_rendement_nets > 0) ? 'trend-up' : (($pourcentage_rendement_nets < 0) ? 'trend-down' : '');
                            $trend_icon = ($pourcentage_rendement_nets > 0) ? 'up' : (($pourcentage_rendement_nets < 0) ? 'down' : 'right');
                        ?>
                        <span class="<?php echo $trend_class; ?>">
                            <i class="fas fa-arrow-<?php echo $trend_icon; ?>"></i> <?php echo number_format(abs($pourcentage_rendement_nets), 1); ?>%
                        </span>
                        <span>Vs Année Précédente</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="dashboard-layout">
            <div class="chart-container">
                <h3><i class="fas fa-chart-pie"></i> Répartition des Agriculteurs</h3>
                <div class="chart-canvas-wrapper">
                    <canvas id="advisorPieChart"></canvas>
                </div>
            </div>

            <div class="table-container">
                <h3><i class="fas fa-list-alt"></i> Dernières Interactions</h3>
                <table class="data-table">
                    <thead>
                        <tr><th>Type</th><th>Agriculteur</th><th>Date</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Message</td><td>K. Talla</td><td>15/12/2025</td><td><span class="status-badge status-new">Nouveau</span></td></tr>
                        <tr><td>Recommandation</td><td>A. Ngomo</td><td>12/12/2025</td><td><span class="status-badge" style="background:#FFF3E0;color:#E37D00;">En Attente</span></td></tr>
                        <tr><td>Analyse Sol</td><td>J. Douala</td><td>10/12/2025</td><td><span class="status-badge" style="background:#E0F0E0;color:#34A853;">Complété</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Restauration de votre script Chart.js original
        const ctx = document.getElementById('advisorPieChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($labels_chart); ?>,
                datasets: [{
                    data: <?php echo json_encode($data_chart); ?>,
                    backgroundColor: ['#0ab9b1ff', '#fd9f07ff', '#A0AEC0'],
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { font: { family: 'Poppins' }, padding: 20 } } }
            }
        });
    </script>
</body>
</html>
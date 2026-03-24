<?php
session_start();
require_once 'db.php';

// Vérification de sécurité
if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) { 
    header("Location: connexion_admin.php"); 
    exit(); 
} 

// --- Logique PHP pour le graphique ---
try {
    $sql_stats = "SELECT DATE_FORMAT(date_inscription, '%M') as mois, COUNT(id) as nb 
                  FROM utilisateur 
                  GROUP BY MONTH(date_inscription) 
                  ORDER BY date_inscription ASC LIMIT 6";
    $stmt_stats = $pdo->query($sql_stats);
    $stats = $stmt_stats->fetchAll(PDO::FETCH_ASSOC);
    
    $labels_evolution = [];
    $data_evolution = [];
    
    foreach($stats as $row) {
        $labels_evolution[] = $row['mois'];
        $data_evolution[] = $row['nb'];
    }
    
    if(empty($data_evolution)) {
        $labels_evolution = ['Juil', 'Août', 'Sept', 'Oct', 'Nov', 'Déc'];
        $data_evolution = [12, 19, 45, 35, 70, 95];
    }
} catch (Exception $e) {
    error_log($e->getMessage());
}
?>

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Admin | MonAgriCoach</title> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --color-primary-emerald: #06bdbd; 
            --color-primary-dark: #008080; 
            --color-secondary-gold: #FF8C00; 
            --color-light-bg: #F0F4F8;
            --color-card-bg: #FFFFFF;
            --color-text-dark: #1F2937;
            --color-text-light: #9CA3AF;
            --sidebar-width: 260px;
        }

        body { 
            font-family: 'Poppins', sans-serif; 
            margin: 0; 
            background-color: var(--color-light-bg); 
            display: flex;
            min-height: 100vh;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--color-card-bg);
            height: 100vh;
            position: fixed;
            box-shadow: 4px 0 15px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            padding-top: 25px;
            z-index: 1000;
        }

        .sidebar .logo {
            font-family: 'Montserrat';
            color: var(--color-primary-emerald);
            font-size: 22px;
            font-weight: 800;
            text-align: center;
            text-decoration: none;
            margin-bottom: 40px;
            display: block;
        }

        .sidebar ul { list-style: none; padding: 0; margin: 0; flex-grow: 1; }

        .sidebar li a {
            display: flex;
            align-items: center;
            padding: 14px 25px;
            color: var(--color-text-dark);
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s ease;
        }

        .sidebar li a i { margin-right: 15px; width: 25px; text-align: center; font-size: 18px; }

        .sidebar li a:hover, .sidebar li a.active {
            background: rgba(6, 189, 189, 0.08);
            color: var(--color-primary-emerald);
            border-left: 5px solid var(--color-primary-emerald);
        }

        .sidebar-footer {
            padding: 20px;
            padding-bottom: 80px; /* Bouton bien remonté */
            margin-top: auto; 
        }

        .btn-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--color-secondary-gold);
            color: white !important;
            padding: 12px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(255, 140, 0, 0.3);
            transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .btn-logout:hover { transform: scale(1.05); filter: brightness(1.1); }

        /* --- CONTENU PRINCIPAL --- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px;
            width: calc(100% - var(--sidebar-width));
        }

        .welcome-section {
            background: linear-gradient(135deg, var(--color-primary-emerald) 0%, #00d2d2 100%);
            padding: 35px 45px;
            border-radius: 20px;
            color: white;
            margin-bottom: 35px;
            box-shadow: 0 10px 30px rgba(6, 189, 189, 0.25);
            position: relative;
            overflow: hidden;
        }

        /* --- CARDS AVEC EFFET SURVOL --- */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .card {
            background: var(--color-card-bg);
            padding: 25px;
            border-radius: 18px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            border-left: 6px solid var(--color-primary-emerald);
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .card:hover { 
            transform: translateY(-10px); 
            box-shadow: 0 15px 30px rgba(0,0,0,0.08);
            background: #fff;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background: rgba(6, 189, 189, 0.1);
            color: var(--color-primary-emerald);
        }

        .card h4 { color: var(--color-text-light); font-size: 14px; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; }
        .card p { font-size: 30px; font-weight: 800; margin: 10px 0; color: var(--color-text-dark); }
        
        .metric-change { font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 5px; }
        .positive { color: #10B981; }
        .negative { color: #EF4444; }

        /* --- GRAPHIQUE --- */
        .chart-container {
            background: white;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.04);
        }
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .chart-header h3 { margin: 0; font-size: 20px; color: var(--color-text-dark); font-weight: 700; }
    </style>
</head> 
<body> 

    <nav class="sidebar">
        <a href="index.php" class="logo"><i class="fas fa-leaf"></i> MonAgriCoach</a>
        <ul>
            <li><a href="admin_dashboard.php" class="active"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="liste_agriculteur.php"><i class="fas fa-tractor"></i> Agriculteurs</a></li>
            <li><a href="liste_conseiller.php"><i class="fas fa-user-tie"></i> Conseillers</a></li>
            <li><a href="tarifs.php"><i class="fas fa-credit-card"></i> Tarifs & Offres</a></li>
            <li><a href="contact.php"><i class="fas fa-paper-plane"></i> Messages</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="deconnexion_admin.php" class="btn-logout">
                <i class="fas fa-power-off" style="margin-right: 12px;"></i> DÉCONNEXION
            </a>
        </div>
    </nav>

    <div class="main-content">
        <div class="welcome-section">
            <h2 style="margin:0; font-size: 28px;">Tableau de Bord Admin</h2>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Gérez vos opérations et suivez la croissance de <strong>MonAgriCoach</strong>.</p>
        </div>

        <div class="cards">
            <div class="card">
                <div class="card-header">
                    <h4>Cultures actives</h4>
                    <div class="card-icon"><i class="fas fa-seedling"></i></div>
                </div>
                <p>17</p>
                <span class="metric-change positive"><i class="fas fa-arrow-trend-up"></i> +12% ce mois</span>
            </div>

            <div class="card" style="border-left-color: var(--color-secondary-gold);">
                <div class="card-header">
                    <h4>Plantations</h4>
                    <div class="card-icon" style="background:rgba(255, 140, 0, 0.1); color: var(--color-secondary-gold);"><i class="fas fa-tree"></i></div>
                </div>
                <p>480</p>
                <span class="metric-change positive"><i class="fas fa-plus"></i> 26.9% vs hier</span>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Rendement Total</h4>
                    <div class="card-icon"><i class="fas fa-weight-hanging"></i></div>
                </div>
                <p>21,302 t</p>
                <span class="metric-change negative"><i class="fas fa-arrow-trend-down"></i> -2.4%</span>
            </div>

            <div class="card" style="border-left-color: #8B5CF6;">
                <div class="card-header">
                    <h4>Revenus Nets</h4>
                    <div class="card-icon" style="background:rgba(139, 92, 246, 0.1); color: #8B5CF6;"><i class="fas fa-wallet"></i></div>
                </div>
                <p>46,716 <small style="font-size: 14px;">FCFA</small></p>
                <span class="metric-change positive"><i class="fas fa-check-circle"></i> Objectif atteint</span>
            </div>
        </div>

        <div class="chart-container">
            <div class="chart-header">
                <h3><i class="fas fa-chart-line" style="color: var(--color-primary-emerald); margin-right: 10px;"></i> Croissance des Inscriptions</h3>
            </div>
            <canvas id="evolutionChart" height="110"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('evolutionChart').getContext('2d');
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(6, 189, 189, 0.45)');
        gradient.addColorStop(1, 'rgba(6, 189, 189, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels_evolution); ?>,
                datasets: [{
                    label: 'Nouveaux Utilisateurs',
                    data: <?php echo json_encode($data_evolution); ?>,
                    borderColor: '#06bdbd',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    borderWidth: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#06bdbd',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 9
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: { usePointStyle: true, font: { family: 'Poppins', weight: '600' } }
                    },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        padding: 15,
                        cornerRadius: 10,
                        titleFont: { size: 14 }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                        ticks: { font: { family: 'Poppins' } }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { font: { family: 'Poppins' } }
                    }
                }
            }
        });
    </script>
</body> 
</html>
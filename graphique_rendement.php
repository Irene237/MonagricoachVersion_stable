<?php
session_start();
require_once 'db.php'; // Assurez-vous que ce fichier existe et contient la connexion PDO

// Vérifie si l'utilisateur est connecté. Sinon, le redirige vers la page de connexion.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id_agriculteur = $_SESSION['user_id'];
$total_rendement = '0,00';

try{
    // Calcul du total du rendement (en Tonnes)
    $sql_total ="SELECT SUM(r.rendement) AS rendement 
                 FROM observation AS r 
                 JOIN plantation AS p ON r.id_plantation = p.id 
                 JOIN utilisateur AS u ON p.id_agriculteur = u.id 
                 WHERE u.type_utilisateur ='agriculteur' 
                 AND r.id_agriculteur = :id_agriculteur";
                 
    $stmt_total =$pdo->prepare($sql_total);
    $stmt_total->bindParam(':id_agriculteur', $id_agriculteur, PDO::PARAM_INT);
    $stmt_total->execute();
    $total_result =$stmt_total->fetch(PDO::FETCH_ASSOC);

    $total = $total_result['rendement'];
    if($total !== null){
        // Formatage pour l'affichage (ex: 1 234,56)
        $total_rendement = number_format($total, 2, ',', ' ');
    }
} catch (PDOException $e) {
    error_log("Erreur de récupération du total: " . $e->getMessage());
    // Gérer l'erreur si nécessaire
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphique Rendement | MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
        /* --- PALETTE HARMONISÉE --- */
        :root {
            --color-primary-emerald: #06bdbdff; 
            --color-primary-dark: #0cb4b4ff; 
            --color-secondary-gold: #FF8C00; 
            --color-secondary-gold-hover: #E37D00;
            --color-accent-danger: #ef4444; 
            --color-accent-success: #10B981; 
            --color-heading: #111827; 
            --color-card-bg: #FFFFFF; 
            --color-light-bg: #F5F8F5; 
            --color-text-dark: #374151;
            --color-text-medium: #4B5563; 
            --color-border: #E5E7EB;
            --color-border-light: #F3F4F6; 
            --color-disconnect-bg: var(--color-secondary-gold);
            --font-main: 'Poppins', sans-serif;
            --font-heading: 'Montserrat', sans-serif;
            --sidebar-width: 260px; 
        }

        body { font-family: var(--font-main); margin: 0; padding: 0; background-color: var(--color-light-bg); display: flex; min-height: 100vh; overflow-x: hidden; }
        
        /* --- BARRE LATÉRALE (Style Classique) --- */
        .sidebar { width: var(--sidebar-width); background-color: var(--color-card-bg); padding: 25px 0; height: 100vh; position: fixed; top: 0; left: 0; box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; z-index: 1000; }
        .sidebar .logo { font-family: var(--font-heading); color: var(--color-primary-emerald); font-size: 20px; font-weight: 800; text-align: center; padding: 0 20px 40px 20px; text-decoration : none; }
        .sidebar .logo i { color: var(--color-primary-emerald); font-size: 28px; margin-right: 5px; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; flex-grow: 1; display: flex; flex-direction: column; }
        .sidebar li a { display: flex; align-items: center; padding: 12px 25px; color: var(--color-text-dark); text-decoration: none; font-size: 15px; font-weight: 500; transition: all 0.2s ease-in-out; border-left: 0px solid transparent; }
        .sidebar li a i { margin-right: 15px; font-size: 18px; color: var(--color-text-medium); width: 25px; text-align: center; font-style: normal; }
        .sidebar li a:hover { background-color: #f0fafa; color: var(--color-primary-dark); }
        
        /* Style actif pour la page Rendement/Graphique */
        .sidebar li a[href="liste_re.php"],
        .sidebar li a[href*="rendement.php"] { 
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-emerald); 
            font-weight: 600; 
            border-left: 5px solid var(--color-primary-emerald); 
        }
        .sidebar li a[href="liste_re.php"] i { color: var(--color-primary-emerald); }

        /* --- BOUTON DE DÉCONNEXION --- */
        .btn_lien { margin-top: auto; padding: 25px; }
        .sidebar .btn_lien button { border: none; padding: 0; background: none; width: 100%; }
        .sidebar .btn_lien button a { 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            gap: 10px; 
            background-color: var(--color-disconnect-bg); 
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
        .sidebar .btn_lien button a:hover { background-color: var(--color-secondary-gold-hover); box-shadow: 0 6px 20px rgba(255, 140, 0, 0.6); } 
        
        .main-content { margin-left: var(--sidebar-width); padding: 50px 40px; flex-grow: 1; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 1px solid var(--color-border); }
        
        /* --- BLOC DES BOUTONS D'ACTION --- */
        .headers { display: flex; gap: 15px; } 

        .action-link { 
            padding: 12px 25px; 
            border-radius: 8px; 
            font-weight: 600; 
            font-size: 16px; 
            text-decoration: none; 
            transition: background-color 0.2s ease, box-shadow 0.2s; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            background-color: var(--color-primary-emerald); 
            color: var(--color-card-bg); 
            box-shadow: 0 4px 12px rgba(6, 189, 189, 0.4); 
            border: none;
            cursor: pointer;
        }

        .action-link:hover { background-color: var(--color-primary-dark); box-shadow: 0 6px 15px rgba(6, 189, 189, 0.6); }

        .content-container { background: var(--color-card-bg); padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); }

        /* Style Total Rendement Harmonisé */
        .total-container { 
            display: flex; 
            justify-content: flex-end; 
            align-items: center; 
            margin-bottom: 30px; 
            padding: 15px 25px; 
            background-color: #e6f7f7; 
            border-left: 5px solid var(--color-primary-emerald); 
            border-radius: 8px; 
            font-size: 18px; 
            font-weight: 700; 
            color: var(--color-primary-dark); 
            box-shadow: 0 2px 8px rgba(6, 189, 189, 0.1);
        }
        .total-container .total-label { margin-right: 15px; font-weight: 500; color: var(--color-text-medium); }
        .total-container .total-value { font-family: var(--font-heading); font-size: 24px; color: var(--color-heading); }

        .search-form { display: flex; gap: 10px; margin-bottom: 30px; }
        .search-form input[type="text"] { padding: 10px 15px; border: 1px solid var(--color-border); border-radius: 8px; flex-grow: 1; font-size: 15px; transition: border-color 0.2s; }
        .search-form input[type="text"]:focus { border-color: var(--color-primary-emerald); outline: none; }
        .search-form button { padding: 10px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 15px; display: flex; align-items: center; gap: 5px; transition: background-color 0.2s; }
        .search-form button[type="submit"] { background-color: var(--color-primary-emerald); color: var(--color-card-bg); }
        .search-form button[type="submit"]:hover { background-color: var(--color-primary-dark); }
        .search-form button[type="button"] { background-color: #6B7280; color: var(--color-card-bg); } 
        .search-form button[type="button"]:hover { background-color: #4B5563; } 
        
        .chart-container { 
            position: relative; 
            height: 40vh; 
            width: 100%;
            margin-top: 20px;
        }

    </style>
</head>

<body> 
    <nav class="sidebar">
        <a href="index.php" class="logo"><i class="fas fa-leaf"></i> MonAgriCoach</a>
        <ul>
             <li><a href="farmer_dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a></li>
            <li> <a href="liste_parcelle.php"><i class="fas fa-map-marker-alt"></i> Parcelles</a></li> 
            <li> <a href="liste_plantation.php"><i class="fas fa-seedling"></i> Plantations</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i> Cultures</a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> Engrais</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i> Messagerie</a></li>
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i> Verser l'engrais</a></li>
            <li> <a href="liste_st_intrant.php"><i class="fas fa-warehouse"></i> Stock engrais</a></li>
            <li><a href="liste_re.php"><i class="fas fa-chart-bar"></i> **Rendement**</a></li>
            <li><a href="liste_taches.php"><i class="fas fa-tasks"></i> Tâches</a></li> 
        </ul>
        
        <div class="btn_lien">
            <button>
                <a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </button>
        </div>
    </nav>

    <div class="main-content">
        <div class="header">
            <h1>Analyse Graphique des Rendements</h1>
            <div class="headers" >
                <button id="exportPDF" class="action-link" style="background-color: var(--color-secondary-gold);"> 
                    <i class="fas fa-file-pdf"></i> Exporter PDF
                </button>
                <a href="liste_re.php" class="action-link"> 
                    <i class="fas fa-table"></i> Tableau
                </a>
                <a href="rendement.php" class="action-link"> 
                    <i class="fas fa-plus-circle"></i> Ajouter
                </a>
            </div>
        </div>

        <div class="content-container">
            <h2>Vue d'ensemble par Culture et Coûts</h2>
            
            <div class="total-container">
                <span class="total-label">Total général du Rendement :</span>
                <span class="total-value"><?= $total_rendement; ?> T</span>
            </div>

            <form action="recherche_re.php" method="GET" class="search-form">
                <input type="text" name="recherche" placeholder="Rechercher par plantation...">
                <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
                <button type="button" onclick="window.location.href='graphique_rendement.php'"><i class="fas fa-redo"></i> Réinitialiser</button>
            </form>
            
            <div class="chart-container">
                <canvas id="myChart"></canvas>
            </div> 
        </div> 
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetch('getrendement.php')
                .then(response =>{
                    if ( !response.ok){
                        throw new Error('Erreur réseau lors de la récupération des données.');
                    }
                    return response.json();
                })
                .then(data => {
                    const ctx = document.getElementById('myChart').getContext('2d');
                    const myChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: data.datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction : {
                                mode: 'index',
                                intersect: false,
                            },
                            scales:{
                                x:{
                                    title:{ display: true, text: 'Cultures' }
                                },
                                y1: {
                                    type: 'linear', display: true, position: 'left',
                                    title: { display: true, text: 'Quantité (Tonnes)' },
                                },
                                y2: {
                                    type: 'linear', display: true, position: 'right',
                                    title: { display: true, text: 'Coûts (FCFA)' },
                                    grid:{ drawOnChartArea: false },
                                }
                            }
                        }
                    });

                    // --- SCRIPT EXPORT PDF DESIGNÉ ---
                    document.getElementById('exportPDF').addEventListener('click', function() {
                        const { jsPDF } = window.jspdf;
                        const doc = new jsPDF('l', 'mm', 'a4'); // Format Paysage
                        const pageWidth = doc.internal.pageSize.getWidth();
                        
                        // 1. Décoration : Entête stylisée (Fond Émeraude)
                        doc.setFillColor(6, 189, 189);
                        doc.rect(0, 0, pageWidth, 25, 'F');
                        
                        // 2. Titre du PDF
                        doc.setTextColor(255, 255, 255);
                        doc.setFontSize(20);
                        doc.setFont("helvetica", "bold");
                        doc.text("RAPPORT D'ANALYSE DES RENDEMENTS", 15, 17);
                        
                        // 3. Infos de contexte (Date et Nom)
                        doc.setTextColor(60, 60, 60);
                        doc.setFontSize(10);
                        doc.setFont("helvetica", "normal");
                        doc.text("Généré le : " + new Date().toLocaleString(), 15, 32);
                        doc.text("Application : MonAgriCoach", 15, 37);

                        // 4. Encadré Récapitulatif (Total)
                        doc.setDrawColor(6, 189, 189);
                        doc.setLineWidth(0.5);
                        doc.roundedRect(pageWidth - 95, 28, 80, 15, 3, 3, 'D');
                        doc.setTextColor(6, 189, 189);
                        doc.setFontSize(12);
                        doc.setFont("helvetica", "bold");
                        doc.text("TOTAL : <?= $total_rendement; ?> T", pageWidth - 90, 38);

                        // 5. Capture du Graphique (Fond blanc forcé)
                        const canvas = document.getElementById('myChart');
                        const context = canvas.getContext('2d');
                        context.save();
                        context.globalCompositeOperation = 'destination-over';
                        context.fillStyle = 'white';
                        context.fillRect(0, 0, canvas.width, canvas.height);
                        context.restore();

                        const chartImg = canvas.toDataURL('image/png', 1.0);
                        
                        // 6. Ajout de l'image du graphique au PDF
                        doc.addImage(chartImg, 'PNG', 15, 45, pageWidth - 30, 120);

                        // 7. Pied de page
                        doc.setFontSize(9);
                        doc.setTextColor(150);
                        doc.text("MonAgriCoach - Votre partenaire agricole de confiance.", pageWidth / 2, 195, { align: "center" });

                        // 8. Sauvegarde
                        doc.save('Rapport_Rendement_MonAgriCoach.pdf');
                    });

                })
                .catch(error => console.error('Erreur de chargement du graphique:' , error));
        });
    </script>
</body>
</html>
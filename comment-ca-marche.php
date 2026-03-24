<?php
    date_default_timezone_set('Africa/Douala');
    $date_heure_actuelles = date("d/m/y H:i:s");
    $nom_utilisateur = "Agriculteur"; // Exemple
    $ville_meteo = "Yaoundé, Cameroun";
    // ... tes autres variables PHP restent les mêmes ...
    $temperature_actuelles = "25°C";
    $condition_actuelles = "Ensoleillé";
    $prevision_24h = "Chaud et ensoleillé, max 28°C";
    $prevision_48h = "Quelques averses possibles, min 22°C, max 26°C";

    $nombre_total_parcelles = 7;
    $superficie_total_cultivee = "15.5 hectares";
    $parcelles_alertes_actives = 2;

    $nombre_culture_en_cours = 3;
    $statut_culture_mais = "En croissance";
    $statut_culture_ble = "Proche récolte";
    $statut_culture_soja = "Problème détecté (maladie)";
    $rendement_moyen_projete = "3.2 tonnes/hectare";

    $intrant_sous_seil_alerte = 1;
    $valeur_estime_stock_total = "500 000 XAF";

    $nombre_alerte_non_lue = 3;
    $detail_alerte_non_lues = array("Humidité basse parcelle A", "Ravageur détecté parcelle B", "Besoin en nutriment parcelle C");
    $nombre_recommandations_en_attente = 2;
    $detail_recommandation_en_attente = array("Planifier l'irrigation pour le maïs", "Appliquer engrais sur le blé");

    $dernier_evenements = array("Il y'a 2h : humidité ajoutée", "Hier : recommandation riz", "Il y'a 2j : stock NPK bas");
    
    $conseiller_agricoles = array(
        array("nom"=> "Dr. Marie Traoré", "spécialité"=> "Agronome", "photo"=> "https://ui-avatars.com/api/?name=Marie+Traore&background=00a693&color=fff", "contact_email"=> "marie@agri.com", "contact_tel"=> "+237 671234567")
    );

    // Données pour les graphiques (JSON)
    $rendements_graph_data = ['labels'=>['2022','2023','2024'], 'datasets'=>[['label'=>'Maïs','data'=>[2.5, 2.8, 3.0], 'borderColor'=>'#00a693', 'fill'=>false]]];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MonAgriCoach | Tableau de Bord</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-emerald: #00a693;
            --bg-light: #f8f9fa;
            --white: #ffffff;
            --dark: #1a1a1a;
            --danger: #e74c3c;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg-light); 
            margin: 0; 
            color: var(--dark);
        }

        header { background: var(--white); padding: 15px 5%; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .logo { color: var(--primary-emerald); font-weight: 800; font-size: 20px; text-decoration: none; }
        
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        
        /* --- Dashboard Grid --- */
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        
        .card { background: var(--white); padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .card h3 { margin-bottom: 15px; font-size: 18px; border-bottom: 2px solid var(--bg-light); padding-bottom: 10px; }
        
        .stat-item { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .alert-badge { background: var(--danger); color: white; padding: 2px 8px; border-radius: 5px; font-size: 12px; }
        
        /* --- Graphiques --- */
        .chart-container { position: relative; height: 250px; width: 100%; }

        /* --- Actions Rapides --- */
        .action-btns { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; }
        .btn-action { background: var(--white); border: 1px solid var(--primary-emerald); color: var(--primary-emerald); padding: 10px; border-radius: 8px; text-align: center; text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.3s; }
        .btn-action:hover { background: var(--primary-emerald); color: white; }

        .conseiller-info { display: flex; align-items: center; gap: 15px; background: #f0fdfa; padding: 15px; border-radius: 10px; margin-top: 10px; }
        .conseiller-info img { border-radius: 50%; border: 2px solid var(--primary-emerald); }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo"><i class="fas fa-leaf"></i> MonAgriCoach</a>
    <div>
        <small><?php echo $date_heure_actuelles; ?></small>
    </div>
</header>

<div class="container">
    <div style="margin-bottom: 30px;">
        <h1>Bonjour, <?php echo htmlspecialchars($nom_utilisateur); ?> 👋</h1>
        <p style="color: #666;">Voici l'état actuel de votre exploitation à <strong><?php echo $ville_meteo; ?></strong></p>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <h3><i class="fas fa-cloud-sun"></i> Météo Locale</h3>
            <div class="stat-item"><span>Actuel:</span> <strong><?php echo $temperature_actuelles; ?></strong></div>
            <div class="stat-item"><span>Ciel:</span> <strong><?php echo $condition_actuelles; ?></strong></div>
            <p style="font-size: 13px; color: #666; margin-top: 10px;"><?php echo $prevision_24h; ?></p>
        </div>

        <div class="card" style="border-left: 5px solid var(--danger);">
            <h3><i class="fas fa-bell"></i> Alertes (<?php echo $nombre_alerte_non_lue; ?>)</h3>
            <ul style="padding-left: 20px; font-size: 14px;">
                <?php foreach($detail_alerte_non_lues as $alerte): ?>
                    <li><?php echo htmlspecialchars($alerte); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <h3><i class="fas fa-seedling"></i> Vos Cultures</h3>
            <div class="stat-item"><span>Parcelles:</span> <strong><?php echo $nombre_total_parcelles; ?></strong></div>
            <div class="stat-item"><span>Superficie:</span> <strong><?php echo $superficie_total_cultivee; ?></strong></div>
            <hr>
            <div class="stat-item"><span>Maïs:</span> <small><?php echo $statut_culture_mais; ?></small></div>
            <div class="stat-item"><span>Blé:</span> <small><?php echo $statut_culture_ble; ?></small></div>
        </div>

        <div class="card">
            <h3><i class="fas fa-chart-line"></i> Tendances</h3>
            <div class="chart-container">
                <canvas id="rendementsChart"></canvas>
            </div>
        </div>
    </div>

    

    <div class="card" style="margin-top: 20px;">
        <h3><i class="fas fa-user-tie"></i> Mon Conseiller</h3>
        <?php foreach($conseiller_agricoles as $conseiller): ?>
        <div class="conseiller-info">
            <img src="<?php echo $conseiller['photo']; ?>" width="50" height="50">
            <div>
                <strong><?php echo $conseiller['nom']; ?></strong><br>
                <small><?php echo $conseiller['spécialité']; ?></small><br>
                <a href="mailto:<?php echo $conseiller['contact_email']; ?>" style="font-size: 12px; color: var(--primary-emerald);">Contacter</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    // Configuration simplifiée du graphique
    const ctx = document.getElementById('rendementsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: <?php echo json_encode($rendements_graph_data); ?>,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
</script>

</body>
</html>
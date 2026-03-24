<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarifs | MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #00a693;
            --secondary: #ffb800;
            --dark: #1a1a1a;
            --light-bg: #f8f9fa;
            --white: #ffffff;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--light-bg); 
            margin: 0; 
            color: var(--dark);
        }

        header { background: var(--white); padding: 15px 5%; border-bottom: 1px solid #eee; }
        nav { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        .logo { color: var(--primary); font-weight: 800; font-size: 22px; text-decoration: none; }

        .pricing-header {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(135deg, #00a693 0%, #007d6f 100%);
            color: white;
        }

        .pricing-header h1 { font-size: 36px; margin-bottom: 10px; }

        .container {
            max-width: 1100px;
            margin: -50px auto 50px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .pricing-card {
            background: var(--white);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: 0.3s;
            position: relative;
            border: 1px solid #eee;
        }

        .pricing-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }

        .pricing-card.popular {
            border: 2px solid var(--primary);
        }

        .badge {
            background: var(--primary);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            font-weight: 700;
        }

        .price { font-size: 42px; font-weight: 800; margin: 20px 0; }
        .price span { font-size: 16px; color: #666; font-weight: 400; }

        .features-list {
            list-style: none;
            padding: 0;
            margin: 30px 0;
            text-align: left;
        }

        .features-list li { margin-bottom: 15px; font-size: 15px; display: flex; align-items: center; gap: 10px; }
        .features-list i { color: var(--primary); }

        .btn-pricing {
            display: block;
            background: var(--primary);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            transition: 0.3s;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-pricing:hover { opacity: 0.9; }

        footer { background: var(--dark); color: #fff; padding: 40px 20px; text-align: center; margin-top: 50px; }
    </style>
</head>
<body>

<header>
    <nav>
        <a href="index.php" class="logo"><i class="fas fa-leaf"></i> MonAgriCoach</a>
        <div style="display:flex; gap:20px;">
            <a href="index.php" style="text-decoration:none; color:var(--dark); font-weight:600;">Accueil</a>
            <a href="fonctionnalite.php" style="text-decoration:none; color:var(--dark); font-weight:600;">Fonctionnalités</a>
        </div>
    </nav>
</header>

<div class="pricing-header">
    <h1>Des tarifs adaptés à vos besoins</h1>
    <p>Choisissez le plan qui correspond à la taille de votre exploitation.</p>
</div>

<div class="container">
    <div class="pricing-card">
        <h3>Basique</h3>
        <div class="price">0 XAF<span>/mois</span></div>
        <p>Pour les petits agriculteurs qui débutent.</p>
        <ul class="features-list">
            <li><i class="fas fa-check"></i> Jusqu'à 2 parcelles</li>
            <li><i class="fas fa-check"></i> Suivi météo basique</li>
            <li><i class="fas fa-check"></i> Carnet de bord manuel</li>
            <li style="color:#ccc"><i class="fas fa-times"></i> Recommandations experts</li>
            <li style="color:#ccc"><i class="fas fa-times"></i> Rapports PDF</li>
        </ul>
        <a href="inscription.php" class="btn-pricing btn-outline">Commencer gratuitement</a>
    </div>

    <div class="pricing-card popular">
        <div class="badge">LE PLUS POPULAIRE</div>
        <h3>Standard</h3>
        <div class="price">5 000 XAF<span>/mois</span></div>
        <p>Idéal pour une gestion professionnelle complète.</p>
        <ul class="features-list">
            <li><i class="fas fa-check"></i> Jusqu'à 10 parcelles</li>
            <li><i class="fas fa-check"></i> Alertes maladies en temps réel</li>
            <li><i class="fas fa-check"></i> Rapports de rendement mensuels</li>
            <li><i class="fas fa-check"></i> Support par email</li>
            <li style="color:#ccc"><i class="fas fa-times"></i> Accès direct agronome</li>
        </ul>
        <a href="inscription.php" class="btn-pricing">Choisir Standard</a>
    </div>

    <div class="pricing-card">
        <h3>Expert</h3>
        <div class="price">15 000 XAF<span>/mois</span></div>
        <p>Pour les grandes exploitations connectées.</p>
        <ul class="features-list">
            <li><i class="fas fa-check"></i> Parcelles illimitées</li>
            <li><i class="fas fa-check"></i> Analyses sols par satellite</li>
            <li><i class="fas fa-check"></i> Accès direct agronome 24/7</li>
            <li><i class="fas fa-check"></i> Gestion d'équipe (ouvriers)</li>
            <li><i class="fas fa-check"></i> Exportation de données avancée</li>
        </ul>
        <a href="inscription.php" class="btn-pricing btn-outline">Choisir Expert</a>
    </div>
</div>

<footer>
    <p>&copy; <?php echo date("Y"); ?> MonAgriCoach - Une solution de ABENG IRENE. Tous droits réservés.</p>
</footer>

</body>
</html>
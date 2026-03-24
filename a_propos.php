<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À Propos | MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #00a693;
            --dark: #1a1a1a;
            --light-bg: #fdfdfd;
            --white: #ffffff;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            margin: 0; 
            color: var(--dark);
            background-color: var(--light-bg);
            line-height: 1.8;
        }

        /* Navigation */
        header { background: var(--white); padding: 15px 5%; border-bottom: 1px solid #eee; position: sticky; top: 0; z-index: 100; }
        nav { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        .logo { color: var(--primary); font-weight: 800; font-size: 22px; text-decoration: none; }
        .nav-links { display: flex; gap: 20px; }
        .nav-links a { text-decoration: none; color: var(--dark); font-weight: 600; font-size: 14px; }

        /* Hero Section avec l'image d'origine */
        .about-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 166, 147, 0.4)), url('https://images.unsplash.com/photo-1523348837708-15d4a09cfac2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 120px 20px;
            text-align: center;
        }

        .about-hero h1 { font-size: 42px; font-weight: 800; margin-bottom: 20px; text-shadow: 0 2px 10px rgba(0,0,0,0.3); }
        .about-hero p { max-width: 700px; margin: 0 auto; font-size: 18px; opacity: 0.9; font-weight: 500; }

        .container { max-width: 1000px; margin: 60px auto; padding: 0 20px; }

        /* Story Section */
        .story-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: center; margin-bottom: 80px; }
        .story-text h2 { color: var(--primary); font-size: 30px; margin-bottom: 20px; }
        
        /* Image avec l'effet de bordure d'origine */
        .story-image img { 
            width: 100%; 
            border-radius: 20px; 
            box-shadow: 20px 20px 0px var(--primary); 
            object-fit: cover;
            height: 350px;
        }

        /* Values Section */
        .values { background: #f0fdfa; padding: 80px 20px; text-align: center; border-radius: 50px; }
        .values-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-top: 40px; }
        .value-card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.03); }
        .value-card i { font-size: 40px; color: var(--primary); margin-bottom: 20px; }
        .value-card h3 { margin-bottom: 10px; }

        /* Team Section */
        .developer-section { text-align: center; margin-top: 80px; padding: 60px 40px; border: 2px dashed #ddd; border-radius: 30px; background: white; }
        .dev-avatar { width: 120px; height: 120px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 45px; margin: 0 auto 25px; box-shadow: 0 10px 20px rgba(0,166,147,0.2); }

        footer { background: var(--dark); color: #fff; padding: 40px 20px; text-align: center; margin-top: 80px; }

        @media (max-width: 768px) {
            .story-grid { grid-template-columns: 1fr; }
            .story-image { order: -1; }
            .about-hero h1 { font-size: 32px; }
        }
    </style>
</head>
<body>

<header>
    <nav>
        <a href="index.php" class="logo"><i class="fas fa-leaf"></i> MonAgriCoach</a>
        <div class="nav-links">
            <a href="index.php">Accueil</a>
            <a href="fonctionnalite.php">Fonctionnalités</a>
            <a href="tarifs.php">Tarifs</a>
        </div>
    </nav>
</header>

<section class="about-hero">
    <h1>Cultiver l'avenir avec précision</h1>
    <p>Nous combinons expertise agronomique et technologie numérique pour transformer chaque parcelle en succès durable.</p>
</section>

<div class="container">
    <div class="story-grid">
        <div class="story-text">
            <h2>Notre Mission</h2>
            <p><strong>MonAgriCoach</strong> est né d'une volonté simple : donner à chaque agriculteur les outils nécessaires pour mieux comprendre sa terre.</p>
            <p>Dans un monde où le climat change et les ressources deviennent précieuses, nous croyons que la donnée est la meilleure alliée de l'agriculteur. Notre plateforme permet de suivre, d'analyser et d'optimiser les cultures pour garantir la sécurité alimentaire et la rentabilité des exploitations.</p>
        </div>
        <div class="story-image">
            <img src="https://images.unsplash.com/photo-1592982537447-7440770cbfc9?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Agriculture moderne">
        </div>
    </div>

    <div class="values">
        <h2>Nos Valeurs Fondamentales</h2>
        <div class="values-grid">
            <div class="value-card">
                <i class="fas fa-hand-holding-heart"></i>
                <h3>Proximité</h3>
                <p>Un outil pensé par et pour les agriculteurs de notre région.</p>
            </div>
            <div class="value-card">
                <i class="fas fa-microchip"></i>
                <h3>Innovation</h3>
                <p>L'utilisation de l'intelligence artificielle pour des recommandations précises.</p>
            </div>
            <div class="value-card">
                <i class="fas fa-globe-africa"></i>
                <h3>Durabilité</h3>
                <p>Optimiser l'usage de l'eau et des intrants pour protéger notre environnement.</p>
            </div>
        </div>
    </div>

    <div class="developer-section">
        <div class="dev-avatar"><i class="fas fa-code"></i></div>
        <h2>Le visage derrière le projet</h2>
        <p>MonAgriCoach est une solution développée avec passion par <strong>ABENG IRENE</strong>.</p>
        <p>Mon objectif est de mettre la technologie au service du développement agricole local pour relever les défis de demain.</p>
        <br>
        <a href="contact.php" style="background: var(--primary); color: white; padding: 12px 25px; border-radius: 50px; text-decoration: none; font-weight: 700; transition: 0.3s;">
            Me contacter <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
        </a>
    </div>
</div>

<footer>
    <p>&copy; <?php echo date("Y"); ?> MonAgriCoach. Tous droits réservés.</p>
</footer>

</body>
</html>
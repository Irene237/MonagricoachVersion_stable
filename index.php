<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MonAgriCoach | Accueil</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-emerald: #00a693;
            --dark-text: #1a1a1a;
            --light-text: #666666;
            --white: #ffffff;
            --glass: rgba(255, 255, 255, 0.95);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: #f8f9fa;
            color: var(--dark-text);
        }

        /* --- HEADER --- */
        header {
            background: var(--white);
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        .logo {
            color: var(--primary-emerald);
            font-size: 24px;
            font-weight: 800;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .nav-links {
            display: flex;
            list-style: none;
            gap: 25px;
            align-items: center;
        }
        .nav-links a {
            text-decoration: none;
            color: #444;
            font-weight: 600;
            font-size: 14px;
        }
        .btn-signup {
            background: var(--primary-emerald);
            color: white !important;
            padding: 10px 22px;
            border-radius: 50px;
            font-weight: 700;
        }

        .hero-container {
    position: relative;
    min-height: 85vh;
    /* L'URL ci-dessous est choisie pour correspondre au flou et à la couleur de votre photo */
    background: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.1)), 
                url('image/premium_photo-1663945778994-11b3201882a0 (1).avif');
    background-size: cover;
    background-position: center;
    padding: 80px 20px;
}

        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .badge {
            background: var(--white);
            color: var(--primary-emerald);
            display: inline-block;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .hero-content h1 {
            color: var(--white);
            font-size: clamp(32px, 5vw, 56px);
            line-height: 1.1;
            font-weight: 800;
            max-width: 800px;
            margin-bottom: 20px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .hero-subtitle {
            color: var(--white);
            font-size: 18px;
            max-width: 600px;
            margin-bottom: 35px;
            opacity: 0.95;
            line-height: 1.6;
        }

        .cta-group { display: flex; gap: 15px; margin-bottom: 60px; }
        .btn {
            padding: 14px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            transition: 0.3s;
        }
        .btn-primary { background: var(--primary-emerald); color: white; }
        .btn-secondary { background: var(--white); color: var(--primary-emerald); }

        /* --- STATS CARDS --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 50px;
        }
        .stat-card {
            background: var(--glass);
            padding: 40px 30px;
            border-radius: 15px;
            position: relative;
            border-left: 8px solid var(--primary-emerald);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .stat-card::after {
            content: "";
            position: absolute;
            bottom: 0; right: 0;
            width: 30px; height: 30px;
            background: var(--primary-emerald);
            clip-path: polygon(100% 0, 100% 100%, 0 100%);
            border-radius: 0 0 15px 0;
        }
        .stat-label {
            font-size: 13px;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--light-text);
            margin-bottom: 20px;
            display: block;
        }
        .stat-flex { display: flex; align-items: center; gap: 20px; }
        .icon-box {
            background: var(--primary-emerald);
            color: white;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 22px;
        }
        .stat-value { font-size: 32px; font-weight: 800; }
        .stat-desc { font-size: 14px; color: var(--light-text); font-weight: 600; }

        /* --- FOOTER --- */
        footer {
            background: white;
            padding: 80px 20px;
            margin-top: 50px;
        }
        .footer-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr;
            gap: 60px;
        }
        .footer-col h3 { font-size: 24px; margin-bottom: 20px; font-weight: 800; }
        .footer-col h4 { font-size: 18px; margin-bottom: 20px; font-weight: 700; }
        .footer-col p { color: var(--light-text); line-height: 1.6; }
        .footer-links { list-style: none; }
        .footer-links li { margin-bottom: 12px; }
        .footer-links a { text-decoration: none; color: var(--light-text); font-weight: 600; }
        
        .newsletter {
            display: flex;
            background: #f0f0f0;
            padding: 5px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .newsletter input {
            border: none;
            background: transparent;
            padding: 10px;
            flex: 1;
            outline: none;
        }
        .newsletter button {
            border: none;
            background: transparent;
            padding: 0 15px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .footer-grid { grid-template-columns: 1fr; }
            .nav-links { display: none; }
        }
    </style>
</head>
<body>

    <header>
        <nav>
            <a href="#" class="logo">
                <i class="fas fa-leaf"></i> MonAgriCoach
            </a>
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="comment-ca-marche.php">comment-ca-marche</a></li>
                <li><a href="contact.php">contact</a></li>
                <li><a href="connexion.php">Connexion</a></li>
                <li><a href="inscription.php" class="btn-signup">S'inscrire</a></li>
            </ul>
        </nav>
    </header>

    <main class="hero-container">
        <div class="hero-content">
            <div class="badge">NOUVEAU : ANALYSE PAR SATELLITE</div>
            
            <h1>Récoltez le fruit de vos<br>Données Agricoles.</h1>
            
            <p class="hero-subtitle">
                Optimisez vos rendements grâce à une gestion intelligente et prédictive de vos cultures basée sur la data.
            </p>

            <div class="cta-group">
                <a href="inscription.php" class="btn btn-primary">Commencer</a>
                <a href="fonctionnalite.php" class="btn btn-secondary">En savoir plus</a>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-label">Performance</span>
                    <div class="stat-flex">
                        <div class="icon-box"><i class="fas fa-chart-line"></i></div>
                        <div>
                            <div class="stat-value">+35%</div>
                            <div class="stat-desc">Gain moyen de rendement</div>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Communauté</span>
                    <div class="stat-flex">
                        <div class="icon-box"><i class="fas fa-users"></i></div>
                        <div>
                            <div class="stat-value">12,000</div>
                            <div class="stat-desc">Agriculteurs actifs</div>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Sécurité</span>
                    <div class="stat-flex">
                        <div class="icon-box"><i class="fas fa-shield-alt"></i></div>
                        <div>
                            <div class="stat-value">100%</div>
                            <div class="stat-desc">Données sécurisées</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-grid">
            <div class="footer-col">
                <h3>MonAgriCoach</h3>
                <h4>Expertise</h4>
                <p>Nous accompagnons les agriculteurs dans leur transition numérique pour une agriculture plus durable et plus rentable.</p>
            </div>
            <div class="footer-col">
                <h4>Liens Utiles</h4>
                <ul class="footer-links">
                    <li><a href="#">Nos solutions</a></li>
                    <li><a href="#">Analyse satellite</a></li>
                    <li><a href="#">Blog & Actualités</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Newsletter</h4>
                <div class="newsletter">
                    <input type="email" placeholder="Votre email">
                    <button><i class="fas fa-chevron-right"></i></button>
                </div>
                <div style="font-weight: 600;">
                    <p><i class="fas fa-globe"></i> International</p>
                    <p><i class="fab fa-facebook"></i> MonAgriCoach-Officiel</p>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
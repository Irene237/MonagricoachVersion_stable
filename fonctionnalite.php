<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MonAgriCoach | Fonctionnalités</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-emerald: #00a693;
            --secondary-gold: #ffb800;
            --dark-text: #1a1a1a;
            --light-bg: #f8f9fa;
            --white: #ffffff;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--light-bg); 
            color: var(--dark-text);
            margin: 0;
            line-height: 1.6;
        }

        /* --- NAVIGATION --- */
        header { background: var(--white); padding: 15px 5%; border-bottom: 1px solid #eee; }
        nav { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        .logo { color: var(--primary-emerald); font-weight: 800; font-size: 22px; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .nav-links { list-style: none; display: flex; gap: 20px; }
        .nav-links a { text-decoration: none; color: var(--dark-text); font-weight: 600; font-size: 14px; }

        /* --- HERO SECTION --- */
        .hero-features { background: var(--primary-emerald); color: white; padding: 60px 20px; text-align: center; }
        .hero-features h1 { font-size: clamp(28px, 4vw, 42px); font-weight: 800; margin-bottom: 15px; }
        .hero-features p { max-width: 800px; margin: 0 auto; opacity: 0.9; }

        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }

        /* --- RECHERCHE --- */
        .search-bar { background: var(--white); padding: 20px; border-radius: 12px; margin-top: -40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); display: flex; gap: 10px; }
        .search-bar input { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; }
        .search-bar button { background: var(--primary-emerald); color: white; border: none; padding: 10px 25px; border-radius: 8px; cursor: pointer; font-weight: 700; }

        /* --- GRID FONCTIONNALITÉS --- */
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 50px; }
        .feature-card { background: var(--white); padding: 30px; border-radius: 20px; border-bottom: 4px solid transparent; transition: 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .feature-card:hover { border-bottom: 4px solid var(--primary-emerald); transform: translateY(-5px); }
        .feature-card i { font-size: 40px; color: var(--primary-emerald); margin-bottom: 20px; }
        .feature-card h3 { margin-bottom: 15px; font-weight: 800; }

        /* --- TÉMOIGNAGES --- */
        .testimonials { background: #eefdfa; padding: 60px 20px; border-radius: 30px; margin: 60px 0; }
        .testimonial-flex { display: flex; gap: 20px; flex-wrap: wrap; justify-content: center; margin-top: 30px; }
        .t-card { background: white; padding: 25px; border-radius: 15px; width: 350px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .t-card img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; border: 3px solid var(--primary-emerald); }
        .t-card blockquote { font-style: italic; color: #555; margin-bottom: 15px; }

        /* --- FOOTER --- */
        footer { background: #1a1a1a; color: white; padding: 60px 20px; margin-top: 80px; }
        .footer-grid { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; }
        footer a { color: #aaa; text-decoration: none; font-size: 14px; }
        footer a:hover { color: var(--primary-emerald); }

        .btn-cta { background: var(--secondary-gold); color: var(--dark-text); padding: 15px 40px; border-radius: 50px; text-decoration: none; font-weight: 800; display: inline-block; margin-top: 20px; transition: 0.3s; }
        .btn-cta:hover { transform: scale(1.05); }
    </style>
</head>
<body>

    <header>
        <nav>
            <a href="index.php" class="logo"><i class="fas fa-leaf"></i> MonAgriCoach</a>
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="a_propos.php">À propos</a></li>
                <li><a href="fonctionnalite.php" style="color:var(--primary-emerald)">Fonctionnalités</a></li>
                <li><a href="tarifs.php">Tarifs</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="inscription.php" style="background:var(--primary-emerald); color:white; padding:8px 15px; border-radius:20px;">S'inscrire</a></li>
            </ul>
        </nav>
    </header>

    <main class="hero-features">
        <h1>Fonctionnalités pour une agriculture optimale</h1>
        <p>Découvrez comment notre plateforme vous aide à maximiser vos rendements, réduire vos coûts et prendre des décisions éclairées pour chaque parcelle.</p>
    </main>

    <div class="container">
        <form action="resultats_recherche.php" method="GET" class="search-bar">
            <i class="fas fa-search" style="align-self:center; color:#999"></i>
            <input type="text" name="search_query" placeholder="Saisissez votre recherche (ex: irrigation, parcelles)...">
            <button type="submit">Rechercher</button>
        </form>

        <section class="features-grid">
            <div class="feature-card">
                <i class="fas fa-map-marked-alt"></i>
                <h3>Cartographie & Parcelles</h3>
                <p>Créez des profils détaillés avec localisation GPS et types de sol pour une vue d'ensemble claire.</p>
                <ul style="padding-left: 20px; font-size: 13px; color: #666;">
                    <li>Vue d'ensemble claire</li>
                    <li>Planification des rotations</li>
                </ul>
            </div>

            <div class="feature-card">
                <i class="fas fa-seedling"></i>
                <h3>Suivi des Cultures</h3>
                <p>Visualisez l'état de chaque plantation en temps réel, de la semence à la récolte finale.</p>
            </div>

            <div class="feature-card">
                <i class="fas fa-flask"></i>
                <h3>Optimisation des Intrants</h3>
                <p>Gérez vos stocks et recevez des alertes pour ne jamais manquer de fertilisants ou de produits.</p>
            </div>

            <div class="feature-card">
                <i class="fas fa-chart-pie"></i>
                <h3>Analyses Intelligentes</h3>
                <p>Recommandations personnalisées pour l'irrigation et la protection des cultures basées sur la donnée.</p>
            </div>

            <div class="feature-card">
                <i class="fas fa-wifi-slash"></i>
                <h3>Accès Hors Ligne</h3>
                <p>Saisissez vos données même sans réseau. Synchronisation automatique dès le retour de la connexion.</p>
            </div>

            <?php if (isset($inclure_accompagnement_expert) && $inclure_accompagnement_expert): ?>
            <div class="feature-card" style="background: #f0fdfa;">
                <i class="fas fa-user-graduate"></i>
                <h3>Accompagnement Expert</h3>
                <p>Bénéficiez des conseils personnalisés de nos agronomes partenaires directement dans l'application.</p>
            </div>
            <?php endif; ?>
        </section>

        <section class="testimonials">
            <h2 style="text-align: center; font-weight: 800;">Ils utilisent MonAgriCoach</h2>
            <div class="testimonial-flex">
                <?php
                $temoignages_details = [
                    ["photo" => "https://ui-avatars.com/api/?name=Jean+Dupont&background=00a693&color=fff", "nom" => "Jean Dupont" , "region"=> "Normandie", "citation" => "Mes rendements ont augmenté de 10% grâce à ce site ! Un outil indispensable."],
                    ["photo" => "https://ui-avatars.com/api/?name=Marie+Dubois&background=00a693&color=fff", "nom" => "Marie Dubois" , "region"=> "Bretagne", "citation" => "La gestion de mes parcelles n'a jamais été aussi simple et intuitive."]
                ];
                foreach ($temoignages_details as $t): ?>
                    <div class="t-card">
                        <img src="<?= $t['photo'] ?>" alt="Photo de <?= $t['nom'] ?>">
                        <blockquote>"<?= $t['citation'] ?>"</blockquote>
                        <p><strong><?= $t['nom'] ?></strong>, <small><?= $t['region'] ?></small></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section style="text-align: center; padding: 40px 0;">
            <h2>Prêt à transformer votre exploitation ?</h2>
            <p>Rejoignez nos <strong>500+</strong> agriculteurs satisfaits dès aujourd'hui.</p>
            <a href="inscription.php" class="btn-cta">Créer mon compte gratuit</a>
        </section>
    </div>

    <footer>
        <div class="footer-grid">
            <div>
                <h3 style="color:var(--primary-emerald)">MonAgriCoach</h3>
                <p style="font-size: 13px; color: #888;">Expertise numérique pour une agriculture durable et rentable.</p>
            </div>
            <div>
                <h4>Liens</h4>
                <a href="a_propos.php">À propos</a><br>
                <a href="blog.php">Actualités</a><br>
                <a href="faq.php">FAQ</a>
            </div>
            <div>
                <h4>Contact</h4>
                <p style="font-size: 13px; color: #888;">
                    Email: contact@monagricoach.com<br>
                    Tel: +237 6 59 66 35 38
                </p>
            </div>
        </div>
        <div style="text-align: center; margin-top: 40px; border-top: 1px solid #333; padding-top: 20px; font-size: 12px; color: #666;">
            &copy; <?= date("Y") ?> MonAgriCoach. Tous droits réservés.
        </div>
    </footer>

</body>
</html>
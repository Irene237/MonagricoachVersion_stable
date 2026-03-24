<?php
// Démarre la session
session_start();
require_once 'db.php';

// Vérification de connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$agriculteurs = [];

try {
    // Requête SQL : On récupère uniquement les utilisateurs de type 'agriculteur'
    // Note : Si tu as une colonne 'id_conseiller' pour le suivi, ajoute : 
    // WHERE type_utilisateur = 'agriculteur' AND id_conseiller = ?
    $sql = "SELECT id, email, nom, prenom, telephone, pays, date_inscription, photo 
            FROM utilisateur 
            WHERE type_utilisateur = 'agriculteur' 
            ORDER BY date_inscription DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $agriculteurs = $stmt->fetchAll();

    $total = count($agriculteurs);

} catch (PDOException $e) {
    error_log("Erreur liste_agriculteur : " . $e->getMessage());
    $error_message = "Impossible de charger la liste des agriculteurs.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Agriculteurs - MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        :root {
            --color-primary-emerald: #0ab9b1;
            --color-primary-dark: #008080;
            --color-secondary-gold: #fd9f07;
            --color-light-bg: #F8F9FA;
            --color-card-bg: #FFFFFF;
            --color-text-dark: #1F2937;
            --color-text-medium: #4B5563;
            --color-text-light: #9CA3AF;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--color-light-bg);
            margin: 0;
            display: flex;
            color: var(--color-text-dark);
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--color-card-bg);
            height: 100vh;
            position: fixed;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .sidebar .logo {
            font-family: 'Montserrat', sans-serif;
            color: var(--color-primary-emerald);
            font-weight: 800;
            text-align: center;
            padding: 30px 10px;
            text-decoration: none;
            font-size: 20px;
        }

        .sidebar ul { list-style: none; padding: 0; flex-grow: 1; }
        .sidebar li a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: var(--color-text-dark);
            text-decoration: none;
            transition: 0.3s;
            font-size: 15px;
        }
        .sidebar li a:hover { background: #e0f8f8; color: var(--color-primary-dark); }
        .sidebar li a i { margin-right: 15px; width: 20px; text-align: center; }
        
        .active-link {
            background: rgba(6, 189, 189, 0.1);
            color: var(--color-primary-emerald) !important;
            border-left: 5px solid var(--color-primary-emerald);
            font-weight: 600;
        }

        .disconnect-item {
            margin: 20px;
            background-color: var(--color-secondary-gold);
            padding: 12px;
            border-radius: 8px;
            text-align: center;
        }
        .disconnect-item a {
            color: white !important;
            text-decoration: none;
            font-weight: bold;
            display: block;
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px;
            width: calc(100% - var(--sidebar-width));
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
        }

        .header-flex h1 { font-family: 'Montserrat'; font-size: 28px; margin: 0; }

        .badge-total {
            background: var(--color-primary-emerald);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            margin-left: 10px;
        }

        .btn-action {
            background: var(--color-primary-emerald);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(10, 185, 177, 0.2);
            transition: 0.3s;
        }
        .btn-action:hover { background: var(--color-primary-dark); transform: translateY(-2px); }

        /* --- SEARCH BAR --- */
        .search-box {
            background: white;
            padding: 10px 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            margin-bottom: 30px;
            border: 1px solid #eee;
        }
        .search-box input {
            border: none;
            outline: none;
            width: 100%;
            padding: 10px;
            font-family: 'Poppins';
        }

        /* --- FLAT LIST DESIGN --- */
        .list-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .list-header {
            display: grid;
            grid-template-columns: 2fr 1.2fr 1.2fr 1fr 0.5fr;
            padding: 0 25px;
            color: var(--color-text-light);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .agri-row {
            display: grid;
            grid-template-columns: 2fr 1.2fr 1.2fr 1fr 0.5fr;
            align-items: center;
            background: white;
            padding: 20px 25px;
            border-radius: 15px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .agri-row:hover {
            transform: scale(1.01);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            border-left: 5px solid var(--color-primary-emerald);
        }

        .user-cell { display: flex; align-items: center; gap: 15px; }
        .avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #e0f8f8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--color-primary-dark);
            object-fit: cover;
        }

        .name { font-weight: 600; font-size: 15px; color: var(--color-text-dark); }
        .email { font-size: 12px; color: var(--color-text-light); }
        .country { font-size: 14px; font-weight: 500; }
        .phone { font-size: 14px; color: var(--color-text-medium); }
        .date { font-size: 13px; color: var(--color-text-light); }
        .chevron { text-align: right; color: var(--color-primary-emerald); }

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
            <li><a href="rapport.php"><i class="fas fa-file-invoice"></i> Rapports</a></li>
            <li><a href="liste_message_cons.php"><i class="fas fa-comments"></i> Mes messages</a></li>
            <li><a href="liste_agriculteur_cons.php"><i class="fas fa-user-friends"></i> **Agriculteurs**</a></li>
  <li><a href="modifier_profile.php" class="active"><i class="fas fa-user-edit"></i> Modifier mon compte</a></li>
        </ul>
        <div class="disconnect-item">
            <a href="deconnexion.php">DÉCONNEXION</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="header-flex">
            <div>
                <h1>Mes Agriculteurs <span class="badge-total"><?= $total ?></span></h1>
                <p style="color: var(--color-text-medium); margin-top: 5px;">Gérez et suivez les activités de vos exploitants.</p>
            </div>
            <a href="recommandation.php" class="btn-action">+ Recommandation</a>
        </div>

        <div class="search-box">
            <i class="fas fa-search" style="color: var(--color-text-light);"></i>
            <form action="recherche_agri.php" method="GET" style="width: 100%;">
                <input type="text" name="recherche" placeholder="Rechercher par nom, email ou pays...">
            </form>
        </div>

        <div class="list-container">
            <div class="list-header">
                <span>Exploitant</span>
                <span>Pays</span>
                <span>Téléphone</span>
                <span>Inscription</span>
                <span></span>
            </div>

            <?php if (count($agriculteurs) > 0): ?>
                <?php foreach($agriculteurs as $agri): ?>
                    <a href="liste_plantation.php?id=<?= $agri['id']; ?>" class="agri-row">
                        <div class="user-cell">
                            <?php if(!empty($agri['photo'])): ?>
                                <img src="uploads/<?= $agri['photo'] ?>" class="avatar" alt="">
                            <?php else: ?>
                                <div class="avatar"><?= strtoupper(substr($agri['nom'], 0, 1)) ?></div>
                            <?php endif; ?>
                            <div>
                                <div class="name"><?= htmlspecialchars($agri['nom'].' '.$agri['prenom']) ?></div>
                                <div class="email"><?= htmlspecialchars($agri['email']) ?></div>
                            </div>
                        </div>

                        <div class="country">
                            <i class="fas fa-globe-africa" style="margin-right: 8px; color: var(--color-primary-emerald);"></i>
                            <?= htmlspecialchars($agri['pays']) ?>
                        </div>

                        <div class="phone">
                            <i class="fas fa-phone-alt" style="margin-right: 8px; opacity: 0.5;"></i>
                            <?= htmlspecialchars($agri['telephone']) ?>
                        </div>

                        <div class="date">
                            <?= date('d/m/Y', strtotime($agri['date_inscription'])) ?>
                        </div>

                        <div class="chevron">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 50px; background: white; border-radius: 15px;">
                    <i class="fas fa-user-slash" style="font-size: 40px; color: #eee; margin-bottom: 15px;"></i>
                    <p style="color: var(--color-text-light);">Aucun agriculteur trouvé dans votre réseau.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
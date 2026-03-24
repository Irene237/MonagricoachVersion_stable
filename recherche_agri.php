<?php
session_start();
require_once 'db.php';

// Vérification de connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$agriculteurs = [];
$conditions = [];
$params = [];

// 1. Condition de base immuable
$conditions[] = "type_utilisateur = 'agriculteur'";

// 2. Logique de recherche "Commence par"
if (!empty($_GET['recherche'])) {
    $recherche = trim($_GET['recherche']);
    // Le % à la fin seulement signifie "commence par"
    $termeRecherche = $recherche . '%'; 
    
    $conditions[] = "(nom LIKE :terme OR prenom LIKE :terme)";
    $params[':terme'] = $termeRecherche;
}

// 3. Construction de la requête
$query_string = "SELECT * FROM utilisateur";
if (!empty($conditions)) {
    $query_string .= " WHERE " . implode(" AND ", $conditions);
}
$query_string .= " ORDER BY nom ASC";

try {
    $stmt = $pdo->prepare($query_string);
    $stmt->execute($params);
    $agriculteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total = count($agriculteurs);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche Agriculteurs</title>
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

        body { font-family: 'Poppins', sans-serif; background-color: var(--color-light-bg); margin: 0; display: flex; }

        /* --- SIDEBAR --- */
        .sidebar { width: var(--sidebar-width); background: var(--color-card-bg); height: 100vh; position: fixed; box-shadow: 2px 0 10px rgba(0,0,0,0.05); display: flex; flex-direction: column; }
        .sidebar .logo { font-family: 'Montserrat', sans-serif; color: var(--color-primary-emerald); font-weight: 800; text-align: center; padding: 30px 10px; text-decoration: none; font-size: 20px; }
        .sidebar ul { list-style: none; padding: 0; flex-grow: 1; }
        .sidebar li a { display: flex; align-items: center; padding: 12px 25px; color: var(--color-text-dark); text-decoration: none; transition: 0.3s; }
        .sidebar li a:hover { background: #e0f8f8; color: var(--color-primary-dark); }
        .sidebar li a i { margin-right: 15px; width: 20px; text-align: center; }

        .disconnect-item { margin: 20px; background-color: var(--color-secondary-gold); padding: 12px; border-radius: 8px; text-align: center; }
        .disconnect-item a { color: white !important; text-decoration: none; font-weight: bold; }

        /* --- MAIN CONTENT --- */
        .main-content { margin-left: var(--sidebar-width); padding: 40px; width: calc(100% - var(--sidebar-width)); }
        
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .header-flex h1 { font-family: 'Montserrat'; font-size: 26px; margin: 0; }

        /* --- BARRE DE RECHERCHE --- */
        .search-box {
            background: white;
            padding: 5px 5px 5px 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            margin-bottom: 30px;
            border: 1px solid #eee;
        }
        .search-box form { display: flex; width: 100%; align-items: center; }
        .search-box input { border: none; outline: none; flex-grow: 1; padding: 10px; font-family: 'Poppins'; font-size: 14px; }
        
        .btn-search {
            background: var(--color-primary-dark);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
        }

        /* --- LISTE --- */
        .list-container { display: flex; flex-direction: column; gap: 12px; }
        .agri-row {
            display: grid;
            grid-template-columns: 2fr 1.2fr 1.2fr 1fr 0.5fr;
            align-items: center;
            background: white;
            padding: 18px 25px;
            border-radius: 15px;
            text-decoration: none;
            color: inherit;
            transition: 0.3s;
        }
        .agri-row:hover { transform: translateX(5px); border-left: 5px solid var(--color-primary-emerald); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }

        .user-cell { display: flex; align-items: center; gap: 15px; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; background: #e0f8f8; display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--color-primary-dark); }
        .name { font-weight: 600; }
        .email { font-size: 12px; color: var(--color-text-light); }
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
           
        </ul>
        <div class="disconnect-item">
            <a href="deconnexion.php">DÉCONNEXION</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="header-flex">
            <h1>Résultats pour "<?= htmlspecialchars($_GET['recherche'] ?? '') ?>"</h1>
            <a href="recommandation.php" style="color: var(--color-primary-emerald); font-weight: 500;">Recommandation</a>
           
        </div>

        <div class="search-box">
            <i class="fas fa-search" style="color: var(--color-text-light);"></i>
            <form action="recherche_agri.php" method="GET">
                <input type="text" name="recherche" placeholder="Rechercher un nom..." value="<?= htmlspecialchars($_GET['recherche'] ?? '') ?>">
                
            </form>
        </div>

        <div class="list-container">
            <?php if ($total > 0): ?>
                <?php foreach($agriculteurs as $agri): ?>
                    <a href="liste_plantation.php?id=<?= $agri['id']; ?>" class="agri-row">
                        <div class="user-cell">
                            <div class="avatar"><?= strtoupper(substr($agri['nom'], 0, 1)) ?></div>
                            <div>
                                <div class="name"><?= htmlspecialchars($agri['nom'].' '.$agri['prenom']) ?></div>
                                <div class="email"><?= htmlspecialchars($agri['email']) ?></div>
                            </div>
                        </div>
                        <div><?= htmlspecialchars($agri['pays']) ?></div>
                        <div><?= htmlspecialchars($agri['telephone']) ?></div>
                        <div style="font-size: 13px; color: #999;"><?= date('d/m/Y', strtotime($agri['date_inscription'])) ?></div>
                        <div class="chevron"><i class="fas fa-chevron-right"></i></div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; background: white; border-radius: 15px;">
                    <p>Aucun agriculteur ne commence par ces lettres.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
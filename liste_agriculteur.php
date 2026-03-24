<?php
session_start();
require_once 'db.php';

// Sécurité : Vérifier si l'admin est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) { 
    header("Location: connexion_admin.php"); 
    exit(); 
} 

try {
    // 1. Récupération des agriculteurs (Basé sur votre table 'utilisateur')
    $sql = "SELECT id, email, nom, prenom, telephone, pays, date_inscription, photo 
            FROM utilisateur 
            WHERE type_utilisateur = 'agriculteur' 
            ORDER BY date_inscription DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $agriculteurs = $stmt->fetchAll();

    // 2. Compte total réel
    $sql_total = "SELECT COUNT(*) AS total FROM utilisateur WHERE type_utilisateur = 'agriculteur'";
    $stmt_total = $pdo->query($sql_total);
    $total_result = $stmt_total->fetch();
    $total = $total_result['total'];

} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Erreur de récupération : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste Agriculteurs | MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --color-primary-emerald: #06bdbd; 
            --color-secondary-gold: #FF8C00; 
            --color-light-bg: #F8F9FA;
            --color-card-bg: #FFFFFF;
            --color-text-dark: #1F2937;
            --color-text-light: #9CA3AF;
            --sidebar-width: 260px;
        }

        body { font-family: 'Poppins', sans-serif; margin: 0; background-color: var(--color-light-bg); display: flex; }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width); background: var(--color-card-bg); height: 100vh;
            position: fixed; box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            display: flex; flex-direction: column; padding-top: 25px; z-index: 1000;
        }
        .sidebar .logo {
            font-family: 'Montserrat'; color: var(--color-primary-emerald);
            font-size: 20px; font-weight: 800; text-align: center; text-decoration: none; margin-bottom: 40px; display: block;
        }
        .sidebar ul { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .sidebar li a {
            display: flex; align-items: center; padding: 12px 25px; color: var(--color-text-dark);
            text-decoration: none; font-weight: 500; transition: 0.3s;
        }
        .sidebar li a i { margin-right: 15px; width: 20px; text-align: center; }
        .sidebar li a:hover, .sidebar li a.active {
            background: rgba(6, 189, 189, 0.1); color: var(--color-primary-emerald); border-left: 4px solid var(--color-primary-emerald);
        }
        .sidebar-footer { padding: 20px; padding-bottom: 80px; margin-top: auto; }
        .btn-logout {
            display: flex; align-items: center; justify-content: center;
            background: var(--color-secondary-gold); color: white !important;
            padding: 12px; border-radius: 8px; text-decoration: none; font-weight: 700;
        }

        /* --- CONTENU --- */
        .main-content { margin-left: var(--sidebar-width); padding: 40px; width: calc(100% - var(--sidebar-width)); }
        
        .action-bar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 25px; background: white; padding: 20px; border-radius: 15px;
        }
        .search-form { display: flex; gap: 10px; }
        .search-form input { padding: 10px 15px; border: 1px solid #E5E7EB; border-radius: 8px; width: 250px; }
        .btn-primary { background: var(--color-primary-emerald); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer;}
        
        .total-badge { background: rgba(6, 189, 189, 0.1); color: var(--color-primary-emerald); padding: 8px 15px; border-radius: 20px; font-weight: 700; }

        /* --- TABLEAU --- */
        .table-container { background: white; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #F9FAFB; padding: 15px 20px; text-align: left; color: var(--color-text-light); font-size: 12px; text-transform: uppercase; }
        td { padding: 15px 20px; border-bottom: 1px solid #F3F4F6; font-size: 14px; color: var(--color-text-dark); }
        
        .user-info { display: flex; align-items: center; gap: 12px; }
        .user-photo { width: 35px; height: 35px; border-radius: 50%; background: #eee; object-fit: cover; }
        .actions { display: flex; gap: 15px; font-size: 18px; }
        .btn-edit { color: var(--color-primary-emerald); }
        .btn-delete { color: #EF4444; border: none; background: none; cursor: pointer; padding: 0; font-size: 18px;}
    </style>
</head>
<body>

    <nav class="sidebar">
        <a href="index.php" class="logo"><i class="fas fa-leaf"></i> MonAgriCoach</a>
        <ul>
            <li><a href="admin_dashboard.php"><i class="fas fa-chart-pie"></i> Tableau de bord</a></li>
            <li><a href="liste_agriculteur.php"><i class="fas fa-tractor"></i> Agriculteurs</a></li>
            <li><a href="liste_conseiller.php" class="active"><i class="fas fa-user-tie"></i> Conseillers</a></li>
            <li><a href="tarifs.php"><i class="fas fa-tags"></i> Tarifs</a></li>
            <li><a href="contact.php"><i class="fas fa-envelope"></i> Messages</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="deconnexion_admin.php" class="btn-logout">
                <i class="fas fa-power-off"></i> &nbsp; DÉCONNEXION
            </a>
        </div>
    </nav>

    <div class="main-content">
        <div class="header-title">
            <h1 style="margin-bottom: 5px;">Gestion des Agriculteurs</h1>
            <p style="color: var(--color-text-light); margin-bottom: 30px;">Visualisez et gérez les membres inscrits comme agriculteurs.</p>
        </div>

        <div class="action-bar">
            <form action="recherche_agri.php" method="GET" class="search-form">
                <input type="text" name="recherche" placeholder="Recherche...">
                <button type="submit" class="btn-primary">Rechercher</button>
            </form>
            <div class="total-badge">
                <i class="fas fa-users"></i> <?php echo $total; ?> Agriculteurs inscrits
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Identité</th>
                        <th>Téléphone</th>
                        <th>Pays</th>
                        <th>Date d'inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($agriculteurs as $agri): ?>
                    <tr>
                        <td>
                            <div class="user-info">
                                <img src="<?php echo !empty($agri['photo']) ? 'uploads/'.$agri['photo'] : 'https://ui-avatars.com/api/?name='.urlencode($agri['nom']); ?>" class="user-photo">
                                <div>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($agri['nom'].' '.$agri['prenom']); ?></div>
                                    <div style="font-size: 12px; color: var(--color-text-light);"><?php echo htmlspecialchars($agri['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($agri['telephone']); ?></td>
                        <td><i class="fas fa-map-marker-alt" style="color: #ccc; margin-right: 5px;"></i> <?php echo htmlspecialchars($agri['pays']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($agri['date_inscription'])); ?></td>
                        <td>
                            <div class="actions">
                                <a href="modifier_utilisateur.php?id=<?php echo $agri['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i></a>
                                <form action="supprimer_agriculteur.php" method="POST" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                    <input type="hidden" name="id" value="<?php echo $agri['id']; ?>">
                                    <button type="submit" class="btn-delete"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
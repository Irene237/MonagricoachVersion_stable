<?php
session_start();
require_once 'db.php';

// Sécurité : Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) { 
    header("Location: connexion_admin.php"); 
    exit(); 
} 

$user_id = $_SESSION['user_id']; // On force l'ID de la session
$message = isset($_GET['msg']) ? $_GET['msg'] : "";
$error = isset($_GET['error']) ? $_GET['error'] : "";

try {
    $sql = "SELECT * FROM utilisateur WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $user_id]);
    $user_data = $stmt->fetch();
    
    if (!$user_data) {
        die("Erreur : Compte utilisateur introuvable.");
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "Erreur de connexion aux données.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil | MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --color-primary-emerald: #06bdbd; 
            --color-secondary-gold: #FF8C00; 
            --color-light-bg: #F0F2F5;
            --sidebar-width: 260px;
        }

        body { font-family: 'Poppins', sans-serif; margin: 0; background-color: var(--color-light-bg); display: flex; min-height: 100vh; }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width); background: white; height: 100vh; position: fixed;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05); display: flex; flex-direction: column; padding-top: 25px; z-index: 1000;
        }
        .sidebar .logo { font-family: 'Montserrat'; color: var(--color-primary-emerald); font-size: 20px; font-weight: 800; text-align: center; text-decoration: none; margin-bottom: 40px; display: block; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .sidebar li a { display: flex; align-items: center; padding: 12px 25px; color: #1F2937; text-decoration: none; font-weight: 500; transition: 0.3s; }
        .sidebar li a:hover, .sidebar li a.active { background: rgba(6, 189, 189, 0.1); color: var(--color-primary-emerald); border-left: 4px solid var(--color-primary-emerald); }
        .sidebar-footer { padding: 20px; padding-bottom: 80px; margin-top: auto; }
        .btn-logout { display: flex; align-items: center; justify-content: center; background: var(--color-secondary-gold); color: white !important; padding: 12px; border-radius: 8px; text-decoration: none; font-weight: 700; }

        /* --- MAIN CONTENT --- */
        .main-content { margin-left: var(--sidebar-width); padding: 40px; width: calc(100% - var(--sidebar-width)); display: flex; justify-content: center; align-items: center; }

        .container-box {
            background: white; border-radius: 24px; display: flex; width: 950px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.08); overflow: hidden;
        }

        .side-banner {
            width: 35%; background: linear-gradient(rgba(6, 189, 189, 0.8), rgba(0, 71, 71, 0.9)), 
            url('https://images.unsplash.com/photo-1592982537447-7440770cbfc9?auto=format&fit=crop&q=80');
            background-size: cover; display: flex; flex-direction: column; justify-content: center; align-items: center; color: white; padding: 30px; text-align: center;
        }

        .user-avatar-preview {
            width: 140px; height: 140px; border-radius: 50%; border: 4px solid white; margin-bottom: 20px; object-fit: cover; background: #eee;
        }

        .form-section { width: 65%; padding: 50px; }
        .grid-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px; }
        .full { grid-column: span 2; }

        .field-box { display: flex; flex-direction: column; gap: 6px; }
        .field-box label { font-size: 12px; font-weight: 600; color: #9CA3AF; text-transform: uppercase; }
        .field-box input, .field-box select {
            padding: 12px 15px; border: 1px solid #E5E7EB; border-radius: 10px; font-family: inherit; font-size: 14px; outline: none; transition: 0.3s;
        }
        .field-box input:focus { border-color: var(--color-primary-emerald); box-shadow: 0 0 0 3px rgba(6, 189, 189, 0.1); }

        .btn-update {
            margin-top: 30px; width: 100%; padding: 15px; border: none; border-radius: 12px;
            background: var(--color-primary-emerald); color: white; font-weight: 700; font-size: 16px; cursor: pointer; transition: 0.3s;
        }
        .btn-update:hover { background: #05a5a5; transform: translateY(-2px); }

        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; border-left: 4px solid; }
        .alert-success { background: #d1fae5; color: #065f46; border-color: #065f46; }
        .alert-error { background: #fee2e2; color: #dc2626; border-color: #dc2626; }
    </style>
</head>
<body>

    <nav class="sidebar">
        <a href="index.php" class="logo">
            <i class="fas fa-leaf"></i> MonAgriCoach
        </a>
        
        <ul>
            <li><a href="farmer_dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a></li>
            <li><a href="liste_parcelle.php"><i class="fas fa-map-marker-alt"></i> Parcelles</a></li> 
            <li><a href="liste_plantation.php"><i class="fas fa-seedling"></i> Plantations</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i> Cultures</a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> **Intrants**</a></li> <li><a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li><a href="liste_message_agri.php"><i class="fas fa-comments"></i> Mes messages</a></li> 
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i> Application Intrants</a></li>
            <li><a href="liste_st_intrant.php"><i class="fas fa-warehouse"></i> Stock Intrants</a></li>
            <li><a href="liste_re.php"><i class="fas fa-chart-bar"></i> Rendement</a></li>
            <li><a href="liste_taches.php"><i class="fas fa-tasks"></i> Tâches</a></li> 
            <li><a href="modifier_profil.php" class="active"><i class="fas fa-user-edit"></i> Modifier mon compte</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="deconnexion.php" class="btn-logout"><i class="fas fa-power-off"></i> &nbsp; DÉCONNEXION</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="container-box">
            <div class="side-banner">
                <?php 
                $avatar = !empty($user_data['photo']) ? 'uploads/'.$user_data['photo'] : 'https://ui-avatars.com/api/?background=fff&color=06bdbd&name='.urlencode($user_data['nom']);
                ?>
                <img src="<?= $avatar ?>" class="user-avatar-preview">
                <h3 style="margin: 0;"><?= htmlspecialchars($user_data['nom'] . ' ' . $user_data['prenom']); ?></h3>
                <p style="font-size: 13px; opacity: 0.8;">Agriculteur membre</p>
            </div>

            <div class="form-section">
                <h2>Mes Informations Personnel</h2>
                
                <?php if($message): ?> <div class="alert alert-success"><?= htmlspecialchars($message); ?></div> <?php endif; ?>
                <?php if($error): ?> <div class="alert alert-error"><?= htmlspecialchars($error); ?></div> <?php endif; ?>

                <form action="update_mon_compte.php" method="POST" enctype="multipart/form-data">
                    <div class="grid-inputs">
                        <div class="field-box">
                            <label>Nom</label>
                            <input type="text" name="nom" value="<?= htmlspecialchars($user_data['nom']); ?>" required>
                        </div>
                        <div class="field-box">
                            <label>Prénom</label>
                            <input type="text" name="prenom" value="<?= htmlspecialchars($user_data['prenom']); ?>" required>
                        </div>
                        <div class="field-box full">
                            <label>Adresse Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user_data['email']); ?>" required>
                        </div>
                        <div class="field-box">
                            <label>Téléphone</label>
                            <input type="text" name="telephone" value="<?= htmlspecialchars($user_data['telephone']); ?>">
                        </div>
                        <div class="field-box">
                            <label>Pays</label>
                            <input type="text" name="pays" value="<?= htmlspecialchars($user_data['pays']); ?>">
                        </div>
                        <div class="field-box full">
                            <label>Nouvelle Photo de profil (Optionnel)</label>
                            <input type="file" name="photo" accept="image/*">
                        </div>
                    </div>

                    <button type="submit" class="btn-update">Sauvegarder les modifications</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
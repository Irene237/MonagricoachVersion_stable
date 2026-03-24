<?php
session_start();
require_once 'db.php';

// Sécurité : Vérifier si l'admin est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) { 
    header("Location: connexion_admin.php"); 
    exit(); 
} 

$user_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$user_data = null;
$message = "";

if ($user_id > 0) {
    try {
        // Sélection basée strictement sur votre script SQL (Colonnes réelles)
        $sql = "SELECT id, email, nom, prenom, telephone, pays, date_inscription, type_utilisateur, photo 
                FROM utilisateur WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user_data = $stmt->fetch();
      
        if (!$user_data) {
            $message = "Utilisateur non trouvé dans la base de données.";
        }
    } catch (PDOException $e) {
        error_log("Erreur PDO : " . $e->getMessage());
        $message = "Erreur de connexion aux données.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Profil | MonAgriCoach</title>
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

        /* Image decorative */
        .side-banner {
            width: 35%; background: linear-gradient(rgba(6, 189, 189, 0.8), rgba(0, 71, 71, 0.9)), 
            url('https://images.unsplash.com/photo-1592982537447-7440770cbfc9?auto=format&fit=crop&q=80');
            background-size: cover; display: flex; flex-direction: column; justify-content: center; align-items: center; color: white; padding: 30px; text-align: center;
        }

        .user-avatar-preview {
            width: 120px; height: 120px; border-radius: 50%; border: 4px solid white; margin-bottom: 20px; object-fit: cover; background: #eee;
        }

        /* Formulaire */
        .form-section { width: 65%; padding: 50px; }
        .form-section h2 { margin: 0 0 10px 0; font-size: 24px; color: #1F2937; }
        
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
        .btn-update:hover { background: #05a5a5; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(6, 189, 189, 0.3); }

        .alert { background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; border-left: 4px solid #dc2626; }
    </style>
</head>
<body>

    <nav class="sidebar">
 <a href="index.php" class="logo">
                <i class="fas fa-leaf"></i> MonAgriCoach
            </a>
        <ul>
            <li><a href="admin_dashboard.php"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="liste_agriculteur.php"><i class="fas fa-tractor"></i> Agriculteurs</a></li>
            <li><a href="liste_conseiller.php"><i class="fas fa-user-tie"></i> Conseillers</a></li>
            <li><a href="contact.php"><i class="fas fa-envelope"></i> Messages</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="deconnexion_admin.php" class="btn-logout"><i class="fas fa-power-off"></i> &nbsp; DÉCONNEXION</a>
        </div>
    </nav>

    <div class="main-content">
        <?php if ($user_data): ?>
        <div class="container-box">
            <div class="side-banner">
                <img src="<?php echo !empty($user_data['photo']) ? 'uploads/'.$user_data['photo'] : 'https://ui-avatars.com/api/?size=120&background=fff&color=06bdbd&name='.urlencode($user_data['nom']); ?>" class="user-avatar-preview">
                <h3 style="margin: 0;"><?= htmlspecialchars($user_data['nom'] . ' ' . $user_data['prenom']); ?></h3>
                <p style="font-size: 13px; opacity: 0.8;"><?= ucfirst($user_data['type_utilisateur']); ?></p>
            </div>

            <div class="form-section">
                <h2>Modifier l'utilisateur</h2>
                <p style="color: #9CA3AF; font-size: 14px;">Mise à jour du compte #<?= $user_id; ?></p>

                <?php if($message): ?> <div class="alert"><?= $message; ?></div> <?php endif; ?>

                <form action="update_utilisateur.php" method="POST">
                    <input type="hidden" name="id" value="<?= $user_data['id']; ?>">
                    
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
                            <input type="text" name="telephone" value="<?= htmlspecialchars($user_data['telephone']); ?>" required>
                        </div>
                        <div class="field-box">
                            <label>Pays</label>
                            <input type="text" name="pays" value="<?= htmlspecialchars($user_data['pays']); ?>" required>
                        </div>
                        <div class="field-box full">
                            <label>Rôle Système</label>
                            <select name="type_utilisateur" required>
                                <option value="agriculteur" <?= $user_data['type_utilisateur'] === 'agriculteur' ? 'selected' : ''; ?>>Agriculteur</option>
                                <option value="conseiller_agricole" <?= $user_data['type_utilisateur'] === 'conseiller_agricole' ? 'selected' : ''; ?>>Conseiller agricole</option>
                                <option value="administrateur" <?= $user_data['type_utilisateur'] === 'administrateur' ? 'selected' : ''; ?>>Administrateur</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-update">Enregistrer les modifications</button>
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="liste_agriculteur.php" style="color: #9CA3AF; text-decoration: none; font-size: 13px;"><i class="fas fa-arrow-left"></i> Retour à la liste</a>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; background: white; border-radius: 20px;">
                <i class="fas fa-search-minus" style="font-size: 40px; color: var(--color-secondary-gold); margin-bottom: 20px;"></i>
                <p><?= $message ?: "ID Utilisateur invalide."; ?></p>
                <a href="admin_dashboard.php" class="btn-update" style="display: inline-block; text-decoration: none; width: auto; padding: 10px 30px;">Retour</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
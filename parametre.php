<?php
session_start();
require_once 'db.php';

// 1. SÉCURITÉ & RÉCUPÉRATION DONNÉES
if (!isset($_SESSION['user_id'])) { 
    header("Location: connexion_admin.php"); 
    exit(); 
} 

$user_id = $_SESSION['user_id'];
$message = $_GET['msg'] ?? "";
$error = $_GET['error'] ?? "";

try {
    $sql = "SELECT * FROM utilisateur WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $user_id]);
    $user_data = $stmt->fetch();
    if (!$user_data) die("Erreur : Compte introuvable.");
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
    <title>MonAgriCoach | Paramètres</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-emerald: #00a693;
            --danger-red: #e74c3c;
            --light-bg: #f4f7f6;
            --white: #ffffff;
            --dark-text: #2d3436;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--light-bg); 
            color: var(--dark-text);
            margin: 0;
            line-height: 1.6;
        }

        /* --- NAVIGATION --- */
        header { background: var(--white); padding: 15px 5%; border-bottom: 1px solid #eee; }
        nav { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        .logo { color: var(--primary-emerald); font-weight: 800; font-size: 22px; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .nav-links { list-style: none; display: flex; gap: 20px; padding: 0; }
        .nav-links a { text-decoration: none; color: var(--dark-text); font-weight: 600; font-size: 14px; }

        .page-container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .header-content { text-align: center; margin-bottom: 40px; }
        .header-content h1 { font-weight: 800; color: var(--primary-emerald); }

        /* --- BLOC PROFIL --- */
        .container-box-profile {
            background: white; border-radius: 24px; display: flex; width: 100%; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 40px;
        }

        .side-banner {
            width: 35%; background: linear-gradient(rgba(0, 166, 147, 0.8), rgba(0, 71, 71, 0.9)), 
            url('https://images.unsplash.com/photo-1592982537447-7440770cbfc9?auto=format&fit=crop&q=80');
            background-size: cover; display: flex; flex-direction: column; justify-content: center; align-items: center; color: white; padding: 30px; text-align: center;
        }

        .user-avatar-preview {
            width: 120px; height: 120px; border-radius: 50%; border: 4px solid white; margin-bottom: 15px; object-fit: cover; background: #eee;
        }

        .form-section-profile { width: 65%; padding: 40px; font-family: 'Poppins', sans-serif; }
        .grid-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 20px; }
        .full { grid-column: span 2; }

        .field-box { display: flex; flex-direction: column; gap: 5px; }
        .field-box label { font-size: 11px; font-weight: 600; color: #9CA3AF; text-transform: uppercase; }
        .field-box input, .field-box select {
            padding: 10px 15px; border: 1px solid #E5E7EB; border-radius: 10px; font-size: 14px; outline: none; transition: 0.3s; font-family: inherit;
        }
        .field-box input:focus { border-color: var(--primary-emerald); box-shadow: 0 0 0 3px rgba(0, 166, 147, 0.1); }

        .btn-update {
            margin-top: 25px; width: 100%; padding: 12px; border: none; border-radius: 10px;
            background: var(--primary-emerald); color: white; font-weight: 700; font-size: 15px; cursor: pointer; transition: 0.3s;
        }
        .btn-update:hover { background: #008a7a; transform: translateY(-2px); }

        /* --- AUTRES SECTIONS --- */
        .form-section { 
            background: var(--white); padding: 30px; border-radius: 15px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 30px;
        }
        h2 { font-size: 20px; border-bottom: 2px solid var(--light-bg); padding-bottom: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

        .checkbox-group { display: flex; align-items: center; gap: 10px; margin-bottom: 15px; background: #f9f9f9; padding: 10px; border-radius: 8px; }
        .btn { padding: 12px 25px; border-radius: 8px; border: none; font-weight: 700; cursor: pointer; transition: 0.3s; font-size: 14px; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--primary-emerald); color: white; }
        .btn-danger { background: #fff5f5; color: var(--danger-red); border: 1px solid var(--danger-red); }
        .btn-outline { color: var(--dark-text); border: 1px solid #ddd; }

        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; border-left: 4px solid; }
        .alert-success { background: #d1fae5; color: #065f46; border-color: #065f46; }
        .alert-error { background: #fee2e2; color: #dc2626; border-color: #dc2626; }

        /* --- FOOTER --- */
        footer { background: #1a1a1a; color: white; padding: 60px 20px; margin-top: 80px; }
        .footer-grid { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; }
        footer a { color: #aaa; text-decoration: none; display: block; margin-bottom: 10px; font-size: 14px; }
        footer a:hover { color: var(--primary-emerald); }
    </style>
</head>

<body>

    <header>
        <nav>
            <a href="index.php" class="logo"><i class="fas fa-leaf"></i> MonAgriCoach</a>
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="fonctionnalite.php">Fonctionnalités</a></li>
                <li><a href="parametre.php" style="color:var(--primary-emerald)">Paramètres</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
    </header>

    <div class="page-container">
        
        <div class="header-content">
            <h1><i class="fas fa-cog"></i> Paramètres du compte</h1>
            <p>Gérez vos informations personnelles et vos préférences d'utilisation.</p>
        </div>

        <?php if($message): ?> <div class="alert alert-success"><?= htmlspecialchars($message); ?></div> <?php endif; ?>
        <?php if($error): ?> <div class="alert alert-error"><?= htmlspecialchars($error); ?></div> <?php endif; ?>

        <div class="container-box-profile">
            <div class="side-banner">
                <?php 
                $avatar = !empty($user_data['photo']) ? 'uploads/'.$user_data['photo'] : 'https://ui-avatars.com/api/?background=fff&color=00a693&name='.urlencode($user_data['nom']);
                ?>
                <img src="<?= $avatar ?>" class="user-avatar-preview">
                <h3 style="margin: 0;"><?= htmlspecialchars($user_data['nom'] . ' ' . $user_data['prenom']); ?></h3>
                <p style="font-size: 13px; opacity: 0.8;">Agriculteur membre</p>
            </div>

            <div class="form-section-profile">
                <h2 style="border:none; margin-bottom:10px;"><i class="fas fa-user-edit"></i> Informations de profil</h2>
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
                            <label>Adresse e-mail</label>
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
                            <label>Changer la photo de profil</label>
                            <input type="file" name="photo" accept="image/*">
                        </div>
                    </div>
                    <button type="submit" class="btn-update">Enregistrer les modifications</button>
                </form>
            </div>
        </div>

        <div class="form-section">
            <h2><i class="fas fa-globe"></i> Préférences système</h2>
            <form action="update_settings.php" method="POST">
                <div class="grid-inputs" style="margin-top:0;">
                    <div class="field-box">
                        <label>Langue de l'interface</label>
                        <select name="langue">
                            <option value="fr" selected>Français</option>
                        </select>
                    </div>
                    <div class="field-box">
                        <label>Fuseau horaire</label>
                        <select name="timezone">
                            <option value="Africa/Douala">GMT+1 (Douala)</option>
                            <option value="Europe/Paris">GMT+1 (Paris)</option>
                        </select>
                    </div>
                </div>
                
                <h3 style="font-size: 16px; margin-top:25px;"><i class="fas fa-bell"></i> Notifications</h3>
                <div class="checkbox-group">
                    <input type="checkbox" name="rappels" id="rappels" checked>
                    <label for="rappels">Recevoir les rappels d'arrosage automatique</label>
                </div>
                
                <button type="submit" class="btn btn-primary">Enregistrer les préférences</button>
            </form>
        </div>

        <div class="form-section">
            <h2><i class="fas fa-exclamation-triangle" style="color:var(--danger-red)"></i> Zone de danger</h2>
            <div class="button-group">
                <a href="supprimer_compte.php" class="btn btn-danger" onclick="return confirm('Attention ! Action irréversible. Supprimer votre compte ?')">Supprimer mon compte</a>
                <a href="deconnexion.php" class="btn btn-outline" style="margin-left:10px;">Se déconnecter</a>
            </div>
        </div>

    </div>

    <footer>
        <div class="footer-grid">
            <div>
                <h3 style="margin-top:0;">MonAgriCoach</h3>
                <p>Votre partenaire numérique pour une gestion agricole intelligente et connectée.</p>
            </div>
            <div>
                <h4>Contact</h4>
                <p>Email: contact@monagricoach.com</p>
                <p>Tel: +237 6 59 66 35 38</p>
            </div>
            <div>
                <h4>Légal</h4>
                <a href="cgu.php">CGU</a>
                <a href="confidentialite.php">Confidentialité</a>
            </div>
        </div>
        <p style="text-align: center; margin-top: 40px; font-size: 12px; color: #777;">
            &copy; <?= date("Y"); ?> MonAgriCoach. Tous droits réservés.
        </p>
    </footer>

</body>
</html>
<?php 
require_once 'db.php'; 
session_start();
$message = ''; 

// 1. Redirection si l'utilisateur est déjà connecté
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $u_type = $_SESSION['user_type'] ?? ''; 
    if ($u_type === 'agriculteur') { header("Location: farmer_dashboard.php"); exit(); }
    elseif ($u_type === 'conseiller_agricole') { header("Location: advisor_dashboard.php"); exit(); }
}

// 2. Traitement du formulaire d'inscription
if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $email = htmlspecialchars(trim($_POST['email'] ?? '')); 
    $nom = htmlspecialchars(trim($_POST["nom"] ?? ''));
    $password = $_POST['mot_de_passe'] ?? '';
    $prenom = htmlspecialchars(trim($_POST["prenom"] ?? ''));
    $telephone = htmlspecialchars(trim($_POST["telephone"] ?? ''));
    $pays = htmlspecialchars(trim($_POST["pays"] ?? ''));
    $type_utilisateur = htmlspecialchars(trim($_POST["type_utilisateur"] ?? '')); 
    
    if (empty($email) || empty($password) || empty($nom) || empty($prenom) || empty($type_utilisateur)) { 
        $message = "Champs obligatoires manquants."; 
    } else { 
        try { 
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = :email"); 
            $stmt->execute([':email' => $email]); 
            
            if ($stmt->fetchColumn() > 0) { 
                $message = "Email déjà utilisé."; 
            } else { 
                $photo_name = ""; 
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
                    $photo_name = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['photo']['name']);
                    if(!is_dir('uploads')) { mkdir('uploads', 0777, true); }
                    move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/" . $photo_name);
                }

                $hashed_password = password_hash($password, PASSWORD_DEFAULT); 
                
                $sql = "INSERT INTO utilisateur (email, mot_de_passe, nom, prenom, telephone, pays, date_inscription, type_utilisateur, reset_token, reset_token_expiration, photo) 
                        VALUES (:email, :mot_de_passe, :nom, :prenom, :telephone, :pays, NOW(), :type_utilisateur, '', '1000-01-01 00:00:00', :photo)"; 
                
                $stmt = $pdo->prepare($sql); 
                $stmt->execute([
                    ':email' => $email, ':mot_de_passe' => $hashed_password, ':nom' => $nom, 
                    ':prenom' => $prenom, ':telephone' => $telephone, ':pays' => $pays, 
                    ':type_utilisateur' => $type_utilisateur, ':photo' => $photo_name
                ]); 

                // CONNEXION AUTO
                $new_id = $pdo->lastInsertId();
                $_SESSION['user_id'] = $new_id;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_type'] = $type_utilisateur;
                $_SESSION['logged_in'] = true;

                if ($type_utilisateur === 'agriculteur') { header("Location: farmer_dashboard.php"); }
                else { header("Location: advisor_dashboard.php"); }
                exit();
            } 
        } catch (PDOException $e) { $message = "Erreur technique."; } 
    } 
} 
?> 

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Inscription | MonAgriCoach</title> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Montserrat:wght@800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        :root { --primary: #06bdbd; }

        body, html {
            margin: 0; padding: 0; height: 100vh; width: 100vw;
            font-family: 'Poppins', sans-serif;
            display: flex; justify-content: center; align-items: center;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), 
                        url('https://images.pexels.com/photos/2132250/pexels-photo-2132250.jpeg?auto=compress&cs=tinysrgb&w=1920');
            background-size: cover; background-position: center;
            overflow: hidden;
        }

        .main-container { display: flex; align-items: flex-end; gap: 40px; width: 95%; max-width: 1100px; justify-content: center; }

        /* --- EFFET BRILLANT (SHIMMER) --- */
        .glass-card {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 40px; padding: 25px 35px;
            width: 100%; max-width: 500px;
            box-shadow: 0 40px 80px rgba(0,0,0,0.6);
            color: white; position: relative; overflow: hidden;
        }

        .glass-card::before {
            content: ""; position: absolute; top: 0; left: -150%; width: 50%; height: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.3), transparent);
            transform: skewX(-25deg); transition: 0.8s;
        }

        .glass-card:hover::before { left: 150%; }

        /* --- ANIMATIONS SVG --- */
        @keyframes body-stand { 0%, 50%, 100% { transform: translateY(0); } 65%, 85% { transform: translateY(-35px); } }
        @keyframes hoe-swing { 0%, 40%, 100% { transform: rotate(0deg); } 20% { transform: rotate(22deg) translateY(5px); } }
        @keyframes hand-wave { 0%, 60%, 100% { transform: rotate(0deg); opacity: 0; } 70%, 80% { transform: rotate(-35deg); opacity: 1; } }
        .f-body { animation: body-stand 6s ease-in-out infinite; transform-origin: bottom; }
        .f-hoe { animation: hoe-swing 2s ease-in-out infinite; transform-origin: 130px 110px; }
        .f-wave { animation: hand-wave 6s ease-in-out infinite; transform-origin: 145px 85px; }

        h1 { font-family: 'Montserrat', sans-serif; font-size: 26px; text-align: center; margin: 0; }
        .tagline { color: var(--primary); font-size: 10px; text-align: center; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .full { grid-column: span 2; }
        .input-group { position: relative; }
        .input-group label { font-size: 10px; color: #fff; margin-bottom: 3px; display: block; font-weight: 600; padding-left: 5px; }
        .input-group i { position: absolute; left: 15px; bottom: 11px; color: var(--primary); font-size: 14px; }
        .input-group input, .input-group select {
            width: 100%; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.15);
            padding: 9px 10px 9px 42px; border-radius: 12px; color: white; font-size: 13px; outline: none; box-sizing: border-box;
        }

        button {
            width: 100%; padding: 14px; background: var(--primary); color: white; border: none; border-radius: 15px;
            font-size: 16px; font-weight: 900; cursor: pointer; margin-top: 15px; text-transform: uppercase; transition: 0.3s;
        }
        button:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(6, 189, 189, 0.4); }
        .footer { text-align: center; margin-top: 15px; font-size: 12px; color: rgba(255,255,255,0.7); }
        .footer a { color: var(--primary); text-decoration: none; font-weight: 800; }
        @media (max-width: 900px) { .farmer-scene { display: none; } }
    </style>
</head> 
<body> 
    <div class="main-container">
        <div class="farmer-scene">
            <svg viewBox="0 0 200 240" width="300" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="100" cy="225" rx="75" ry="15" fill="rgba(0,0,0,0.4)" />
                <g class="f-body">
                    <path d="M60 140 Q100 135 140 140 L145 220 L55 220 Z" fill="#2c5282" />
                    <g class="f-hoe"><rect x="130" y="110" width="12" height="40" rx="5" fill="#ffd3b6" /><rect x="80" y="145" width="85" height="5" fill="#5d4037" transform="rotate(-45 130 145)" /><path d="M65 185 L90 185 L85 205 L70 205 Z" fill="#455a64" /></g>
                    <g class="f-wave"><rect x="140" y="80" width="16" height="65" rx="8" fill="#ffd3b6" transform="rotate(-35 140 80)" /><circle cx="178" cy="48" r="18" fill="#ffd3b6" /></g>
                    <circle cx="100" cy="75" r="42" fill="#ffd3b6" /><circle cx="85" cy="70" r="3" fill="#2d3748" /><circle cx="115" cy="70" r="3" fill="#2d3748" />
                    <path d="M85 95 Q100 115 115 95" fill="none" stroke="#2d3748" stroke-width="4" stroke-linecap="round" />
                    <g style="transform: translateY(-12px);"><path d="M25 70 Q100 -5 175 70" stroke="#ecc94b" stroke-width="22" fill="none" stroke-linecap="round" /><rect x="15" y="70" width="170" height="10" rx="5" fill="#d69e2e" /></g>
                </g>
            </svg>
        </div>

        <div class="glass-card">
            <h1>Bienvenue</h1>
            <p class="tagline">Votre succès agricole commence ici</p>
            <?php if(!empty($message)) echo "<p style='color:#ff8e8e; text-align:center; font-size:11px; margin-bottom:10px;'>$message</p>"; ?>
            <form action="" method="POST" enctype="multipart/form-data" class="form-grid">
                <div class="input-group"><label>Nom</label><i class="fas fa-id-card"></i><input type="text" name="nom" required></div>
                <div class="input-group"><label>Prénom</label><i class="fas fa-user"></i><input type="text" name="prenom" required></div>
                <div class="input-group full"><label>Email</label><i class="fas fa-envelope"></i><input type="email" name="email" required></div>
                <div class="input-group full"><label>Mot de passe</label><i class="fas fa-lock"></i><input type="password" name="mot_de_passe" required></div>
                <div class="input-group"><label>Téléphone</label><i class="fas fa-phone"></i><input type="text" name="telephone"></div>
                <div class="input-group"><label>Pays</label><i class="fas fa-globe"></i><input type="text" name="pays"></div>
                <div class="input-group full"><label>Profil</label><i class="fas fa-leaf"></i><select name="type_utilisateur" required><option value="agriculteur">Agriculteur</option><option value="conseiller_agricole">Conseiller</option></select></div>
                <div class="input-group full" style="background: rgba(255,255,255,0.05); padding: 8px; border-radius: 12px; border: 1px dashed var(--primary);">
                    <label style="margin:0"><i class="fas fa-camera"></i> Photo</label>
                    <input type="file" name="photo" style="border:none; background:none; color:#ccc; font-size:10px;">
                </div>
                <button type="submit" class="full">S'inscrire</button>
            </form>
            <div class="footer">Déjà membre ? <a href="connexion.php">Se connecter</a></div>
        </div>
    </div>
</body> 
</html>
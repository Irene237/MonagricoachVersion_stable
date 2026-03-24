<?php 
session_start();
require_once 'db.php';

$message = ''; 

// --- Redirection si déjà connecté (Ta logique exacte) ---
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    $user_type = $_SESSION['user_type'];
    if ($user_type === 'agriculteur') {
        header("Location: farmer_dashboard.php"); 
        exit(); 
    } elseif ($user_type === 'conseiller_agricole') {
        header("Location: advisor_dashboard.php"); 
        exit(); 
    } else {
        session_unset();
        session_destroy();
        header("Location: connexion.php?error=invalid_role_session"); 
        exit(); 
    }
} 

// --- Traitement du formulaire (Ta logique exacte) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $email = htmlspecialchars(trim($_POST['email'] ?? '')); 
    $password_input = $_POST['mot_de_passe'] ?? ''; 
    $type_utilisateur_saisi = strtolower(trim($_POST["type_utilisateur"] ?? '')); 

    if (empty($email) || empty($password_input) || empty($type_utilisateur_saisi)) { 
        $message = '<p class="message error">Veuillez remplir tous les champs.</p>'; 
    } else { 
        try { 
            $sql = "SELECT id, email, mot_de_passe, type_utilisateur FROM utilisateur WHERE email = :email"; 
            $stmt = $pdo->prepare($sql); 
            $stmt->bindParam(':email', $email); 
            $stmt->execute(); 
            $user = $stmt->fetch(PDO::FETCH_ASSOC); 

            if ($user && password_verify($password_input, $user['mot_de_passe'])) { 
                if ($user['type_utilisateur'] === $type_utilisateur_saisi) {
                    $_SESSION['user_id'] = $user['id']; 
                    $_SESSION['user_type'] = $user['type_utilisateur']; 
                    $_SESSION['user_email'] = $user['email']; 
                    $_SESSION['logged_in'] = true; 

                    if ($user['type_utilisateur'] === 'agriculteur') {
                        header("Location: farmer_dashboard.php"); 
                        exit(); 
                    } elseif ($user['type_utilisateur'] === 'conseiller_agricole') {
                        header("Location: advisor_dashboard.php"); 
                        exit(); 
                    }
                } else { 
                    $message = '<p class="message error">Le type d\'utilisateur sélectionné ne correspond pas à ce compte.</p>'; 
                } 
            } else { 
                $message = '<p class="message error">Email ou mot de passe incorrect.</p>'; 
            } 
        } catch (PDOException $e) { 
            error_log("Erreur PDO : " . $e->getMessage()); 
            $message = '<p class="message error">Une erreur est survenue.</p>'; 
        } 
    } 
} 
?> 

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Connexion | MonAgriCoach</title> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        :root {
            --primary: #06bdbd;
            --primary-glow: rgba(6, 189, 189, 0.4);
        }

        body, html {
            margin: 0; padding: 0; height: 100vh; width: 100vw;
            font-family: 'Poppins', sans-serif;
            display: flex; justify-content: center; align-items: center;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1920&q=80');
            background-size: cover; background-position: center;
            overflow: hidden;
        }

        /* --- CONTENEUR PRINCIPAL EN DEUX COLONNES --- */
        .main-container {
            display: flex;
            flex-direction: row; /* Force la ligne */
            align-items: center;
            justify-content: center;
            width: 90%;
            max-width: 1000px;
            gap: 40px;
        }

        /* --- COLONNE GAUCHE : L'AGRICULTEUR --- */
        .farmer-side {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: float 5s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(1deg); }
        }

        .farmer-svg {
            width: 100%;
            max-width: 380px;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.5));
        }

        /* Animation du bras qui fait coucou */
        #arm-wave {
            transform-origin: 75px 80px;
            animation: wave 2.5s ease-in-out infinite;
        }

        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(-15deg); }
        }

        /* --- COLONNE DROITE : FORMULAIRE --- */
        .form-side {
            flex: 1;
            max-width: 420px;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            text-align: center;
        }

        .logo-title {
            font-family: 'Montserrat', sans-serif;
            color: var(--primary); font-size: 26px; font-weight: 900;
            margin-bottom: 25px; display: flex; align-items: center; justify-content: center;
            text-shadow: 0 0 15px var(--primary-glow);
        }
        .logo-title i { margin-right: 12px; font-size: 32px; }

        .input-group { position: relative; margin-bottom: 18px; text-align: left; }
        .input-group i {
            position: absolute; left: 18px; top: 50%; transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
        }

        .input-group input, .input-group select {
            width: 100%; background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 14px 15px 14px 50px; border-radius: 15px;
            color: white; font-size: 15px; outline: none; box-sizing: border-box;
            transition: 0.3s;
        }

        .input-group input:focus, .input-group select:focus {
            border-color: var(--primary); box-shadow: 0 0 15px var(--primary-glow);
            background: rgba(255, 255, 255, 0.12);
        }

        .input-group select option { background: #1a1a1a; color: white; }

        button[type="submit"] {
            width: 100%; padding: 15px; background: var(--primary);
            color: white; border: none; border-radius: 15px;
            font-size: 16px; font-weight: 700; cursor: pointer;
            transition: 0.4s; box-shadow: 0 10px 20px rgba(6, 189, 189, 0.3);
            text-transform: uppercase; letter-spacing: 1px;
        }

        button[type="submit"]:hover {
            transform: translateY(-3px); background: #08dada;
            box-shadow: 0 15px 30px var(--primary-glow);
        }

        .message.error {
            background: rgba(239, 68, 68, 0.25); color: #fca5a5;
            padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 14px;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .link { margin-top: 18px; font-size: 14px; }
        .link a { color: var(--primary); text-decoration: none; font-weight: 600; }
        .link a:hover { color: white; }

        /* Responsive : On n'empile que sur les très petits écrans */
        @media (max-width: 768px) {
            .main-container { flex-direction: column; gap: 20px; overflow-y: auto; height: 100%; padding-top: 50px;}
            .farmer-svg { max-width: 200px; }
            body { overflow-y: auto; }
        }
    </style>
</head> 
<body> 

    <div class="main-container">
        <div class="farmer-side">
            <svg class="farmer-svg" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <path d="M70 140 L130 140 L135 180 L110 180 L105 160 L95 160 L90 180 L65 180 Z" fill="#2c5282" />
                <rect x="70" y="90" width="60" height="55" rx="5" fill="#f6ad55" />
                <rect x="75" y="90" width="8" height="50" fill="#2c5282" />
                <rect x="117" y="90" width="8" height="50" fill="#2c5282" />
                <circle cx="100" cy="65" r="28" fill="#ffd3b6" />
                <path d="M55 55 Q100 20 145 55" stroke="#ecc94b" stroke-width="15" fill="none" stroke-linecap="round" />
                <rect x="45" y="55" width="110" height="8" rx="4" fill="#d69e2e" />
                <g id="arm-wave">
                    <rect x="45" y="95" width="12" height="40" rx="6" fill="#ffd3b6" transform="rotate(30 45 95)" />
                    <circle cx="30" cy="80" r="10" fill="#ffd3b6" />
                </g>
                <circle cx="90" cy="65" r="2.5" fill="#2d3748" />
                <circle cx="110" cy="65" r="2.5" fill="#2d3748" />
                <path d="M92 80 Q100 88 108 80" stroke="#2d3748" stroke-width="2" fill="none" />
            </svg>
            <h1 style="color: white; font-family: 'Montserrat'; font-size: 22px; margin-top: 15px; text-align: center;">
                Bienvenue sur <span style="color: var(--primary);">MonAgriCoach</span>
            </h1>
        </div>

        <div class="form-side">
            <div class="glass-card"> 
                <div class="logo-title">
                    <i class="fas fa-leaf"></i> Connexion
                </div>
                
                <?php if(!empty($message)) echo $message; ?> 
                
                <form action="connexion.php" method="POST"> 
                    <div class="input-group">
                        <input type="email" id="email" name="email" placeholder="Adresse Email" required>
                        <i class="fas fa-envelope"></i>
                    </div>
                    
                    <div class="input-group">
                        <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Mot de passe" required>
                        <i class="fas fa-lock"></i>
                    </div>
                    
                    <div class="input-group">
                        <select name="type_utilisateur" id="type_utilisateur" required>
                            <option value="" disabled selected>Votre rôle</option>
                            <option value="agriculteur">Agriculteur</option>
                            <option value="conseiller_agricole">Conseiller agricole</option>
                        </select>
                        <i class="fas fa-user-tag"></i> 
                    </div>
                    
                    <button type="submit">Accéder au Coach</button> 
                </form> 
                
                <div class="link"> 
                    <a href="mot_de_passe_oublie.php">Mot de passe oublié ?</a>
                </div>
                
                <div class="link"> 
                    <p style="color: rgba(255,255,255,0.6);">Nouveau ? <a href="inscription.php">Créer un compte</a></p> 
                </div> 
            </div>
        </div>
    </div>

</body> 
</html>
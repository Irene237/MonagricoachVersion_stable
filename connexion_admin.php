<?php 
session_start();
require_once 'db.php'; 

$message = ''; 

// Redirection si déjà connecté
if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in'])) { 
    header("Location: admin_dashboard.php"); 
    exit(); 
} 

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $email = htmlspecialchars(trim($_POST['email'] ?? '')); 
    $password_input = $_POST['mot_de_passe'] ?? ''; 
 
    if (empty($email) || empty($password_input)) { 
        $message = 'Veuillez remplir tous les champs.'; 
    } else { 
        try { 
            // TA LOGIQUE ORIGINALE (STRICTEMENT IDENTIQUE)
            $sql = "SELECT id, email, mot_de_passe FROM utilisateur WHERE email = :email"; 
            $stmt = $pdo->prepare($sql); 
            $stmt->execute([':email' => $email]); 
            $user = $stmt->fetch(PDO::FETCH_ASSOC); 
 
            if ($user && password_verify($password_input, $user['mot_de_passe'])) { 
                $_SESSION['user_id'] = $user['id']; 
                $_SESSION['user_email'] = $user['email']; 
                $_SESSION['logged_in'] = true; 
 
                header("Location: admin_dashboard.php"); 
                exit(); 
            } else { 
                $message = 'Email ou mot de passe incorrect.'; 
            } 
        } catch (PDOException $e) { 
            error_log("Erreur : " . $e->getMessage()); 
            $message = 'Une erreur technique est survenue.'; 
        } 
    } 
} 
?> 

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Admin | MonAgriCoach</title> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Montserrat:wght@900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root { --primary: #06bdbd; --dark: #1a1a1a; }
        body { 
            margin: 0; height: 100vh; display: flex; justify-content: center; align-items: center; 
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.pexels.com/photos/2132250/pexels-photo-2132250.jpeg?auto=compress&cs=tinysrgb&w=1920');
            background-size: cover; background-position: center;
        }
        .login-card { background: white; padding: 40px; border-radius: 30px; width: 100%; max-width: 400px; text-align: center; border: 2px solid var(--primary); position: relative; }
        .security-icon { position: absolute; top: -35px; left: 50%; transform: translateX(-50%); background: var(--dark); color: white; width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; border: 4px solid var(--primary); }
        .logo-area h1 { font-family: 'Montserrat'; font-size: 24px; margin-top: 25px; margin-bottom: 5px; }
        .badge { background: var(--primary); color: white; padding: 2px 10px; border-radius: 10px; font-size: 10px; font-weight: 700; letter-spacing: 1px; }
        .input-group { text-align: left; margin-top: 20px; }
        .input-group label { font-size: 11px; font-weight: 700; margin-left: 10px; color: #666; }
        .wrapper { position: relative; margin-top: 5px; }
        .wrapper i { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: var(--primary); }
        .wrapper input { width: 100%; padding: 12px 15px 12px 50px; border-radius: 25px; border: 1px solid #ddd; box-sizing: border-box; outline: none; }
        .wrapper input:focus { border-color: var(--primary); }
        button { width: 100%; padding: 14px; background: var(--primary); color: white; border: none; border-radius: 25px; font-weight: 700; cursor: pointer; margin-top: 20px; transition: 0.3s; }
        button:hover { background: var(--dark); }
        .error { color: #d63031; font-size: 13px; margin-top: 15px; background: #fab1a044; padding: 8px; border-radius: 10px; }
        .links { margin-top: 20px; font-size: 12px; }
        .links a { color: var(--dark); text-decoration: none; font-weight: 600; }
    </style>
</head> 
<body> 
    <div class="login-card">
        <div class="security-icon"><i class="fas fa-shield-alt"></i></div>
        <div class="logo-area">
            <h1>MonAgriCoach</h1>
            <span class="badge">ADMINISTRATION</span>
        </div>

        <?php if(!empty($message)) echo "<div class='error'>$message</div>"; ?>

        <form action="" method="POST"> 
            <div class="input-group">
                <label>IDENTIFIANT</label>
                <div class="wrapper">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" required>
                </div>
            </div>
            <div class="input-group">
                <label>MOT DE PASSE</label>
                <div class="wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="mot_de_passe" required>
                </div>
            </div>
            <button type="submit">SE CONNECTER</button> 
        </form> 
        <div class="links"><a href="connexion.php">Retour au portail</a></div>
    </div>
</body> 
</html>
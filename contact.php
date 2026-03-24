<?php
// 1. Connexion à la base de données
$host = "localhost";
$dbname = "agca";
$username = "root";
$password = "";

$success_msg = "";
$error_msg = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// 2. Traitement du formulaire à l'envoi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = htmlspecialchars($_POST['email']);
    $sujet = htmlspecialchars($_POST['sujet']);
    $message = htmlspecialchars($_POST['message']);

    if (!empty($nom) && !empty($email) && !empty($message)) {
        try {
            $sql = "INSERT INTO messages_contact (nom, prenom, email, sujet, message) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $prenom, $email, $sujet, $message]);
            
            $success_msg = "Votre message a été envoyé avec succès !";
        } catch(PDOException $e) {
            $error_msg = "Erreur lors de l'envoi : " . $e->getMessage();
        }
    } else {
        $error_msg = "Veuillez remplir tous les champs obligatoires.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #00a693;
            --dark: #1a1a1a;
            --bg: #f4f7f6;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg); 
            margin: 0; 
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Navigation Style */
        header { background: #fff; padding: 15px 5%; border-bottom: 1px solid #eee; }
        nav { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        .logo { color: var(--primary); font-weight: 800; font-size: 22px; text-decoration: none; }

        .container { flex: 1; max-width: 800px; margin: 50px auto; padding: 0 20px; }

        .contact-card {
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        h1 { color: var(--primary); text-align: center; margin-bottom: 30px; }

        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px; }
        
        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            box-sizing: border-box;
        }

        textarea { height: 120px; resize: vertical; }

        .btn-send {
            background: var(--primary);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 700;
            width: 100%;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-send:hover { opacity: 0.9; transform: translateY(-2px); }

        footer { background: var(--dark); color: #fff; padding: 30px; text-align: center; margin-top: 50px; }
    </style>
</head>
<body>

<header>
    <nav>
        <a href="index.php" class="logo"><i class="fas fa-leaf"></i> MonAgriCoach</a>
        <a href="index.php" style="text-decoration:none; color:var(--dark); font-weight:600;">Retour</a>
    </nav>
</header>

<div class="container">
    <div class="contact-card">
        <h1>Contactez-nous</h1>

        <?php if($success_msg): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <?php if($error_msg): ?>
            <div class="alert alert-error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="contact.php" method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" required placeholder="Votre prénom">
                </div>
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" required placeholder="Votre nom">
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="exemple@mail.com">
            </div>

            <div class="form-group">
                <label for="sujet">Sujet</label>
                <input type="text" id="sujet" name="sujet" required placeholder="Comment pouvons-nous vous aider ?">
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" required placeholder="Décrivez votre demande ici..."></textarea>
            </div>

            <button type="submit" class="btn-send">Envoyer le message</button>
        </form>
    </div>
</div>

<footer>
    <p>&copy; <?php echo date("Y"); ?> MonAgriCoach. Tous droits réservés.</p>
</footer>

</body>
</html>
<?php 
session_start(); // Démarre la session PHP 
require_once 'db.php'; // Inclure le fichier de connexion à la base de données 
 
$message_succes = ''; 
$message_erreur = ''; 

if($_SERVER["REQUEST_METHOD"]== "POST"){
    $email= $_POST['email'];
    if(empty($email)){
        $message_erreur="l'adresse email est requise.";

    }else{
        try{
            $stmt= $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = ?");
            $stmt->execute([$email]);
            $user_existe= $stmt->fetchColumn();
            if($user_existe){
                $token= bin2hex(random_bytes(32));
                $stmt= $pdo->prepare("UPDATE utilisateur SET reset_token=?, reset_token_expiration=DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email=?");
                $stmt->execute([$token, $email]);
                $reset_link="http//localhost/agca/reinitialisation_mdp.php?token=" . $token;
                $sujet = "Rénitialiser votre mot de passe ";
                $corps_message="Bonjour,\n\n" . "Vous avez demander à rénitialiser votre mot de passe" . "Veuillez cliquez sur le lien ci-dessous pour continuer :\n\n" . "ce lien expira dans 1 heure . Si vous n'avez pas fait cette demande, veuillez ignorer cet e-mail.\n\n" . "cordialement,\n" . "l'equipe de votre site.";
            $headers='From: noreply@votre-site.com' . "\r\n" . 'Reply-To: noreply@votre-site.com' ."\r\n" . 'X-Mailer:PHP/'.phpversion();

            if(mail($email, $sujet, $corps_message, $headers)){
                $message_succes="un email de rénitialisation à été envoyer à votre adresse";
            }else{
                $message_erreur="Une erreur est survenu lors de l'envoie del'email .veuillez réeasyer.";
            }

            }else{
                $message_succes="si cette adresse existe un lien de réenitialisation à été envoyer";
            }
         } catch (PDOException $e) {
        error_log("Erreur:" .$e->getMessage());
        echo "une erreur est survenue :" . $e->getMessage();
        }
    }
}

if($message_succes){
    echo '<p style="color::green;">' . htmlspecialchars($message_succes).'</p>';
}
if($message_succes){
    echo '<p style="color::red;">' . htmlspecialchars($message_erreur).'</p>';
}
?>

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Rénitialiser le mo de passe</title> 
    <style>
        
    </style>
</head> 
<body> 
    <div class="page-connexion"> 
        <div class="formulaie-connexion-container"> 
        <h2>  mot de passe oublié</h2> 
        <p>Veuillez entrer votre adresse email pour rénitialiser le mot de passe</p>
        
        <form action="mot_de_passe_oublie.php" method="POST"> 
            
            <label for="email">email :</label> 
            <input type="email"  name="email" placeholder="votre adresse email"  required> 
            <br><br>
            
    
            <button type="submit">Envoyer un lien de rénitialisation</button> 
        </form> 

        
       
    </div>
</div>
</body> 
</html>
   
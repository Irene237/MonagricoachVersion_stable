<?php 
session_start(); // Démarre la session PHP 
require_once 'db.php'; // Inclure le fichier de connexion à la base de données 
 
$message = ''; 
$token=$_GET['token']??'';

if(empty($token)){
    $message="jeton de rénitialisation manquant.";
}else{
    try{
        $stmt=$pdo->prepare("SELECT*FROM utilisateur WHERE reset_token =? AND reset_token_expiration > NOW()");
        $stmt->execute([$token]);
        $user=$stmt->fetch();
        if($user){
            if($_SERVER["REQUEST_METHOD"] == "POST"){
                $new_password=$_POST['password'];
                $confirm_password=$_POST['confirm_password'];
                if($new_password ! == $confirm_password){
                    $message="Les mots de passe ne coresponde pas";
                }elseif{
                    (strien($new_password) <6) {
                        $message="Le mot de passe doit containir au moins 6 caratère";
                    }else{
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$stmt= $pdo->prepare("UPDATE utilisateur SET password=? ,reset_token=NULL, reset_token_expiration=NULL WHERE reset_token=?");
                $stmt->execute([$hashed_password, $token]);
                $message="votre mot de passe à été rénitialisé avec succès ous pouvez maintenant vous connecter";
                header("Refresh:3; url=connexion.php");
                    }
                }
            }else{
                $message="jeton de rénitialisation invalide ou expiré";
            }
        }
        



}catch (PDOException $e) {
        error_log("Erreur:" .$e->getMessage());
        echo "une erreur est survenue :" . $e->getMessage();
        }

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
        <h2>Rénitialiser le mot de passe</h2> 
        <?php if ($message): ?> 
            <p class="message   <?php echo strpos ($message, 'succes')!== false?'succes':''; ?>" <?php echo htmlspecialchars ($message); ?>></p>
            <?php endif; ?>
            <?php if ($user && empty($_POST)): ?>
        <form action="reinitialisation_mdp.php? token=<?php echo htmlspecialchars ($token); ?>" method="POST"> 
            
            <label for="mot_de_passe">Mot de passe :</label> 
            <input type="password"  name="password" placeholder="Nouveau mot de passe"  required> 
            <br><br>
            <label for="mot_de_passe">Mot de passe :</label> 
            <input type="password"  name="confirm_password"   required> 
            <br><br>
   
    
            <button type="submit">Rénitialiser le mot de passe</button> 
        </form> 
<?php endif; ?>
        
       
    </div>
</div>
</body> 
</html>
   
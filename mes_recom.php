<?php 
session_start();
 require_once 'db.php';  

 if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) { 
    // Si non connecté, rediriger vers la page de connexion 
    header("Location: connexion.php"); 
    exit(); 
} 

 $message ='';
// L'utilisateur est connecté, on peut utiliser ses informations de session
 
$utilisateur_recom_id = $_SESSION['user_id']; 
$recoms=[];


try{
    $sql ="Select date_recom,statut_recom,contenu_recom, id_utilisateur_recom From recommandation Where id_utilisateur_recom = :id_utilisateur_recom ";
    $stmt =$pdo->prepare($sql);
    $stmt->bindParam(':id_utilisateur_recom', $agriculteur_recom_id);
    $stmt->execute();
    $recoms =$stmt->fetchAll();

}catch (PDOException $e) {
    error_log ("Erreur recuperation: .$e.get(message()");
    $message="Une erreur est survenu lors du chargement de vos recommandations.";
}
?> 



<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>mes recommandations</title>
    <link rel="stylesheet" href="">
</head>
<body>
<?php if($message): ?>
            <p style="color:blue; font-weight:bold;"><?php echo $message; ?></p>
            <?php endif; ?>

            <h2>Mes recommandations</h2>
            <?php if(empty($recoms)): ?>
                <p>Vous n'avez pas encore reçu de recommandations.</p>
                <?php else: ?>
                    <?php foreach($recoms as $recom): ?>      
                        <div style="border:1px solid #ccc; padding:10px; margin-bottom:15px;">
                            <p><strong>Date de la recommandation  :</strong><?php echo htmlspecialchars($recom['date_recom']); ?></p>
                            <p><strong>Statut :</strong><?php echo htmlspecialchars($recom['statut_recom']); ?></p>
                            <p><strong>recommandation :</strong><?php echo htmlspecialchars($recom['contenu_recom']); ?></p><br>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
            <p><a href="farmer_dashboard.php">retour au tableau de bord</a></p>
</body>


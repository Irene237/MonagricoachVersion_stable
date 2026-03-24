

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier recommandation</title>
    <link rel="stylesheet" href="styl_modi.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
</head>

<body> 
        <nav class="sidebar">
             <a href="acceuil.php" class="logo">🍃Mon agricoach</a>
            <ul>
            <li><a href="farmer_dashboard.php"><i class="fas fa-tachometer-alt">⌂</i> Tableau de bord</a></li>
            <li> <a href="liste_parcelle.php"><i class="fas fa-map-marked-alt">⛰️</i>Parcelle</a></li>
            <li> <a href="liste_plantation.php"><i class="fas fa-seedling">🪴</i>Plantation</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf">☘️</i>Culture</a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask">💩</i>Intrant</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb">🗳️</i>Mes recommandations</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments">📩</i>Mes messages</a></li>
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs">🗾</i>Application intrants</a></li>
            <li> <a href="liste_st_intrant.php"><i class="fas fa-box">🎽</i>Stock Intrants</a></li>
            <li><a href="liste_re.php"><i class="fas fa-chart-line">💹</i>Rendement</a></li>
            <li><a href="contact.php">📱Contact</a></li>
            <div class="btn_lien">
 <button ><a href="deconnexion.php">Déconnexion</a></button>
            </div>
            </ul>
        </nav>
        <div class="form-container">
<?php
require_once 'db.php';
$id =(int) $_GET["id"]; $recom=null;
$message=null;
try{
    $sql="select id,date_recom,statut_recom ,contenu_recom,id_plantation,id_utilisateur_recom From recommandation where id= :id";
    $stmt= $pdo->prepare($sql);
     $stmt-> bindParam(':id',$id,PDO::PARAM_INT);
     $stmt-> execute();
     
     $recom=$stmt-> fetch();
  
     if (!$recom){
        $message="recommandation non trouve"; }
     }catch (PDOException $e){
        error_log("erreurPDO:".$e->getmessage());
     }
?>



    <?php if($message):?>
        <p><?= message;?></p>
        <?php endif;?>
        <div style="display:flex">
            <div class="left-img">
            <img  src="images/graphic-designer-choosing-color-from-sampler.jpg" alt="photo">
        </div>
        <form action="update_recom.php" method="POST">
    <input type="date" id="date_recom" name="date_recom" value="<?=$recom['date_recom'] ; ?> " required>
    <input type="text" id="statut_recom" name="statut_recom" value="<?=$recom['statut_recom'] ; ?> " required>
    <input type="text" id="contenu_recom" name="contenu_recom" value="<?=$recom['contenu_recom'] ; ?> " required>
    <input type="hidden" id="id_plantation" name="id_plantation" value="<?=$recom['id_plantation'] ; ?> " required>
    <input type="hidden" id="id_utilisateur_recom" name="id_utilisateur_recom" value="<?=$recom['id_utilisateur_recom'] ; ?> " required>
    <input type="hidden" name="id"  value="<?=$recom['id'] ; ?> ">
    <input type="submit" value="Mettre a jour">
    </from>
    </div>
</div>
</body>





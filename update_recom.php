<?php
require_once 'db.php';

$id =(int) $_POST["id"];
$date_recom=$_POST['date_recom'];
$statut_recom=$_POST['statut_recom'];
$contenu_recom=$_POST['contenu_recom'];
$id_plantation=$_POST['id_plantation'];
$id_utilisateur_recom=$_POST['id_utilisateur_recom'];


try{
    $sql="UPDATE recommandation set date_recom= :date_recom,statut_recom= :statut_recom, contenu_recom= :contenu_recom,id_plantation= :id_plantation,id_utilisateur_recom= :id_utilisateur_recom  where id=:id";
    $stmt = $pdo->prepare($sql);
    $stmt-> bindParam(':date_recom', $date_recom);
    $stmt-> bindParam(':statut_recom', $statut_recom);
    $stmt-> bindParam(':contenu_recom', $contenu_recom);
    $stmt-> bindParam(':id_plantation', $id_plantation);
    $stmt-> bindParam(':id_utilisateur_recom', $id_utilisateur_recom);
    $stmt-> bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    header('location:liste_recom.php');
}catch(PDOException $e){
    error_log("erreur PDO:" .$e->getMessage());
}
?>

<?php
require_once 'db.php';

$id =(int) $_POST["id"];
$quantite_actuelle=$_POST['quantite_actuelle'];
$seuil_alerte=$_POST['seuil_alerte'];
$date_derniere_mise_a_jour=$_POST['date_derniere_mise_a_jour'];
$id_agriculteur=$_POST['id_agriculteur'];
$id_intrant=$_POST['id_intrant'];


try{
    $sql="UPDATE stock_intrant set quantite_actuelle= :quantite_actuelle,seuil_alerte= :seuil_alerte, date_derniere_mise_a_jour= :date_derniere_mise_a_jour,id_agriculteur= :id_agriculteur,id_intrant= :id_intrant  where id=:id";
    $stmt = $pdo->prepare($sql);
    $stmt-> bindParam(':quantite_actuelle', $quantite_actuelle);
    $stmt-> bindParam(':seuil_alerte', $seuil_alerte);
    $stmt-> bindParam(':date_derniere_mise_a_jour', $date_derniere_mise_a_jour);
    $stmt-> bindParam(':id_agriculteur', $id_agriculteur);
    $stmt-> bindParam(':id_intrant', $id_intrant);
    $stmt-> bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    header('location:liste_st_intrant.php');
}catch(PDOException $e){
    error_log("erreur PDO:" .$e->getMessage());
}
?>














<?php
require_once 'db.php';

$id =(int) $_POST["id"];
$date_aplication=$_POST['date_aplication'];
$quantite_appliquee=$_POST['quantite_appliquee'];
$unite_appliquee=$_POST['unite_appliquee'];
$methode_application=$_POST['methode_application'];
$notes=$_POST['notes'];
$id_plantation=$_POST['id_plantation'];
$id_agriculteur=$_POST['id_agriculteur'];
$id_intrant=$_POST['id_intrant'];


try{
    $sql="UPDATE application_intrant set date_aplication= :date_aplication,quantite_appliquee= :quantite_appliquee, unite_appliquee= :unite_appliquee,methode_application= :methode_application,notes= :notes, id_plantation= :id_plantation,id_agriculteur= :id_agriculteur,id_intrant= :id_intrant  where id=:id";
    $stmt = $pdo->prepare($sql);
    $stmt-> bindParam(':date_aplication', $date_aplication);
    $stmt-> bindParam(':quantite_appliquee', $quantite_appliquee);
    $stmt-> bindParam(':unite_appliquee', $unite_appliquee);
    $stmt-> bindParam(':methode_application', $methode_application);
    $stmt-> bindParam(':notes', $notes);
    $stmt-> bindParam(':id_plantation', $id_plantation);
    $stmt-> bindParam(':id_agriculteur', $id_agriculteur);
    $stmt-> bindParam(':id_intrant', $id_intrant);
    $stmt-> bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    header('location:liste_appli_intrant.php');
}catch(PDOException $e){
    error_log("erreur PDO:" .$e->getMessage());
}
?>







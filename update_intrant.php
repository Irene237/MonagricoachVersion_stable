<?php
require_once 'db.php';

$id =(int) $_POST["id"];
$nom_intrant=$_POST['nom_intrant'];
$type_intrant=$_POST['type_intrant'];
$unite_standard=$_POST['unite_standard'];
$descriptions=$_POST['descriptions'];

try{
    $sql="UPDATE intrant set nom_intrant= :nom_intrant,type_intrant= :type_intrant,unite_standard= :unite_standard,descriptions = :descriptions where id=:id";
    $stmt = $pdo->prepare($sql);
    $stmt-> bindParam(':nom_intrant', $nom_intrant);
    $stmt-> bindParam(':type_intrant', $type_intrant);
    $stmt-> bindParam(':unite_standard', $unite_standard);
    $stmt-> bindParam(':descriptions', $descriptions);
    $stmt-> bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    header('location:liste_intrant.php');
}catch(PDOException $e){
    error_log("erreur PDO:" .$e->getMessage());
}

?>



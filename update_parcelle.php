<?php
require_once 'db.php';

$id =(int) $_POST["id"];
$nom_parcelle=$_POST['nom_parcelle'];
$superficie_ha=$_POST['superficie_ha'];
$localisation_gps=$_POST['localisation_gps'];
$typ_sol=$_POST['typ_sol'];
$date_creation=$_POST['date_creation'];
$statut=$_POST['statut'];
$id_agriculteur_proprietaire=$_POST['id_agriculteur_proprietaire'];


try{
    $sql="UPDATE parcelle set nom_parcelle= :nom_parcelle,superficie_ha= :superficie_ha, localisation_gps= :localisation_gps,typ_sol= :typ_sol,date_creation= :date_creation, statut= :statut,id_agriculteur_proprietaire= :id_agriculteur_proprietaire  where id=:id";
    $stmt = $pdo->prepare($sql);
    $stmt-> bindParam(':nom_parcelle', $nom_parcelle);
    $stmt-> bindParam(':superficie_ha', $superficie_ha);
    $stmt-> bindParam(':localisation_gps', $localisation_gps);
    $stmt-> bindParam(':typ_sol', $typ_sol);
    $stmt-> bindParam(':date_creation', $date_creation);
    $stmt-> bindParam(':statut', $statut);
    $stmt-> bindParam(':id_agriculteur_proprietaire', $id_agriculteur_proprietaire);
    $stmt-> bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    header('location:liste_parcelle.php');
}catch(PDOException $e){
    error_log("erreur PDO:" .$e->getMessage());
}
?>
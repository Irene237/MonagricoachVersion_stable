<?php
require_once 'db.php';

$id =(int) $_POST["id"];
$nom_commun=$_POST['nom_commun'];
$cycle_de_vie_jours=$_POST['cycle_de_vie_jours'];
$besoins_eau_mm=$_POST['besoins_eau_mm'];
$besoins_nutriments=$_POST['besoins_nutriments'];
$sensibilite_maladies=$_POST['sensibilite_maladies'];
$informations_generales=$_POST['informations_generales'];

try{
    $sql="UPDATE culture set nom_commun= :nom_commun,cycle_de_vie_jours= :cycle_de_vie_jours,besoins_eau_mm= :besoins_eau_mm,besoins_nutriments= :besoins_nutriments,sensibilite_maladies= :sensibilite_maladies,informations_generales= :informations_generales where id=:id";
    $stmt = $pdo->prepare($sql);
    $stmt-> bindParam(':nom_commun', $nom_commun);
    $stmt-> bindParam(':cycle_de_vie_jours', $cycle_de_vie_jours);
    $stmt-> bindParam(':besoins_eau_mm', $besoins_eau_mm);
    $stmt-> bindParam(':besoins_nutriments', $besoins_nutriments);
    $stmt-> bindParam(':sensibilite_maladies', $sensibilite_maladies);
    $stmt-> bindParam(':informations_generales', $informations_generales);
    $stmt-> bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    header('location:liste_culture.php');
}catch(PDOException $e){
    error_log("erreur PDO:" .$e->getMessage());
}

?>
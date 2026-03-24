<?php
require_once 'db.php';

$id =(int) $_POST["id"];
$date_semis=$_POST['date_semis'];
$date_recolte_prevue=$_POST['date_recolte_prevue'];
$quantite_semis_kg_ha=$_POST['quantite_semis_kg_ha'];
$statut_plantation=$_POST['statut_plantation'];
$rendement_final_kg=$_POST['rendement_final_kg'];
$unite_rendement=$_POST['unite_rendement'];
$id_parcelle=$_POST['id_parcelle'];
$id_culture=$_POST['id_culture'];


try{
    $sql="UPDATE plantation set date_semis= :date_semis,date_recolte_prevue= :date_recolte_prevue, quantite_semis_kg_ha= :quantite_semis_kg_ha,statut_plantation= :statut_plantation,rendement_final_kg= :rendement_final_kg, unite_rendement= :unite_rendement,id_parcelle= :id_parcelle,id_culture= :id_culture  where id=:id";
    $stmt = $pdo->prepare($sql);
    $stmt-> bindParam(':date_semis', $date_semis);
    $stmt-> bindParam(':date_recolte_prevue', $date_recolte_prevue);
    $stmt-> bindParam(':quantite_semis_kg_ha', $quantite_semis_kg_ha);
    $stmt-> bindParam(':statut_plantation', $statut_plantation);
    $stmt-> bindParam(':rendement_final_kg', $rendement_final_kg);
    $stmt-> bindParam(':unite_rendement', $unite_rendement);
    $stmt-> bindParam(':id_parcelle', $id_parcelle);
    $stmt-> bindParam(':id_culture', $id_culture);
    $stmt-> bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    header('location:liste_plantation.php');
}catch(PDOException $e){
    error_log("erreur PDO:" .$e->getMessage());
}
?>
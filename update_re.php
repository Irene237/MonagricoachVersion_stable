<?php
require_once 'db.php';

$id =(int) $_POST["id"];
$date_re=$_POST['date_re'];
$cout=$_POST['cout'];
$quantite=$_POST['quantite'];
$rendement=$_POST['rendement'];
$id_plantation=$_POST['id_plantation'];
$id_agriculteur=$_POST['id_agriculteur'];


try{
    $sql="UPDATE observation set date_re= :date_re,cout= :cout, quantite= :quantite,rendement= :rendement,id_plantation= :id_plantation,id_agriculteur= :id_agriculteur  where id=:id";
    $stmt = $pdo->prepare($sql);
    $stmt-> bindParam(':date_re', $date_re);
    $stmt-> bindParam(':cout', $cout);
    $stmt-> bindParam(':quantite', $quantite);
    $stmt-> bindParam(':rendement', $rendement);
    $stmt-> bindParam(':id_plantation', $id_plantation);
    $stmt-> bindParam(':id_agriculteur', $id_agriculteur);
    $stmt-> bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    header('location:liste_re.php');
}catch(PDOException $e){
    error_log("erreur PDO:" .$e->getMessage());
}
?>


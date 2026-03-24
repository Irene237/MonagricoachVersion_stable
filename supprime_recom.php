<?php
require_once 'db.php';
$id =(int) $_POST["id"];
try{
    $sql = "Delete From recommandation Where id = :id";
    $stmt =$pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt-> execute();
    header("Location:liste_recom.php");
}catch (PDOException $e){
    error_log("Erreur PDO suppression:" .$e->getmessage());
    header("Location: liste_recom.php");
}
?>
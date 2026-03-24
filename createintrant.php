<?php
require_once 'db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nom_intrant']) && isset($_POST['type_intrant']) && isset($_POST['unite_standard']) && isset($_POST['descriptions'])){
$nom_intrant=$_POST["nom_intrant"];
$type_intrant=$_POST["type_intrant"];
$unite_standard=$_POST["unite_standard"];
$descriptions=$_POST["descriptions"];

}else{
    echo" Erreur:Donnees du formulaire non soumise ou incompletes.";
    exit();
}

try{
    $sql="Insert Into intrant(nom_intrant, type_intrant,unite_standard, descriptions) Value (:nom_intrant, :type_intrant,:unite_standard, :descriptions)";
    $stmt=$pdo->prepare($sql);
     $stmt->bindParam (':nom_intrant', $nom_intrant); 
      $stmt->bindParam (':type_intrant', $type_intrant); 
      $stmt->bindParam (':unite_standard', $unite_standard); 
      $stmt->bindParam (':descriptions', $descriptions); 

         $stmt->execute(); 
         echo "intrant ajouter avec succes !";
} catch (PDOException $e) {
    error_log("Erreur:" .$e->getMessage());
    echo "une erreur est survenue :" . $e->getMessage();
    }

?> 
 
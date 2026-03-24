<?php
require_once 'db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nom_commun']) && isset($_POST['cycle_de_vie_jours']) && isset($_POST['besoins_eau_mm']) && isset($_POST['besoins_nutriments']) && isset($_POST['sensibilite_maladies']) && isset($_POST['informations_generales'])){
$nom_commun=$_POST["nom_commun"];
$cycle_de_vie_jours=$_POST["cycle_de_vie_jours"];
$besoins_eau_mm=$_POST["besoins_eau_mm"];
$besoins_nutriments=$_POST["besoins_nutriments"];
$sensibilite_maladies=$_POST["sensibilite_maladies"];
$informations_generales=$_POST["informations_generales"];

}else{
    echo" Erreur:Donnees du formulaire non soumise ou incompletes.";
    exit();
}

try{
    $sql="Insert Into culture(nom_commun, cycle_de_vie_jours,besoins_eau_mm, besoins_nutriments,sensibilite_maladies, informations_generales) Value (:nom_commun, :cycle_de_vie_jours,:besoins_eau_mm, :besoins_nutriments,:sensibilite_maladies, :informations_generales )";
    $stmt=$pdo->prepare($sql);
     $stmt->bindParam (':nom_commun', $nom_commun); 
      $stmt->bindParam (':cycle_de_vie_jours', $cycle_de_vie_jours); 
      $stmt->bindParam (':besoins_eau_mm', $besoins_eau_mm); 
      $stmt->bindParam (':besoins_nutriments', $besoins_nutriments); 
      $stmt->bindParam (':sensibilite_maladies', $sensibilite_maladies); 
      $stmt->bindParam (':informations_generales', $informations_generales); 

         $stmt->execute(); 
         echo "culture ajouter avec succes !";
} catch (PDOException $e) {
    error_log("Erreur:" .$e->getMessage());
    echo "une erreur est survenue :" . $e->getMessage();
    }

?>
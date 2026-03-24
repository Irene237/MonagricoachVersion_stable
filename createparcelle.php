


<?php
include_once 'db.php';

ini_set('display_errors',1);
error_reporting(E_ALL);

echo "<h1>Debug info</h1>";
echo "methode de requete : " . $_SERVER["REQUEST_METHOD"] . "<br>";
echo "contenu de\$_POST : <br>";
var_dump($_POST);
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_agriculteur_proprietaire']) && isset($_POST['nom_parcelle']) && isset ($_POST['superficie_ha']) && isset ($_POST['localisation_gps'])  && isset ($_POST['typ_sol']) && isset ($_POST['date_creation']) && isset ($_POST['statut'])) {
    $id_agriculteur_proprietaire = $_POST["id_agriculteur_proprietaire"];
    $nom_parcelle = $_POST["nom_parcelle"];
    $superficie_ha=$_POST["superficie_ha"];
    $localisation_gps=$_POST["localisation_gps"];
    $typ_sol=$_POST["typ_sol"];
    $date_creation=$_POST["date_creation"];
    $statut=$_POST["statut"];
    if (empty($id_agriculteur_proprietaire) || empty($nom_parcelle)|| empty($superficie_ha) || empty($localisation_gps)|| empty($typ_sol) || empty($date_creation) || empty($statut)) { 
        $message = '<p class="message error">Veuillez remplir tous les champs.</p>'; 
        exit();
}
    try{
  
        $stmt=$pdo->prepare("INSERT INTO parcelle(superficie_ha, localisation_gps,typ_sol,date_creation, id_agriculteur_proprietaire, nom_parcelle,statut) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute ([ $superficie_ha ,$localisation_gps,$typ_sol,$date_creation, $id_agriculteur_proprietaire ,$nom_parcelle, $statut]); 
        echo "  parcelle enregistre avec succes !";
    
    
    } catch (PDOException $e) {
        error_log("Erreur:" .$e->getMessage());
        echo "une erreur est survenue :" . $e->getMessage();
        }
}else{
    echo" Erreur:Donnees du formulaire non soumise ou incompletes.";


}
   


?>


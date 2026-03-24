


<?php
include_once 'db.php';

ini_set('display_errors',1);
error_reporting(E_ALL);

echo "<h1>Debug info</h1>";
echo "methode de requete : " . $_SERVER["REQUEST_METHOD"] . "<br>";
echo "contenu de\$_POST : <br>";
var_dump($_POST);
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_parcelle']) && isset($_POST['id_culture']) && isset ($_POST['date_semis']) && isset ($_POST['date_recolte_prevue'])  && isset ($_POST['quantite_semis_kg_ha']) && isset ($_POST['statut_plantation']) && isset ($_POST['rendement_final_kg']) && isset ($_POST['unite_rendement'])) {
    $id_parcelle = $_POST["id_parcelle"];
    $id_culture = $_POST["id_culture"];
    $date_semis=$_POST["date_semis"];
    $date_recolte_prevue=$_POST["date_recolte_prevue"];
    $quantite_semis_kg_ha=$_POST["quantite_semis_kg_ha"];
    $statut_plantation=$_POST["statut_plantation"];
    $rendement_final_kg=$_POST["rendement_final_kg"];
    $unite_rendement=$_POST["unite_rendement"];
    if (empty($id_parcelle) || empty($id_culture)|| empty($date_semis) || empty($date_recolte_prevue)|| empty($quantite_semis_kg_ha) || empty($statut_plantation) || empty($rendement_final_kg) || empty($unite_rendement)) { 
        $message = '<p class="message error">Veuillez remplir tous les champs.</p>'; 
        exit();
}
    try{
  
        $stmt=$pdo->prepare("INSERT INTO plantation(date_semis, date_recolte_prevue,quantite_semis_kg_ha,statut_plantation,rendement_final_kg,unite_rendement, id_parcelle, id_culture) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute ([ $date_semis ,$date_recolte_prevue,$quantite_semis_kg_ha,$statut_plantation,$rendement_final_kg,$unite_rendement, $id_parcelle ,$id_culture]); 
        echo "  plantation enregistre avec succes !";
    
    
    } catch (PDOException $e) {
        error_log("Erreur:" .$e->getMessage());
        echo "une erreur est survenue :" . $e->getMessage();
        }
}else{
    echo" Erreur:Donnees du formulaire non soumise ou incompletes.";


}
   


?>


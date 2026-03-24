


<?php
include_once 'db.php';

ini_set('display_errors',1);
error_reporting(E_ALL);

echo "<h1>Debug info</h1>";
echo "methode de requete : " . $_SERVER["REQUEST_METHOD"] . "<br>";
echo "contenu de\$_POST : <br>";
var_dump($_POST);
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_agriculteur']) && isset($_POST['id_intrant']) && isset ($_POST['quantite_actuelle']) && isset ($_POST['seuil_alerte'])  && isset ($_POST['date_derniere_mise_a_jour'])) {
    $id_agriculteur = $_POST["id_agriculteur"];
    $id_intrant= $_POST["id_intrant"];
    $quantite_actuelle=$_POST["quantite_actuelle"];
    $seuil_alerte=$_POST["seuil_alerte"];
    $date_derniere_mise_a_jour=$_POST["date_derniere_mise_a_jour"];
    if (empty($id_agriculteur) || empty($id_intrant)|| empty($quantite_actuelle) || empty($seuil_alerte)|| empty($date_derniere_mise_a_jour)) { 
        $message = '<p class="message error">Veuillez remplir tous les champs.</p>'; 
        exit();
}
    try{
  
        $stmt=$pdo->prepare("INSERT INTO stock_intrant(quantite_actuelle, seuil_alerte,date_derniere_mise_a_jour, id_agriculteur, id_intrant) VALUES (?,?,?,?,?)");
        $stmt->execute ([ $quantite_actuelle ,$seuil_alerte,$date_derniere_mise_a_jour, $id_agriculteur ,$id_intrant]); 
        echo "  stock intrant enregistre avec succes !";
    
    
    } catch (PDOException $e) {
        error_log("Erreur:" .$e->getMessage());
        echo "une erreur est survenue :" . $e->getMessage();
        }
}else{
    echo" Erreur:Donnees du formulaire non soumise ou incompletes.";
}
?>









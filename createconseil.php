


<?php
include_once 'db.php';

ini_set('display_errors',1);
error_reporting(E_ALL);

echo "<h1>Debug info</h1>";
echo "methode de requete : " . $_SERVER["REQUEST_METHOD"] . "<br>";
echo "contenu de\$_POST : <br>";
var_dump($_POST);
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_conseiller']) && isset($_POST['id_agriculteur_assiste']) && isset ($_POST['date_debut_assistance']) && isset ($_POST['date_fin_assistance'])  && isset ($_POST['message_conseiller']) && isset ($_POST['message_agriculteur'])) {
    $id_conseiller = $_POST["id_conseiller"];
    $id_agriculteur_assiste = $_POST["id_agriculteur_assiste"];
    $date_debut_assistance=$_POST["date_debut_assistance"];
    $date_fin_assistance=$_POST["date_fin_assistance"];
    $message_conseiller=$_POST["message_conseiller"];
    $message_agriculteur=$_POST["message_agriculteur"];
    if (empty($id_conseiller) || empty($id_agriculteur_assiste)|| empty($date_debut_assistance) || empty($date_fin_assistance)|| empty($message_conseiller) || empty($message_agriculteur)) { 
        $message = '<p class="message error">Veuillez remplir tous les champs.</p>'; 
        exit();
}
    try{
  
        $stmt=$pdo->prepare("INSERT INTO assistance_conseiller(date_debut_assistance, date_fin_assistance,message_conseiller,message_agriculteur, id_conseiller, id_agriculteur_assiste) VALUES (?,?,?,?,?,?)");
        $stmt->execute ([ $date_debut_assistance ,$date_fin_assistance,$message_conseiller,$message_agriculteur, $id_conseiller ,$id_agriculteur_assiste]); 
        echo " message enregistre avec succes !";
    
    
    } catch (PDOException $e) {
        error_log("Erreur:" .$e->getMessage());
        echo "une erreur est survenue :" . $e->getMessage();
        }
}else{
    echo" Erreur:Donnees du formulaire non soumise ou incompletes.";


}
   


?>


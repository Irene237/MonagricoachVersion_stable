


<?php
include_once 'db.php';

ini_set('display_errors',1);
error_reporting(E_ALL);

echo "<h1>Debug info</h1>";
echo "methode de requete : " . $_SERVER["REQUEST_METHOD"] . "<br>";
echo "contenu de\$_POST : <br>";
var_dump($_POST);
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_plantation']) && isset($_POST['id_agriculteur']) && isset ($_POST['id_intrant']) && isset ($_POST['date_aplication'])  && isset ($_POST['quantite_appliquee']) && isset ($_POST['unite_appliquee']) && isset ($_POST['methode_application']) && isset ($_POST['notes'])) {
    $id_plantation = $_POST["id_plantation"];
    $id_agriculteur = $_POST["id_agriculteur"];
    $id_intrant=$_POST["id_intrant"];
    $date_aplication=$_POST["date_aplication"];
    $quantite_appliquee=$_POST["quantite_appliquee"];
    $unite_appliquee=$_POST["unite_appliquee"];
    $methode_application=$_POST["methode_application"];
    $notes=$_POST["notes"];
    if (empty($id_plantation) || empty($id_agriculteur)|| empty($id_intrant) || empty($date_aplication)|| empty($quantite_appliquee) || empty($unite_appliquee) || empty($methode_application) || empty($notes)) { 
        $message = '<p class="message error">Veuillez remplir tous les champs.</p>'; 
        exit();
}
    try{
  
        $stmt=$pdo->prepare("INSERT INTO application_intrant(id_intrant, date_aplication,quantite_appliquee,unite_appliquee,methode_application,notes, id_plantation, id_agriculteur) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute ([ $id_intrant ,$date_aplication,$quantite_appliquee,$unite_appliquee,$methode_application,$notes, $id_plantation ,$id_agriculteur]); 
        echo "  appli intrant enregistre avec succes !";
    
    
    } catch (PDOException $e) {
        error_log("Erreur:" .$e->getMessage());
        echo "une erreur est survenue :" . $e->getMessage();
        }
}else{
    echo" Erreur:Donnees du formulaire non soumise ou incompletes.";


}
   


?>




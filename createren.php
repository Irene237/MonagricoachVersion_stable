



<?php
include_once 'db.php';

ini_set('display_errors',1);
error_reporting(E_ALL);

echo "<h1>Debug info</h1>";
echo "methode de requete : " . $_SERVER["REQUEST_METHOD"] . "<br>";
echo "contenu de\$_POST : <br>";
var_dump($_POST);
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_plantation']) && isset($_POST['id_agriculteur']) && isset ($_POST['date_re']) && isset ($_POST['cout'])  && isset ($_POST['quantite']) && isset ($_POST['rendement'])) {
    $id_plantation = $_POST["id_plantation"];
    $id_agriculteur = $_POST["id_agriculteur"];
    $date_re=$_POST["date_re"];
    $cout=$_POST["cout"];
    $quantite=$_POST["quantite"];
    $rendement=$_POST["rendement"];
    if (empty($id_plantation) || empty($id_agriculteur)|| empty($date_re) || empty($cout)|| empty($quantite) || empty($rendement)) { 
        $message = '<p class="message error">Veuillez remplir tous les champs.</p>'; 
        exit();
}
    try{
  
        $stmt=$pdo->prepare("INSERT INTO observation(date_re, cout,quantite,rendement, id_plantation, id_agriculteur) VALUES (?,?,?,?,?,?)");
        $stmt->execute ([ $date_re ,$cout,$quantite,$rendement, $id_plantation ,$id_agriculteur]); 
        echo "  rendement enregistre avec succes !";
    
    
    } catch (PDOException $e) {
        error_log("Erreur:" .$e->getMessage());
        echo "une erreur est survenue :" . $e->getMessage();
        }
}else{
    echo" Erreur:Donnees du formulaire non soumise ou incompletes.";
}
?>



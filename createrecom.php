


<?php
include_once 'db.php';

ini_set('display_errors',1);
error_reporting(E_ALL);

echo "<h1>Debug info</h1>";
echo "methode de requete : " . $_SERVER["REQUEST_METHOD"] . "<br>";
echo "contenu de\$_POST : <br>";
var_dump($_POST);
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_plantation']) && isset($_POST['id_utilisateur_recom']) && isset ($_POST['date_recom']) && isset ($_POST['statut_recom'])  && isset ($_POST['contenu_recom'])) {
    $id_plantation = $_POST["id_plantation"];
    $id_utilisateur_recom = $_POST["id_utilisateur_recom"];
    $date_recom=$_POST["date_recom"];
    $statut_recom=$_POST["statut_recom"];
    $contenu_recom=$_POST["contenu_recom"];
    if (empty($id_plantation) || empty($id_utilisateur_recom)|| empty($date_recom) || empty($statut_recom)|| empty($contenu_recom)) { 
        $message = '<p class="message error">Veuillez remplir tous les champs.</p>'; 
        exit();
}
    try{
  
        $stmt=$pdo->prepare("INSERT INTO recommandation(date_recom, statut_recom,contenu_recom, id_plantation, id_utilisateur_recom) VALUES (?,?,?,?,?)");
        $stmt->execute ([ $date_recom ,$statut_recom,$contenu_recom, $id_plantation ,$id_utilisateur_recom]); 
        echo "  recommandation enregistre avec succes !";
    
    
    } catch (PDOException $e) {
        error_log("Erreur:" .$e->getMessage());
        echo "une erreur est survenue :" . $e->getMessage();
        }
}else{
    echo" Erreur:Donnees du formulaire non soumise ou incompletes.";
}
?>
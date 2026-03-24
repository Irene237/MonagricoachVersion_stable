
<?php
require_once 'db.php';

$id =(int) $_POST["id"];
$email=$_POST['email'];
$mot_de_passe=$_POST['mot_de_passe'];
$nom=$_POST['nom'];
$prenom=$_POST['prenom'];
$telephone=$_POST['telephone'];
$adresse=$_POST['adresse'];
$ville=$_POST['ville'];
$pays=$_POST['pays'];
$date_inscription=$_POST['date_inscription'];
$type_utilisateur=$_POST['type_utilisateur'];

try{
    $sql="UPDATE utilisateur set email= :email,mot_de_passe= :mot_de_passe,nom= :nom,prenom= :prenom,telephone= :telephone,adresse= :adresse,ville= :ville,pays= :pays, date_inscription= :date_inscription,type_utilisateur= :type_utilisateur where id=:id";
    $stmt = $pdo->prepare($sql);
    $stmt-> bindParam(':email', $email);
    $stmt-> bindParam(':nom', $nom);
    $stmt-> bindParam(':prenom', $prenom);
     $stmt-> bindParam(':telephone', $telepone);
    $stmt-> bindParam(':adresse', $adresse);
    $stmt-> bindParam(':ville', $ville);
     $stmt-> bindParam(':pays', $pays);
    $stmt-> bindParam(':date_inscription', $date_inscription);
    $stmt-> bindParam(':type_utilisateur', $type_utilisateur);
    if(!empty($mot_de_passe)){
        $hashed_password = password_hash($mot_de_passe,PASSWORD_BCRYPT);
        $stmt-> bindParam(':mot_de_passe', $hashed_password);
    }
    $stmt-> bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    header('location:admin_dashboard.php');

}catch(PDOException $e){
    error_log("erreur PDO:" .$e->getMessage());
}
    
?>
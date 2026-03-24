<?php
session_start();
require_once 'db.php';

// Vérifier la connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion_admin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; 
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $pays = trim($_POST['pays']);

    try {
        // 1. Gérer l'upload de la photo si présente
        $photo_sql = "";
        $params = [$nom, $prenom, $email, $telephone, $pays];

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['photo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $new_name = "user_" . $user_id . "_" . time() . "." . $ext;
                $target = "uploads/" . $new_name;

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                    $photo_sql = ", photo = ?";
                    $params[] = $new_name;
                }
            }
        }

        // 2. Préparer et exécuter la requête SQL
        $params[] = $user_id; 
        $sql = "UPDATE utilisateur SET nom = ?, prenom = ?, email = ?, telephone = ?, pays = ? $photo_sql WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // --- LA CORRECTION EST ICI ---
        header("Location: parametre.php?msg=Profil mis à jour avec succès !");
        exit();

    } catch (PDOException $e) {
        error_log($e->getMessage());
        // --- ET ICI AUSSI POUR LES ERREURS ---
        header("Location: parametre.php?error=Erreur lors de la mise à jour.");
        exit();
    }
}
?>
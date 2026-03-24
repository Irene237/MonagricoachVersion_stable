<?php
// Démarrer la session pour accéder à l'ID de l'utilisateur
session_start();

// Vérifier si l'utilisateur est connecté et si l'ID est bien reçu
if (!isset($_SESSION['user_id']) || !isset($_POST["id"])) {
    header("Location: liste_taches.php?status=error_auth");
    exit();
}

require_once 'db.php'; // Inclut le fichier de connexion PDO

$id_tache = (int) $_POST["id"];
$userId = $_SESSION['user_id'];

try {
    // Requête DELETE sécurisée : supprime la tâche SEULEMENT si elle appartient à l'utilisateur
    $sql = "DELETE FROM tache WHERE id = :id_tache AND id_agriculteur = :userId";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_tache', $id_tache, PDO::PARAM_INT);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    // Redirection après succès (ou si la tâche n'a pas été trouvée, ce qui est acceptable pour une suppression)
    // On ajoute un paramètre de statut pour afficher un message dans liste_taches.php
    header("Location: liste_taches.php?status=deleted");
    exit();

} catch (PDOException $e) {
    // Enregistrement de l'erreur pour le débogage
    error_log("Erreur PDO suppression tâche: " . $e->getMessage());
    
    // Redirection avec un message d'erreur
    header("Location: liste_taches.php?status=error_db");
    exit();
}
?>
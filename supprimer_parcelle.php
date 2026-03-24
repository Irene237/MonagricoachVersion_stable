<?php
session_start();
require_once 'db.php';

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $id = intval($_GET['id']);
    $userId = $_SESSION['user_id'];

    try {
        // Sécurité : on vérifie que la parcelle appartient bien à l'utilisateur
        $sql = "DELETE FROM parcelle WHERE id = ? AND id_agriculteur_proprietaire = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $userId]);

        header("Location: liste_parcelle.php?msg=Parcelle supprimée avec succès");
    } catch (PDOException $e) {
        header("Location: liste_parcelle.php?err=Erreur lors de la suppression");
    }
} else {
    header("Location: liste_parcelle.php");
}
exit();
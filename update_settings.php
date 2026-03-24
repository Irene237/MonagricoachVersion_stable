<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $nouvelle_langue = $_POST['langue']; // 'fr' ou 'en'

    try {
        // 1. Mettre à jour la base de données pour que le choix reste au prochain login
        $sql = "UPDATE utilisateur SET langue = :langue WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':langue' => $nouvelle_langue,
            ':id' => $user_id
        ]);

        // 2. TRÈS IMPORTANT : Mettre à jour la session immédiatement
        $_SESSION['lang'] = $nouvelle_langue;

        // Rediriger vers les paramètres avec un message de succès
        header("Location: parametre.php?msg=Paramètres mis à jour / Settings updated");
        exit();

    } catch (PDOException $e) {
        header("Location: parametre.php?error=Erreur lors de la mise à jour");
        exit();
    }
}
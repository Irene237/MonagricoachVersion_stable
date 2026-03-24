<?php
// Démarrer la session pour accéder aux informations de l'utilisateur (user_id)
session_start();

// Inclure le fichier de connexion à la base de données
// ASSUREZ-VOUS que 'db.php' est correctement configuré et contient l'objet $pdo.
require_once 'db.php';

// 1. VÉRIFICATION DE SÉCURITÉ : Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. VÉRIFICATION DE LA REQUÊTE : S'assurer que la requête est de type POST et contient l'ID de la tâche
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    
    $tacheId = $_POST['id'];
    $userId = $_SESSION['user_id'];
    
    try {
        // 3. PRÉPARATION DE LA REQUÊTE : Mettre à jour le statut dans la table 'tache'
        $sql = "UPDATE tache 
                SET statut = 'Terminée' 
                WHERE id = :tacheId AND id_agriculteur = :userId";
                
        $stmt = $pdo->prepare($sql);
        
        // Liaison des paramètres
        $stmt->bindParam(':tacheId', $tacheId, PDO::PARAM_INT);
        // SECURITÉ : On vérifie l'ID de l'agriculteur pour s'assurer qu'il ne termine que SES tâches.
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        
        // 4. EXÉCUTION
        if ($stmt->execute()) {
            // Succès : Redirection vers la liste des tâches avec un message de succès
            header("Location: liste_taches.php?status=completed");
            exit();
        } else {
            // Échec de l'exécution (bien que la requête ait été préparée)
            error_log("Échec de la mise à jour du statut de la tâche ID: $tacheId");
            header("Location: liste_taches.php?status=error&message=update_failed");
            exit();
        }

    } catch (PDOException $e) {
        // Erreur de base de données
        error_log("Erreur PDO lors de la complétion de la tâche: " . $e->getMessage());
        header("Location: liste_taches.php?status=error&message=db_error");
        exit();
    }
} else {
    // Mauvaise requête (pas de POST ou ID manquant)
    header("Location: liste_taches.php?status=error&message=invalid_request");
    exit();
}
?>
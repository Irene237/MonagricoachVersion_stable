<?php
// Démarrer la session pour la sécurité (vérification de l'utilisateur si nécessaire)
session_start();

// Inclure le fichier de connexion à la base de données
require_once 'db.php'; 

// --- Récupération et sécurisation des données POST ---

// Assurez-vous que l'ID de la tâche à modifier est présent
$id_tache = isset($_POST["id"]) ? (int) $_POST["id"] : 0;

// Récupérer les autres champs de la tâche
$titre = $_POST['titre'] ?? '';
$statut = $_POST['statut'] ?? '';
$priorite = $_POST['priorite'] ?? '';

// Les champs date/heure nécessitent un traitement pour s'assurer qu'ils sont au bon format (Y-m-d H:i:s)
// L'input HTML 'datetime-local' envoie 'YYYY-MM-DDTHH:MM', nous devons remplacer 'T' par un espace et ajouter ':00'
$date_debut_raw = $_POST['date_debut'] ?? '';
$date_fin_raw = $_POST['date_fin'] ?? '';

$date_debut = str_replace('T', ' ', $date_debut_raw) . ':00';
$date_fin = str_replace('T', ' ', $date_fin_raw) . ':00';

// L'ID de l'agriculteur ne devrait pas être modifiable par le formulaire, mais est nécessaire pour la sécurité
$id_agriculteur = isset($_POST['id_agriculteur']) ? (int) $_POST['id_agriculteur'] : 0; 


// --- Validation et Exécution de la Mise à Jour ---

// Vérification minimale des données critiques
if ($id_tache === 0 || empty($titre) || empty($statut)) {
    error_log("Tentative de mise à jour de tâche échouée : ID manquant ou données incomplètes.");
    header('location:liste_taches.php?status=error_data');
    exit();
}


try {
    // Requête SQL pour mettre à jour la tâche
    $sql = "UPDATE tache SET 
                titre = :titre,
                statut = :statut,
                priorite = :priorite,
                date_debut = :date_debut,
                date_fin = :date_fin,
                id_agriculteur = :id_agriculteur
            WHERE id = :id_tache";

    $stmt = $pdo->prepare($sql);

    // Liaison des paramètres (bind)
    $stmt->bindParam(':titre', $titre);
    $stmt->bindParam(':statut', $statut);
    $stmt->bindParam(':priorite', $priorite);
    $stmt->bindParam(':date_debut', $date_debut);
    $stmt->bindParam(':date_fin', $date_fin);
    $stmt->bindParam(':id_agriculteur', $id_agriculteur, PDO::PARAM_INT);
    $stmt->bindParam(':id_tache', $id_tache, PDO::PARAM_INT);

    $stmt->execute();
    
    // Redirection vers la liste des tâches avec un message de succès
    header('location:liste_taches.php?status=updated');
    exit();
    
} catch(PDOException $e) {
    error_log("Erreur PDO lors de la mise à jour de la tâche ID {$id_tache}: " . $e->getMessage());
    // Redirection vers la liste avec un message d'erreur
    header('location:liste_taches.php?status=error_db');
    exit();
}
?>
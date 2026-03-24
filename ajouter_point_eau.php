<?php
// Démarrage de la session pour identifier l'agriculteur connecté
session_start();
// Connexion à la base de données
require_once 'db.php';

// Vérification de la session et de la méthode d'envoi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    
    // Récupération et sécurisation des données du formulaire
    $userId = $_SESSION['user_id'];
    $nom = htmlspecialchars($_POST['nom']);
    $lat = filter_var($_POST['latitude'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $lon = filter_var($_POST['longitude'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $type = htmlspecialchars($_POST['type_source']);

    try {
        // Préparation de la requête SQL pour éviter les injections SQL
        $sql = "INSERT INTO points_eau (nom, latitude, longitude, type_source, id_agriculteur) 
                VALUES (:nom, :lat, :lon, :type, :userId)";
        
        $stmt = $pdo->prepare($sql);
        
        // Exécution avec les paramètres
        $stmt->execute([
            ':nom' => $nom,
            ':lat' => $lat,
            ':lon' => $lon,
            ':type' => $type,
            ':userId' => $userId
        ]);

        // Redirection vers la carte après l'ajout réussi
        header("Location: carte_points_eau.php?status=success");
        exit();

    } catch (PDOException $e) {
        // En cas d'erreur, on affiche un message (utile pour le débogage de ton BTS)
        die("Erreur lors de l'enregistrement du point d'eau : " . $e->getMessage());
    }
} else {
    // Si on tente d'accéder au fichier sans formulaire ou session
    header("Location: liste_parcelle.php");
    exit();
}
?>
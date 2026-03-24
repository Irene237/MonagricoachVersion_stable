<?php
// Démarre la session au tout début du script pour accéder à l'ID de l'utilisateur
session_start();

// Définit le type de contenu de la réponse comme JSON
header('Content-Type: application/json');

// Inclut le fichier de connexion à la base de données
require_once 'db.php';

// Vérifie si l'utilisateur est connecté pour des raisons de sécurité
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Utilisateur non authentifié.']);
    exit();
}

try {
    // Récupère l'ID de l'utilisateur connecté
    $user_id = $_SESSION['user_id'];

    // Prépare une requête SQL avec la clause WHERE pour filtrer par l'utilisateur connecté
    // On trie par l'année et le mois pour avoir un ordre chronologique correct
    $sql = "SELECT mois, valeur_production FROM rendement_mens WHERE id_agriculteur = ? ORDER BY annee ASC";

    // Exécute la requête avec l'ID de l'utilisateur comme paramètre
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vérifie si des données ont été trouvées
    if ($data) {
        // Envoie les données au format JSON
        echo json_encode($data);
    } else {
        // Si aucune donnée n'est trouvée pour cet agriculteur, envoie un tableau vide
        echo json_encode([]);
    }

} catch (PDOException $e) {
    // En cas d'erreur de base de données, enregistre l'erreur et envoie un message générique
    error_log("Erreur de base de données: " . $e->getMessage());
    echo json_encode(['error' => 'Une erreur est survenue lors de la récupération des données.']);
}
?>
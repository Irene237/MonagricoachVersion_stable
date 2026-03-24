<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Erreur : Session expirée ou utilisateur non connecté.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $nom = $_POST['nom'] ?? 'Point sans nom';
    $lat = $_POST['lat'] ?? null;
    $lon = $_POST['lon'] ?? null; 
    $type = $_POST['type'] ?? 'puits'; 

    if ($lat && $lon) {
        try {
            $sql = "INSERT INTO points_eau (nom, latitude, longitude, type_source, id_agriculteur) 
                    VALUES (:nom, :lat, :lon, :type, :userId)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nom'    => $nom,
                'lat'    => $lat,
                'lon'    => $lon,
                'type'   => $type,
                'userId' => $userId
            ]);

            header("Location: " . $_SERVER['HTTP_REFERER']); // Retourne à la carte
            exit();
        } catch (PDOException $e) {
            die("Erreur SQL : " . $e->getMessage());
        }
    }
}
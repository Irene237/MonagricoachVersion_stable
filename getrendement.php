<?php
session_start();
header('content-type: application/json');
require_once 'db.php';

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];

    // Requête SQL pour récupérer les données de l'utilisateur connecté seulement
    $sql = "SELECT p.id, c.nom_commun, r.date_re, r.quantite, r.cout
            FROM plantation p
            INNER JOIN culture c ON p.id_culture = c.id
            LEFT JOIN observation r ON p.id = r.id_plantation
            WHERE p.id_agriculteur = :user_id
            ORDER BY r.date_re ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll();

    $labels = [];
    $quantites = [];
    $costs = [];

    foreach ($data as $row) {
        // N'ajoute des points de données que s'il y a une date d'observation
        if (!empty($row['date_re'])) {
            $labels[] = $row['nom_commun'] . ' - ' . date('d/m/y', strtotime($row['date_re']));
            $quantites[] = (float)$row['quantite'];
            $costs[] = (float)$row['cout'];
        }
    }

    $chartData = [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Quantité (tonnes)',
                'data' => $quantites,
                'backgroundColor' => 'rgba(54,162,235,0.5)',
                'borderColor' => 'rgba(54,162,235,1)',
                'borderWidth' => 1,
                'yAxisID' => 'y1',
            ],
            [
                'label' => 'Coûts (FCFA)',
                'data' => $costs,
                'backgroundColor' => 'rgba(255,99,132,0.5)',
                'borderColor' => 'rgba(255,99,132,1)',
                'borderWidth' => 2,
                'type' => 'line',
                'yAxisID' => 'y2',
            ]
        ]
    ];
    echo json_encode($chartData);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erreur:" . $e->getMessage());
    echo json_encode(['error' => 'Une erreur est survenue: ' . $e->getMessage()]);
}
?>
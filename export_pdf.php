<?php

// ------------------------------------------------------------------
// 1. Initialisation et configuration (FPDF)
// ------------------------------------------------------------------

// Utilisation du chemin absolu pour FPDF
require(__DIR__ . '/fpdf186/fpdf.php');

// Inclure votre fichier de connexion a la base de donnees
require_once 'db.php'; 

// Demarrer la session
session_start();

// Verifier la connexion utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ------------------------------------------------------------------
// 2. Fonction utilitaire pour les couleurs
// ------------------------------------------------------------------

function determiner_couleur_rendement_fpdf($actuel, $dernier) {
    $actuel = floatval($actuel);
    $dernier = floatval($dernier);

    if ($actuel > $dernier) {
        return array(209, 250, 229); // Vert pale
    } elseif ($actuel < $dernier) {
        return array(254, 226, 226); // Rouge pale
    } else {
        return array(243, 244, 246); // Gris pale
    }
}

// ------------------------------------------------------------------
// 3. Recuperation des donnees (LOGIQUE FILTRÉE)
// ------------------------------------------------------------------

$id_agriculteur = $_SESSION['user_id'];
$rens = [];
$total_rendement = '0,00';
$nom_agriculteur = $_SESSION['user_name'] ?? 'Utilisateur Inconnu'; 

// Récupération des filtres envoyés par liste_re.php
$search = isset($_GET['s']) ? $_GET['s'] : '';
$statut_filtre = isset($_GET['statut']) ? $_GET['statut'] : '';
$tendance = isset($_GET['tendance']) ? $_GET['tendance'] : '';

try {
    // Construction de la clause WHERE dynamique
    $conditions = ["r.id_agriculteur = :id_agriculteur"];
    $params = [':id_agriculteur' => $id_agriculteur];

    if (!empty($search)) {
        $conditions[] = "(p.statut_plantation LIKE :search OR r.date_re LIKE :search OR u.nom LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    if (!empty($statut_filtre)) {
        $conditions[] = "p.statut_plantation = :statut";
        $params[':statut'] = $statut_filtre;
    }

    if ($tendance === 'rendement-meilleur') {
        $conditions[] = "r.rendement_nets_actuels > r.rendement_nets_an_dernier";
    } elseif ($tendance === 'rendement-moins-bon') {
        $conditions[] = "r.rendement_nets_actuels < r.rendement_nets_an_dernier";
    }

    $whereClause = implode(" AND ", $conditions);

    // 1. Requête principale filtrée
    $sql = "SELECT 
                r.id, 
                r.date_re,
                r.cout, 
                r.quantite, 
                r.rendement, 
                r.rendement_nets_actuels, 
                r.rendement_nets_an_dernier, 
                p.statut_plantation AS statut_plantation,
                u.nom AS nom_agriculteur 
            FROM observation AS r 
            JOIN plantation AS p ON r.id_plantation = p.id 
            JOIN utilisateur AS u ON r.id_agriculteur = u.id 
            WHERE $whereClause 
            ORDER BY r.date_re DESC"; 
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($rens)) {
        $nom_agriculteur = $rens[0]['nom_agriculteur'];
    }

    // 2. Calcul du total filtré
    $sql_total = "SELECT SUM(r.rendement) AS total_rendement FROM observation AS r 
                  JOIN plantation AS p ON r.id_plantation = p.id
                  JOIN utilisateur AS u ON r.id_agriculteur = u.id
                  WHERE $whereClause";
    
    $stmt_total = $pdo->prepare($sql_total);
    $stmt_total->execute($params);
    $total_result = $stmt_total->fetch(PDO::FETCH_ASSOC);

    $total = $total_result['total_rendement'];
    if($total !== null){
        $total_rendement = number_format(floatval($total), 2, ',', ' '); 
    }

} catch (PDOException $e) {
    die("Erreur de base de donnees : " . $e->getMessage());
}

// ------------------------------------------------------------------
// 4. Generation du PDF (Structure originale conservée)
// ------------------------------------------------------------------

$col_widths = [28, 38, 25, 38, 38, 38, 30, 22]; 
$header = ['Date', 'Cout (FCFA)', 'Quantite', 'Rendement (T)', 'Rdt Net Actuel', 'Rdt Net An Dernier', 'Statut Plantation', 'Agriculteur'];

$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14); 

// Header du rapport
$pdf->SetTextColor(6, 189, 189); 
$pdf->Cell(277, 10, utf8_decode('Rapport de Rendement'), 0, 1, 'L');
$pdf->Ln(2);
$pdf->SetTextColor(0, 0, 0); 
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(277, 6, utf8_decode('Généré pour l\'Agriculteur: ') . utf8_decode($nom_agriculteur), 0, 1, 'L');
$pdf->Cell(277, 6, utf8_decode('Date d\'exportation: ') . date('d/m/Y H:i:s'), 0, 1, 'L');
$pdf->Ln(5);

// Header du tableau
$pdf->SetFillColor(240, 248, 255); 
$pdf->SetTextColor(12, 180, 180); 
$pdf->SetFont('Arial', 'B', 9);

for ($i = 0; $i < count($header); $i++) {
    $align = ($i >= 1 && $i <= 3) ? 'R' : 'C'; 
    $pdf->Cell($col_widths[$i], 7, utf8_decode($header[$i]), 1, 0, $align, true);
}
$pdf->Ln(); 

// Contenu du tableau
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(0, 0, 0); 

if (count($rens) > 0) {
    foreach ($rens as $ren) {
        $actuel = (float)$ren['rendement_nets_actuels'];
        $dernier = (float)$ren['rendement_nets_an_dernier'];
        $color_actuel = determiner_couleur_rendement_fpdf($actuel, $dernier);
        $color_dernier = array(243, 244, 246); 

        $pdf->Cell($col_widths[0], 7, utf8_decode($ren['date_re']), 1, 0, 'L');
        $pdf->Cell($col_widths[1], 7, number_format((float)$ren['cout'], 2, ',', ' '), 1, 0, 'R');
        $pdf->Cell($col_widths[2], 7, number_format((float)$ren['quantite'], 2, ',', ' '), 1, 0, 'R');
        $pdf->Cell($col_widths[3], 7, number_format((float)$ren['rendement'], 2, ',', ' '), 1, 0, 'R');
        
        $pdf->SetFillColor($color_actuel[0], $color_actuel[1], $color_actuel[2]);
        $pdf->Cell($col_widths[4], 7, number_format($actuel, 2, ',', ' '), 1, 0, 'C', true);
        
        $pdf->SetFillColor($color_dernier[0], $color_dernier[1], $color_dernier[2]);
        $pdf->Cell($col_widths[5], 7, number_format($dernier, 2, ',', ' '), 1, 0, 'C', true);
        
        $pdf->SetFillColor(255, 255, 255); 
        $pdf->Cell($col_widths[6], 7, utf8_decode($ren['statut_plantation']), 1, 0, 'L');
        $pdf->Cell($col_widths[7], 7, utf8_decode($ren['nom_agriculteur']), 1, 0, 'L');
        $pdf->Ln(); 
    }
} else {
    $pdf->Cell(277, 7, utf8_decode('Aucun rendement trouvé pour cette recherche.'), 1, 1, 'C');
}

// Affichage du Total
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 247, 247); 
$pdf->SetDrawColor(6, 189, 189); 

$x_start = 140; 
$width_box = 137;

$pdf->SetX($x_start);
$pdf->Cell($width_box, 10, utf8_decode('Total du Rendement: ') . $total_rendement . ' T', 1, 1, 'R', true);

// Sortie du fichier
$filename = 'Rapport_Rendement_' . date('Ymd_His') . '.pdf';
$pdf->Output('I', $filename); 
?>
<?php
// Fichier : export_list_pdf.php
// Ce script exporte la liste des rapports en PDF en utilisant des DONNÉES STATIQUES.

session_start();

// --- 1. SÉCURITÉ ET VÉRIFICATION ---
if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    // Si la session n'est pas démarrée, nous devons au moins simuler un utilisateur 
    // ou arrêter, car la page rapport.php nécessite une session.
    // Si vous testez ce script directement, vous pouvez commenter les lignes ci-dessous 
    // mais dans l'architecture finale, la connexion est nécessaire.
    // header("Location: connexion.php");
    // exit();
    
    // Si la session n'est pas active, on simule l'utilisateur pour éviter l'erreur
    $_SESSION['user_email'] = 'conseiller.demo@agricoach.com';
}

// **IMPORTANT : Inclure la librairie FPDF**
// L'erreur précédente était ici : assurez-vous que ce chemin est correct.
// Utilisez le chemin adapté à votre configuration (par exemple : 'fpdf.php' si au même niveau)
require('fpdf186/fpdf.php');

// 2. DONNÉES STATIQUES DE DÉMONSTRATION (Remplacement de la BDD)
$reports_simules = [
    ['rapport_id' => 1, 'nom_agriculteur' => 'Romari rin', 'type_rapport' => 'Rapport de Rendement', 'date_creation' => '2025-12-10', 'statut' => 'Généré'],
    ['rapport_id' => 2, 'nom_agriculteur' => 'helsinky james', 'type_rapport' => 'Bilan de Fertilisation', 'date_creation' => '2025-11-28', 'statut' => 'Archivé'],
    ['rapport_id' => 3, 'nom_agriculteur' => 'riu tyty', 'type_rapport' => 'Diagnostic Parasitaire', 'date_creation' => '2025-11-15', 'statut' => 'Généré'],
    ['rapport_id' => 4, 'nom_agriculteur' => 'riso tyty', 'type_rapport' => 'Bilan de Fertilisation', 'date_creation' => '2025-11-01', 'statut' => 'En Cours'],
];

// 3. PRÉPARATION DES COLONNES POUR FPDF
$header = ['ID Rapport', 'Agriculteur', 'Type de Rapport', 'Date de Génération', 'Statut'];
// Largeurs des colonnes (total doit être 190 pour un A4 standard Portrait)
$w = [25, 60, 45, 30, 30]; 

// 4. CLASSE PDF PERSONNALISÉE
class PDF_List extends FPDF
{
    // En-tête (Header)
    function Header()
    {
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(0, 128, 128); 
        
        $this->Cell(0, 10, utf8_decode('Liste d\'Historique des Rapports'), 0, 1, 'C');
        
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, utf8_decode('Généré par : ' . ($_SESSION['user_email'] ?? 'Démo') . ' | Le : ' . date('d/m/Y H:i:s')), 0, 1, 'C');

        $this->Ln(5);
    }

    // Pied de page (Footer)
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, utf8_decode('Page ') . $this->PageNo() . '/{nb} - MonAgriCoach', 0, 0, 'C');
    }

    // Fonction de tableau pour afficher les données
    function ReportTable($header, $data, $w)
    {
        // En-tête du tableau
        $this->SetFillColor(240, 240, 240); 
        $this->SetTextColor(0, 128, 128); 
        $this->SetDrawColor(0, 0, 0); 
        $this->SetLineWidth(.3);
        $this->SetFont('Arial', 'B', 10);

        for($i=0; $i<count($header); $i++)
            $this->Cell($w[$i], 7, utf8_decode($header[$i]), 1, 0, 'C', true);
        $this->Ln();

        // Corps du tableau
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0);
        $this->SetFont('Arial', '', 10);
        
        $fill = false;
        foreach($data as $row)
        {
            // Les données sont mappées à partir du tableau statique
            $data_row = [
                $row['rapport_id'], 
                utf8_decode($row['nom_agriculteur']), 
                utf8_decode($row['type_rapport']), 
                $row['date_creation'], 
                utf8_decode($row['statut'])
            ];
            
            // Alternance des couleurs de fond
            $this->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
            
            // Affichage des cellules
            for($i=0; $i<count($data_row); $i++) {
                $align = ($i == 0 || $i >= 3) ? 'C' : 'L'; 
                $this->Cell($w[$i], 6, $data_row[$i], 'LR', 0, $align, $fill);
            }
            $this->Ln();
            $fill = !$fill;
        }
        // Ligne de fermeture
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}

// 5. CRÉATION DU PDF
$pdf = new PDF_List();
$pdf->AliasNbPages(); 
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Ajout du tableau avec les données statiques
$pdf->ReportTable($header, $reports_simules, $w);

// 6. Sortie
$filename_pdf = "Liste_Rapports_MonAgriCoach_" . date('Ymd_His') . ".pdf";
$pdf->Output('D', $filename_pdf); // 'D' force le téléchargement
exit();
?>
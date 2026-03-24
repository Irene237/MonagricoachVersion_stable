<?php
// Fichier : export_report_detail.php
session_start();

// **IMPORTANT : Inclure la librairie FPDF**
require('fpdf186/fpdf.php');

// --- 1. SÉCURITÉ ET VÉRIFICATION ---
if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    // Si la session n'est pas démarrée, on redirige ou on quitte
    exit("Accès non autorisé.");
}

$rapport_id = $_GET['id'] ?? null;
if (!$rapport_id) {
    exit("Erreur : Identifiant de rapport manquant.");
}

// 2. DONNÉES STATIQUES DE DÉMONSTRATION (Récupération de la BDD simulée)
// REMARQUE : Dans une application réelle, cette section ferait une requête JOIN.
$simulated_data = [
    // Rapport 1 (Rendement)
    1 => [
        'id' => 1,
        'agriculteur' => 'Romari rin (ID: 999)',
        'type' => 'Rapport de Rendement',
        'conseiller' => 'Consultant Démo',
        'date_creation' => '10 Déc. 2025',
        'statut' => 'Généré',
        'periode' => '01/01/2025 au 01/12/2025',
        'resume' => "Bilan positif. Le rendement net est supérieur de 12% à l'année précédente grâce à l'optimisation de la fertilisation. Cependant, une surveillance est nécessaire dans la parcelle Ouest (risque de mildiou). L'analyse des données satellites a permis d'identifier des zones de stress hydrique précoces qui ont été corrigées à temps.",
        'donnees_cles' => [
            'Rendement Total' => '54.2 T',
            'Marge Nette' => '12 500 000 FCFA',
            'Croissance / An-1' => '+12%',
            'Statut Plantation' => 'En Récolte'
        ],
        'recommandations' => [
            "Augmenter l'irrigation dans la parcelle Ouest de 10% pour la prochaine saison.",
            "Utiliser l'engrais à libération lente recommandé pour réduire le lessivage.",
            "Planifier un traitement préventif contre le mildiou en début de saison humide."
        ]
    ],
    // Rapport 2 (Fertilisation)
    2 => [
        'id' => 2,
        'agriculteur' => 'helsinky james (ID: 998)',
        'type' => 'Bilan de Fertilisation',
        'conseiller' => 'Consultant Démo',
        'date_creation' => '28 Nov. 2025',
        'statut' => 'Archivé',
        'periode' => 'Période 2025/2026',
        'resume' => "Analyse des sols P1 et P3 terminée. Un déficit important en Potasse (K) est noté. Recommandation d'appliquer l'engrais 14-7-21 avant le début de la saison des pluies. L'acidité du sol (pH 6.2) est stable mais devra être surveillée l'an prochain.",
        'donnees_cles' => [
            'Déficit Principal' => 'Potasse (K)',
            'pH Moyen' => '6.2',
            'Dernier Amendement' => '2025-10-15',
            'Recommandation' => 'Engrais 14-7-21'
        ],
        'recommandations' => [
            "Appliquer 200 kg/ha de l'engrais 14-7-21, divisés en deux applications.",
            "Effectuer une analyse foliaire trois mois après l'application principale.",
            "Considérer un chaulage léger pour maintenir le pH dans la zone optimale (6.0-6.5)."
        ]
    ]
];

$rapport_details = $simulated_data[$rapport_id] ?? null;

if (!$rapport_details) {
    exit("Rapport non trouvé avec l'ID : " . htmlspecialchars($rapport_id));
}

// 3. CLASSE PDF PERSONNALISÉE POUR LE RAPPORT DÉTAILLÉ
class PDF_Report extends FPDF
{
    protected $reportData;

    function setReportData($data) {
        $this->reportData = $data;
    }

    // En-tête (Header)
    function Header()
    {
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(0, 128, 128); // Couleur principale (Teal)
        
        $titre = $this->reportData['type'] ?? 'Rapport Détaillé';
        
        // Titre Centré
        $this->Cell(0, 10, utf8_decode($titre), 0, 1, 'C');
        
        // Sous-titre
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, utf8_decode('Pour l\'agriculteur : ' . ($this->reportData['agriculteur'] ?? 'N/A')), 0, 1, 'C');

        $this->Ln(5);
        
        // Ligne de séparation
        $this->SetDrawColor(0, 128, 128);
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
    }

    // Pied de page (Footer)
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $date = $this->reportData['date_creation'] ?? date('d/m/Y');
        $this->Cell(0, 10, utf8_decode("Rapport ID {$this->reportData['id']} | Généré le : {$date} | Page ") . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Section Métadonnées et Résumé
    function MetadataAndSummary()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 7, utf8_decode('1. Informations Générales'), 0, 1, 'L');
        $this->Ln(2);

        // -- Affichage des Métadonnées dans un tableau simple --
        $this->SetFillColor(245, 245, 245); // Fond gris clair
        $this->SetTextColor(0);
        $this->SetFont('Arial', '', 10);
        
        $data = [
            'ID Rapport' => $this->reportData['id'],
            'Conseiller' => $this->reportData['conseiller'] ?? 'N/A',
            'Statut' => $this->reportData['statut'] ?? 'N/A',
            'Période' => $this->reportData['periode'] ?? 'N/A',
            'Date Création' => $this->reportData['date_creation'] ?? 'N/A',
        ];

        $w_label = 40;
        $w_value = 55;
        $x_start = 10;
        $x_pos = $x_start;

        $i = 0;
        foreach ($data as $label => $value) {
            $this->SetX($x_pos);
            $this->Cell($w_label, 6, utf8_decode($label . ' :'), 'TLB', 0, 'L', true);
            $this->Cell($w_value, 6, utf8_decode($value), 'TRB', 0, 'L', false);
            
            $i++;
            if ($i % 2 == 0) {
                // Retour à la ligne pour le prochain couple
                $this->Ln(6);
                $x_pos = $x_start;
            } else {
                // Déplacement pour le deuxième couple sur la même ligne
                $x_pos += $w_label + $w_value + 5; // +5 pour l'espace entre les colonnes
            }
        }
        $this->Ln(6); // Assurer un saut de ligne propre

        // -- Résumé --
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 7, utf8_decode('2. Résumé et Analyse'), 0, 1, 'L');
        $this->Ln(2);
        
        $this->SetFont('Arial', '', 10);
        $this->SetFillColor(255, 255, 255);
        // Multicell pour gérer les longs résumés
        $this->MultiCell(0, 5, utf8_decode($this->reportData['resume'] ?? 'Pas de résumé disponible.'), 0, 'L');
        $this->Ln(5);
    }
    
    // Section Données Clés
    function KeyFigures()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 7, utf8_decode('3. Indicateurs Clés'), 0, 1, 'L');
        $this->Ln(2);
        
        $figures = $this->reportData['donnees_cles'] ?? [];
        if (empty($figures)) {
            $this->SetFont('Arial', 'I', 10);
            $this->Cell(0, 5, utf8_decode('Aucune donnée clé détaillée n\'est associée à ce rapport.'), 0, 1, 'L');
            $this->Ln(5);
            return;
        }
        
        $w_label = 70;
        $w_value = 30;
        $x_pos = 10;
        
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(220, 220, 220); // Gris foncé pour l'en-tête du tableau
        
        // En-têtes
        $this->Cell($w_label, 7, utf8_decode('Indicateur'), 1, 0, 'C', true);
        $this->Cell($w_value, 7, utf8_decode('Valeur'), 1, 1, 'C', true);

        $this->SetFont('Arial', '', 10);
        $this->SetFillColor(245, 245, 245);
        $fill = false;
        
        foreach ($figures as $label => $value) {
            $this->SetFillColor($fill ? 255 : 245, $fill ? 255 : 245, $fill ? 255 : 245);
            $this->Cell($w_label, 6, utf8_decode($label), 'LR', 0, 'L', $fill);
            $this->Cell($w_value, 6, utf8_decode($value), 'LR', 1, 'C', $fill);
            $fill = !$fill;
        }
        // Ligne de fermeture
        $this->Cell($w_label + $w_value, 0, '', 'T', 1);
        $this->Ln(5);
    }
    
    // Section Recommandations
    function Recommendations()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 7, utf8_decode('4. Recommandations Spécifiques'), 0, 1, 'L');
        $this->Ln(2);

        $recos = $this->reportData['recommandations'] ?? [];
        if (empty($recos)) {
            $this->SetFont('Arial', 'I', 10);
            $this->Cell(0, 5, utf8_decode('Aucune recommandation associée à ce rapport.'), 0, 1, 'L');
            $this->Ln(5);
            return;
        }
        
        $this->SetFont('Arial', '', 10);
        $i = 1;
        foreach ($recos as $reco) {
            $this->SetFillColor(240, 255, 255); // Fond Cyan très clair
            $this->Rect(10, $this->GetY(), 180, 7, 'F');
            $this->SetX(12);
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(5, 7, utf8_decode("$i."), 0, 0, 'L');
            $this->SetFont('Arial', '', 10);
            $this->MultiCell(173, 7, utf8_decode($reco), 0, 'L');
            $i++;
            $this->Ln(3);
        }
        $this->Ln(5);
    }
}

// 4. CRÉATION DU PDF
$pdf = new PDF_Report();
$pdf->setReportData($rapport_details);
$pdf->AliasNbPages(); // Active le {nb} pour le numéro total de pages
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10); // Définir les marges

// Construction du contenu du rapport
$pdf->MetadataAndSummary();
$pdf->KeyFigures();
$pdf->Recommendations();
// Note: Les graphiques et tableaux complexes nécessitent plus de code FPDF ou une librairie tierce.

// 5. Sortie
$filename_pdf = "Rapport_{$rapport_details['type']}_ID{$rapport_id}_" . date('Ymd') . ".pdf";
$pdf->Output('D', utf8_decode($filename_pdf)); // 'D' force le téléchargement
exit();
?>
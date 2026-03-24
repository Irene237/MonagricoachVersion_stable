<?php
// Inclut le fichier de connexion à la base de données (db.php)
require_once 'db.php'; 

// Démarre la session et vérifie la connexion (Sécurité essentielle)
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id_agriculteur = $_SESSION['user_id'];
$rens = [];
$total_rendement = '0,00';
$errorMessage = null;

/**
 * Détermine la classe CSS (couleur) pour le rendement net actuel basé sur la comparaison avec l'année dernière.
 */
function determiner_couleur_rendement($actuel, $dernier) {
    $classes = ['actuel' => 'rendement-neutre', 'dernier' => 'rendement-neutre'];
    if ($actuel > $dernier) {
        $classes['actuel'] = 'rendement-meilleur'; 
    } elseif ($actuel < $dernier) {
        $classes['actuel'] = 'rendement-moins-bon'; 
    }
    return $classes;
}

// ------------------------------------------------------------------
// 1. LOGIQUE DE RECHERCHE ET DE FILTRE
// ------------------------------------------------------------------

$conditions = ["u.type_utilisateur = 'agriculteur'", "r.id_agriculteur = :id_agriculteur"];
$params = [':id_agriculteur' => $id_agriculteur];

// Construction de la requête
$query_string = "SELECT 
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
                WHERE " . implode(" AND ", $conditions);

// Modification de la logique de recherche pour inclure spécifiquement les rendements et le statut
if (!empty($_GET['recherche'])) {
    $termeRecherche = '%' . $_GET['recherche'] . '%';
    $query_string .= " AND (
                        p.statut_plantation LIKE :termeRecherche OR 
                        r.rendement_nets_actuels LIKE :termeRecherche OR
                        r.rendement_nets_an_dernier LIKE :termeRecherche
                      )";
    $params[':termeRecherche'] = $termeRecherche;
}

if (!empty($_GET['date_re'])) {
    $query_string .= " AND r.date_re = :date_re";
    $params[':date_re'] = $_GET['date_re'];
}

$query_string .= " ORDER BY r.date_re DESC";

try {
    $stmt = $pdo->prepare($query_string);
    $stmt->execute($params);
    $rens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. CALCUL DU TOTAL
    $total = array_sum(array_column($rens, 'rendement'));
    if ($total !== null) {
        $total_rendement = number_format($total, 2, ',', ' ');
    }
} catch (PDOException $e) {
    error_log("Erreur : " . $e->getMessage());
    $errorMessage = "Erreur de base de données. Détail : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de Recherche Rendement | MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* (Le CSS reste identique au vôtre) */
        :root {
            --color-primary-emerald: #06bdbdff; 
            --color-primary-dark: #0cb4b4ff; 
            --color-secondary-gold: #FF8C00; 
            --color-secondary-gold-hover: #E37D00;
            --color-accent-danger: #ef4444; 
            --color-accent-success: #10B981; 
            --color-heading: #111827; 
            --color-card-bg: #FFFFFF; 
            --color-light-bg: #F5F8F5; 
            --color-text-dark: #374151;
            --color-text-medium: #4B5563; 
            --color-border: #E5E7EB;
            --color-border-light: #F3F4F6; 
            --color-disconnect-bg: var(--color-secondary-gold);
            --color-badge-success-text: #059669; 
            --color-badge-success-bg: #D1FAE5; 
            --color-badge-danger-text: #DC2626; 
            --color-badge-danger-bg: #FEE2E2; 
            --color-badge-neutral-text: var(--color-text-medium);
            --color-badge-neutral-bg: var(--color-border-light);
            --font-main: 'Poppins', sans-serif;
            --font-heading: 'Montserrat', sans-serif;
            --sidebar-width: 260px; 
        }

        body { font-family: var(--font-main); margin: 0; padding: 0; background-color: var(--color-light-bg); display: flex; min-height: 100vh; overflow-x: hidden; }
        .sidebar { width: var(--sidebar-width); background-color: var(--color-card-bg); padding: 25px 0; height: 100vh; position: fixed; top: 0; left: 0; box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; z-index: 1000; }
        .sidebar .logo { font-family: var(--font-heading); color: var(--color-primary-emerald); font-size: 20px; font-weight: 800; text-align: center; padding: 0 20px 40px 20px; text-decoration : none; }
        .sidebar .logo i { color: var(--color-primary-emerald); font-size: 28px; margin-right: 5px; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; flex-grow: 1; display: flex; flex-direction: column; }
        .sidebar li a { display: flex; align-items: center; padding: 12px 25px; color: var(--color-text-dark); text-decoration: none; font-size: 15px; font-weight: 500; transition: all 0.2s ease-in-out; border-left: 0px solid transparent; }
        .sidebar li a i { margin-right: 15px; font-size: 18px; color: var(--color-text-medium); width: 25px; text-align: center; font-style: normal; }
        .sidebar li a:hover { background-color: #f5f8f8ff; color: var(--color-primary-dark); }
        .sidebar li a[href="liste_re.php"] { background-color: rgba(6, 189, 189, 0.1); color: var(--color-primary-emerald); font-weight: 600; border-left: 5px solid var(--color-primary-emerald); }
        .sidebar li a[href="liste_re.php"] i { color: var(--color-primary-emerald); }
        .btn_lien { margin-top: auto; padding: 25px; }
        .sidebar .btn_lien button { border: none; padding: 0; background: none; width: 100%; }
        .sidebar .btn_lien button a { 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            gap: 10px; 
            background-color: var(--color-disconnect-bg); 
            color: var(--color-card-bg); 
            padding: 12px 20px; 
            border-radius: 8px; 
            font-size: 15px; 
            font-weight: 700; 
            box-shadow: 0 4px 15px rgba(255, 140, 0, 0.4); 
            transition: all 0.3s; 
            text-decoration: none; 
            border-left: none;
        }
        .sidebar .btn_lien button a:hover { background-color: var(--color-secondary-gold-hover); box-shadow: 0 6px 20px rgba(255, 140, 0, 0.6); } 
        
        .main-content { margin-left: var(--sidebar-width); padding: 50px 40px; flex-grow: 1; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 1px solid var(--color-border); }
        
        .headers { display: flex; gap: 15px; } 

        .action-link { 
            padding: 12px 25px; 
            border-radius: 8px; 
            font-weight: 600; 
            font-size: 16px; 
            text-decoration: none; 
            transition: background-color 0.2s ease, box-shadow 0.2s; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
        }

        .add-new-link {
            background-color: var(--color-primary-emerald); 
            color: var(--color-card-bg); 
            box-shadow: 0 4px 12px rgba(6, 189, 189, 0.4); 
        }
        .add-new-link:hover { background-color: var(--color-primary-dark); box-shadow: 0 6px 15px rgba(6, 189, 189, 0.6); }

        .export-pdf-link {
            background-color: var(--color-secondary-gold);
            color: var(--color-card-bg);
            box-shadow: 0 4px 12px rgba(255, 140, 0, 0.4);
        }
        .export-pdf-link:hover { background-color: var(--color-secondary-gold-hover); box-shadow: 0 6px 15px rgba(255, 140, 0, 0.6); }


        .content-container { background: var(--color-card-bg); padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); }
        .total-container { 
            display: flex; 
            justify-content: flex-end; 
            align-items: center; 
            margin-bottom: 30px; 
            padding: 15px 25px; 
            background-color: #e6f7f7; 
            border-left: 5px solid var(--color-primary-emerald); 
            border-radius: 8px; 
            font-size: 18px; 
            font-weight: 700; 
            color: var(--color-primary-dark); 
            box-shadow: 0 2px 8px rgba(6, 189, 189, 0.1);
        }
        .total-container .total-label { margin-right: 15px; font-weight: 500; color: var(--color-text-medium); }
        .total-container .total-value { font-family: var(--font-heading); font-size: 24px; color: var(--color-heading); }

        .search-form { display: flex; gap: 10px; margin-bottom: 30px; }
        .search-form input[type="text"] { padding: 10px 15px; border: 1px solid var(--color-border); border-radius: 8px; flex-grow: 1; font-size: 15px; transition: border-color 0.2s; }
        .search-form input[type="text"]:focus { border-color: var(--color-primary-emerald); outline: none; }
        .search-form button { padding: 10px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 15px; display: flex; align-items: center; gap: 5px; transition: background-color 0.2s; }
        .search-form button[type="submit"] { background-color: var(--color-primary-emerald); color: var(--color-card-bg); }
        .search-form button[type="submit"]:hover { background-color: var(--color-primary-dark); }
        .search-form button[type="button"] { background-color: #6B7280; color: var(--color-card-bg); } 
        .search-form button[type="button"]:hover { background-color: #4B5563; } 
        
        .table-responsive { overflow-x: auto; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            background-color: var(--color-card-bg); 
            border-radius: 12px; 
            overflow: hidden; 
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08); 
        }
        
        thead { 
            background-color: #F8F9FA; 
            border-bottom: 4px solid var(--color-primary-emerald); 
            box-shadow: 0 4px 6px -6px rgba(0, 0, 0, 0.15); 
        }
        
        th { 
            padding: 16px 20px; 
            text-align: left; 
            font-weight: 700; 
            text-transform: uppercase; 
            font-size: 13px; 
            letter-spacing: 0.5px; 
            color: var(--color-primary-dark); 
        }
        
        td { 
            padding: 15px 20px; 
            color: var(--color-text-dark); 
            font-size: 14px; 
            vertical-align: middle; 
            border-bottom: 1px solid #EAEAEA; 
        }
        
        tbody tr:nth-child(even) { background-color: #fcfcfc; }
        tbody tr:nth-child(odd) { background-color: var(--color-card-bg); }
        tbody tr:hover {
            background-color: #f0fafa; 
            transition: background-color 0.2s ease;
        }
        
        td:nth-child(2), td:nth-child(3), td:nth-child(4) { 
            text-align: right;
            font-weight: 500;
            color: var(--color-heading); 
            font-family: 'Poppins', sans-serif; 
        }
        
        th:nth-child(5), th:nth-child(6) { text-align: center; } 
        td:nth-child(5), td:nth-child(6) { text-align: center; } 

        .rendement-cell-wrapper {
            display: flex;
            justify-content: center; 
            width: 100%;
        }

        .rendement-badge {
            display: inline-block;
            padding: 6px 12px; 
            border-radius: 20px; 
            font-weight: 700;
            font-size: 12px;
            text-align: center;
            min-width: 90px; 
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1); 
            border-width: 1px;
            border-style: solid;
        }
        
        .rendement-badge.rendement-meilleur {
            color: var(--color-badge-success-text); 
            background-color: var(--color-badge-success-bg);
            border-color: var(--color-accent-success); 
        }
        
        .rendement-badge.rendement-moins-bon {
            color: var(--color-badge-danger-text); 
            background-color: var(--color-badge-danger-bg);
            border-color: var(--color-accent-danger);
        }
        
        .rendement-badge.rendement-neutre {
            color: var(--color-badge-neutral-text);
            background-color: var(--color-badge-neutral-bg);
            border-color: var(--color-border);
        }
        
        .table-actions { display: flex; gap: 8px; align-items: center; justify-content: center; }
        .table-actions a, .table-actions button {
            width: 30px; height: 30px; 
            border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            transition: all 0.3s; 
            border: none; cursor: pointer; padding: 0; 
            font-size: 14px; 
            color: var(--color-card-bg);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); 
        }
        .table-actions a { background-color: var(--color-primary-emerald); }
        .table-actions a:hover { background-color: var(--color-primary-dark); transform: scale(1.1); }
        .delete-button { background-color: var(--color-accent-danger); }
        .delete-button:hover { background-color: #c52929; transform: scale(1.1); }
    </style>
</head>

<body> 
    <nav class="sidebar">
      <a href="index.php" class="logo">
                <i class="fas fa-leaf"></i> MonAgriCoach
            </a>
        <ul>
             <li><a href="farmer_dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a></li>
            <li> <a href="liste_parcelle.php"><i class="fas fa-map-marker-alt"></i> Parcelles</a></li> 
            <li> <a href="liste_plantation.php"><i class="fas fa-seedling"></i> plantations</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i>  Cultures</a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> Engrais</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i> Messagerie</a></li>
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i>Verser l'engrais</a></li>
            <li> <a href="liste_st_intrant.php"><i class="fas fa-warehouse"></i> stock engrais</a></li>
            <li><a href="liste_re.php"><i class="fas fa-chart-bar"></i> **Rendement**</a></li>
            <li><a href="liste_taches.php"><i class="fas fa-tasks"></i> Tâches</a></li>
        </ul>
        
        <div class="btn_lien">
            <button>
                <a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </button>
        </div>
    </nav>

    <div class="main-content">
        <div class="header">
            <h1>Résultats de Recherche Rendement</h1>
            <div class="headers">
                <a href="export_pdf.php?recherche=<?= urlencode($_GET['recherche'] ?? ''); ?>" class="action-link export-pdf-link" title="Exporter au format PDF"> 
                    <i class="fas fa-file-pdf"></i> Exporter PDF
                </a>
                <a href="graphique_rendement.php" class="action-link add-new-link"> 
                    <i class="fas fa-chart-area"></i> Graphique
                </a>
                <a href="rendement.php" class="action-link add-new-link"> 
                    <i class="fas fa-plus-circle"></i> Ajouter
                </a>
            </div>
        </div>
        
        <div class="content-container">
            <h2>Résultats pour : "<?= htmlspecialchars($_GET['recherche'] ?? 'Tous'); ?>"</h2>
            
            <?php if (isset($errorMessage)): ?>
                <p style="color: var(--color-accent-danger); background-color: #FEE2E2; padding: 15px; border-radius: 8px;"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($errorMessage); ?></p>
            <?php endif; ?>

            <div class="total-container">
                <span class="total-label">Total du Rendement Filtré :</span>
                <span class="total-value"><?= $total_rendement; ?> T</span> 
            </div>

            <form action="recherche_re.php" method="GET" class="search-form">
                <input type="text" name="recherche" placeholder="Rechercher par plantation, Rdt Actuel/Dernier..." value="<?= htmlspecialchars($_GET['recherche'] ?? ''); ?>">
                <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
                <button type="button" onclick="window.location.href='liste_re.php'"><i class="fas fa-redo"></i> Réinitialiser</button>
            </form>
            
            <?php if (count($rens) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th style="text-align: right;">Coût (FCFA)</th>
                                <th style="text-align: right;">Quantité</th>
                                <th style="text-align: right;">Rendement (T)</th>
                                <th style="text-align: center;">Rdt Net Actuel</th>
                                <th style="text-align: center;">Rdt Net An Dernier</th>
                                <th>Plantation</th>
                                <th>Agriculteur</th>
                                <th style="text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($rens as $ren ): 
                                $actuel = (float)$ren['rendement_nets_actuels'];
                                $dernier = (float)$ren['rendement_nets_an_dernier'];
                                $classes = determiner_couleur_rendement($actuel, $dernier);
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($ren['date_re']); ?></td>
                                    <td><?= number_format((float)$ren['cout'], 2, ',', ' '); ?></td>
                                    <td><?= number_format((float)$ren['quantite'], 2, ',', ' '); ?></td>
                                    <td><?= number_format((float)$ren['rendement'], 2, ',', ' '); ?></td>
                                    
                                    <td>
                                        <div class="rendement-cell-wrapper">
                                            <span class="rendement-badge <?= $classes['actuel']; ?>">
                                                <?= number_format($actuel, 2, ',', ' '); ?>
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <div class="rendement-cell-wrapper">
                                            <span class="rendement-badge <?= $classes['dernier']; ?>">
                                                <?= number_format($dernier, 2, ',', ' '); ?>
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <td><?= htmlspecialchars($ren['statut_plantation']); ?></td>
                                    <td><?= htmlspecialchars($ren['nom_agriculteur']); ?></td>
                                    <td class="table-actions">
                                        <a href="modifier_re.php?id=<?= $ren['id']; ?>" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <form action="supprime_re.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce rendement ?');" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $ren['id']; ?>" >
                                            <button type="submit" title="Supprimer" class="delete-button">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr> 
                            <?php endforeach;?>
                        </tbody>
                    </table>
                </div> 
            <?php else:?> 
                <p style="padding: 20px; text-align: center; border: 1px dashed var(--color-border); border-radius: 8px; color: var(--color-text-medium);"><i class="fas fa-info-circle"></i> Aucun rendement trouvé correspondant à vos critères de recherche.</p> 
            <?php endif;?>
        </div> 
    </div>
</body>
</html>
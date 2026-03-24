-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 24 mars 2026 à 09:27
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `agca`
--

-- --------------------------------------------------------

--
-- Structure de la table `application_intrant`
--

CREATE TABLE `application_intrant` (
  `id` int(11) NOT NULL,
  `date_aplication` datetime NOT NULL,
  `quantite_appliquee` decimal(10,0) NOT NULL,
  `unite_appliquee` varchar(45) NOT NULL,
  `methode_application` varchar(255) NOT NULL,
  `notes` text NOT NULL,
  `id_plantation` int(11) NOT NULL,
  `id_intrant` int(11) NOT NULL,
  `id_agriculteur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `application_intrant`
--

INSERT INTO `application_intrant` (`id`, `date_aplication`, `quantite_appliquee`, `unite_appliquee`, `methode_application`, `notes`, `id_plantation`, `id_intrant`, `id_agriculteur`) VALUES
(2, '2025-07-02 00:00:00', 240, 'kg', 'sion', 'en attente de resultat', 4, 3, 2),
(6, '2025-11-09 00:00:00', 28843, 'kg', 'En sion', 'pour une bonne croissance des planatations', 23, 3, 23),
(7, '2025-11-10 00:00:00', 28643, 'kg', 'Pulvirisation par drone', 'J\'observe encore', 23, 3, 23),
(8, '2025-11-10 00:00:00', 199, 'kg', 'Pulvirisation par drone', 'J\'observe encore', 23, 3, 23),
(9, '2025-11-11 00:00:00', 28843, 'kg', 'Pulvirisation par drone', 'rrrrrrrrrr', 23, 3, 23),
(10, '2025-12-03 00:00:00', 38157, '', 'En sion', 'vvvvvvvvvvvvvvv', 25, 3, 23),
(11, '2026-03-08 00:00:00', 44444, '', 'Pulvirisation par drone', '', 6, 5, 23),
(12, '2026-03-12 00:00:00', 40000000, '', 'Pulvirisation par drone', '', 26, 3, 23),
(13, '2026-03-15 00:00:00', 28843, '', 'Pulvirisation par drone', '', 25, 4, 23),
(14, '2026-03-23 00:00:00', 26000, '', 'Pulvirisation par drone', '', 26, 5, 23);

-- --------------------------------------------------------

--
-- Structure de la table `assistance_conseiller`
--

CREATE TABLE `assistance_conseiller` (
  `id` int(11) NOT NULL,
  `date_debut_assistance` date NOT NULL,
  `date_fin_assistance` date NOT NULL,
  `message_conseiller` varchar(255) NOT NULL,
  `id_conseiller` int(11) NOT NULL,
  `id_agriculteur_assiste` int(11) NOT NULL,
  `message_agriculteur` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `assistance_conseiller`
--

INSERT INTO `assistance_conseiller` (`id`, `date_debut_assistance`, `date_fin_assistance`, `message_conseiller`, `id_conseiller`, `id_agriculteur_assiste`, `message_agriculteur`) VALUES
(17, '2025-08-29', '2025-08-15', 'optimise tes ressources', 25, 23, '..'),
(18, '2025-08-29', '2025-08-28', 'optimise tes ressources', 25, 23, '..'),
(19, '2025-08-22', '2025-08-21', 'optimise tes ressources', 25, 23, '..'),
(21, '2025-08-21', '2025-08-31', 'Utilise comme engrais  la  merde de poule', 25, 27, ''),
(22, '2025-09-18', '2025-10-12', '', 41, 23, 'A l\'aide'),
(25, '2025-12-03', '0000-00-00', '', 20, 23, 'bonjour toi'),
(26, '2025-12-03', '0000-00-00', '', 20, 23, 'vvvv'),
(28, '0000-00-00', '2025-12-17', 'bbbbbbbbbbbbbbbbbbbbbbbbbbbb', 25, 23, ''),
(29, '0000-00-00', '2025-12-17', 'bbbbbbbbbbbbbbbbbbbbbbb', 25, 23, ''),
(30, '2026-03-08', '0000-00-00', '', 20, 23, 'bonne fete de la femme a vous ????'),
(31, '2026-03-08', '0000-00-00', '', 25, 23, 'Bonne fete de la femme a vous????'),
(32, '0000-00-00', '2026-03-08', 'hhhhhhhhhh', 25, 23, ''),
(33, '0000-00-00', '2026-03-08', 'Ok', 25, 23, ''),
(34, '0000-00-00', '2026-03-08', 'ok', 25, 23, ''),
(35, '0000-00-00', '2026-03-08', 'merci meilleur a vous', 25, 23, ''),
(36, '2026-03-12', '0000-00-00', '', 20, 23, 'merci'),
(37, '0000-00-00', '2026-03-12', 'daccord', 25, 23, ''),
(38, '2026-03-12', '0000-00-00', '', 25, 23, 'pas de quoi');

-- --------------------------------------------------------

--
-- Structure de la table `culture`
--

CREATE TABLE `culture` (
  `id` int(11) NOT NULL,
  `nom_commun` varchar(255) NOT NULL,
  `cycle_de_vie_jours` varchar(255) NOT NULL,
  `besoins_eau_mm` varchar(255) NOT NULL,
  `besoins_nutriments` text NOT NULL,
  `sensibilite_maladies` text NOT NULL,
  `informations_generales` text NOT NULL,
  `id_agriculteur` int(11) NOT NULL,
  `statut` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `culture`
--

INSERT INTO `culture` (`id`, `nom_commun`, `cycle_de_vie_jours`, `besoins_eau_mm`, `besoins_nutriments`, `sensibilite_maladies`, `informations_generales`, `id_agriculteur`, `statut`) VALUES
(1, 'riz  ', '90jours   Annuel ', '100ml    ', 'eleve (N,P,K)', 'gui    ', 'croissance bonne    ', 23, '0'),
(5, 'soja', '30jours', '200l', 'compost', 'gui', 'croissance bonne', 23, '0'),
(12, 'paume', '4mois', '1000l', 'eleve (N,P,K)', 'chanceron', 'observation ', 23, '17'),
(13, 'paume', '4mois', '1000l', 'eleve (N,P,K)', 'chanceron', 'croissance bonne', 23, 'actif'),
(14, 'manioc', '6mois', '10000l', 'compost', 'chanceron', 'RAS', 23, 'actif'),
(15, 'plantain', '30jours', '1000l', 'eleve (N,P,K)', 'chanceron', 'RAS', 23, 'actif');

-- --------------------------------------------------------

--
-- Structure de la table `intrant`
--

CREATE TABLE `intrant` (
  `id` int(11) NOT NULL,
  `nom_intrant` varchar(45) NOT NULL,
  `type_intrant` varchar(255) NOT NULL,
  `unite_standard` varchar(45) NOT NULL,
  `descriptions` text NOT NULL,
  `id_agriculteur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `intrant`
--

INSERT INTO `intrant` (`id`, `nom_intrant`, `type_intrant`, `unite_standard`, `descriptions`, `id_agriculteur`) VALUES
(3, 'compost', 'naturel', '0f', 'en grande quantité', 23),
(4, 'La maerde de poule', 'naturel', '0f', 'Aide la croissance de plante bio et naturel', 23),
(5, 'engrais ', 'chimique', '2000f', 'Aide la croissance de plante ', 23);

-- --------------------------------------------------------

--
-- Structure de la table `messages_contact`
--

CREATE TABLE `messages_contact` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `sujet` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `messages_contact`
--

INSERT INTO `messages_contact` (`id`, `nom`, `email`, `sujet`, `message`, `prenom`) VALUES
(2, 'riso', 'tmse@exemple.com', 'voir', 'je voudrai vous voir', 'tyty');

-- --------------------------------------------------------

--
-- Structure de la table `meteo_cache`
--

CREATE TABLE `meteo_cache` (
  `id` int(11) NOT NULL,
  `id_agriculteur` int(11) NOT NULL,
  `date_heure_releve` datetime NOT NULL,
  `temperature` decimal(5,2) NOT NULL,
  `humidite` decimal(5,2) DEFAULT NULL,
  `description_meteo` varchar(100) NOT NULL,
  `icone_code` varchar(10) DEFAULT NULL,
  `vitesse_vent` decimal(5,2) DEFAULT NULL,
  `pression` decimal(6,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `observation`
--

CREATE TABLE `observation` (
  `id` int(11) NOT NULL,
  `date_re` datetime NOT NULL,
  `cout` decimal(10,2) DEFAULT NULL,
  `quantite` decimal(10,0) NOT NULL,
  `rendement` decimal(10,2) DEFAULT NULL,
  `id_plantation` int(11) NOT NULL,
  `id_agriculteur` int(11) NOT NULL,
  `rendement_nets_actuels` varchar(255) NOT NULL,
  `rendement_nets_an_dernier` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `observation`
--

INSERT INTO `observation` (`id`, `date_re`, `cout`, `quantite`, `rendement`, `id_plantation`, `id_agriculteur`, `rendement_nets_actuels`, `rendement_nets_an_dernier`) VALUES
(3, '2026-08-29 00:00:00', 32000.00, 2000, 21.00, 4, 23, '46,716FCFA', '46,716FCFA'),
(4, '2026-09-02 00:00:00', 2000.00, 100, 2.00, 4, 23, '46,716FCFA', '46,716FCFA'),
(5, '2026-09-03 00:00:00', 2000.00, 100, 2.00, 23, 23, '46,716FCFA', '46,716FCFA'),
(7, '2026-09-02 00:00:00', 40.00, 3000, 2.00, 7, 23, '46,716FCFA', '46,716FCFA'),
(8, '2026-09-19 00:00:00', 40.00, 4000, 20.00, 25, 23, '60,716FCFA', '65,716FCFA'),
(9, '2026-11-08 00:00:00', 40.00, 4000, 10.00, 25, 23, '50,716FCFA', '55,716FCFA'),
(10, '2026-09-19 00:00:00', 30.00, 3000, 5.00, 25, 23, '20,716FCFA', '25,716FCFA'),
(11, '2026-09-19 00:00:00', 3500.00, 1500, 10.00, 25, 23, '60,716FCFA', '65,716FCFA'),
(12, '2026-09-19 00:00:00', 4000.00, 300, 2.00, 25, 23, '20,716FCFA', '25,716FCFA'),
(13, '2026-11-06 00:00:00', 3200.00, 1500, 2.00, 4, 23, '60,716FCFA', '25,716FCFA');

-- --------------------------------------------------------

--
-- Structure de la table `parcelle`
--

CREATE TABLE `parcelle` (
  `id` int(11) NOT NULL,
  `nom_parcelle` varchar(45) NOT NULL,
  `superficie_ha` decimal(10,0) NOT NULL,
  `localisation_gps` varchar(255) NOT NULL,
  `typ_sol` varchar(255) NOT NULL,
  `date_creation` date NOT NULL,
  `statut` varchar(255) NOT NULL,
  `id_agriculteur_proprietaire` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `parcelle`
--

INSERT INTO `parcelle` (`id`, `nom_parcelle`, `superficie_ha`, `localisation_gps`, `typ_sol`, `date_creation`, `statut`, `id_agriculteur_proprietaire`) VALUES
(4, 'champs z  ', 200, '45.33, -22.100 ', 'noir    ', '2025-11-11', 'actif', 23),
(9, 'champs A   ', 10, '4.545, -11.502   ', 'argilleux    ', '2025-11-11', 'actif', 23),
(11, 'Champs de soja ', 100, '11.6, -5.722', 'humide ', '2025-11-11', 'actif', 23),
(14, 'champs V  ', 100, '20.333, -2.457 ', 'sableux  ', '2025-11-11', 'actif', 23),
(15, 'Champs de soja ', 100, '2.234, -4.888', 'humide ', '2025-11-11', 'actif', 23),
(16, 'champs  soja', 100, '4.0511,9.7679', 'Argileux', '2025-11-11', 'Actif', 23),
(18, 'Champs de soja', 100, '3.8058,11.5315', 'Humifère', '2026-03-24', 'Actif', 23);

-- --------------------------------------------------------

--
-- Structure de la table `plantation`
--

CREATE TABLE `plantation` (
  `id` int(11) NOT NULL,
  `date_semis` date NOT NULL,
  `date_recolte_prevue` date NOT NULL,
  `quantite_semis_kg_ha` decimal(10,0) NOT NULL,
  `statut_plantation` varchar(255) NOT NULL,
  `rendement_final_kg` decimal(10,0) NOT NULL,
  `unite_rendement` varchar(45) NOT NULL,
  `id_parcelle` int(11) NOT NULL,
  `id_culture` int(11) NOT NULL,
  `id_agriculteur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `plantation`
--

INSERT INTO `plantation` (`id`, `date_semis`, `date_recolte_prevue`, `quantite_semis_kg_ha`, `statut_plantation`, `rendement_final_kg`, `unite_rendement`, `id_parcelle`, `id_culture`, `id_agriculteur`) VALUES
(4, '2025-07-10', '2025-07-04', 500, 'en croissance ', 100, 'ha ', 4, 1, 23),
(6, '2025-08-08', '2025-08-09', 640, 'en croissance', 100, 'ha', 4, 1, 23),
(7, '2025-08-07', '2025-08-10', 500, 'en croissance', 100, 'ha', 4, 1, 23),
(22, '2025-08-08', '2025-08-28', 2000, 'en attente ', 221444, 'ha', 4, 1, 23),
(23, '2025-09-19', '2025-09-26', 243, 'en attente ', 221444, 'ha', 4, 12, 23),
(24, '2025-11-08', '2025-11-28', 2400, 'en attente ', 221444, 'm^2 ', 14, 14, 23),
(25, '2025-09-19', '2025-09-27', 243, 'Termine', 100, 'ha', 9, 15, 23),
(26, '2025-12-03', '2025-12-04', 500, 'Planifiée', 2, 'ha', 4, 1, 23);

-- --------------------------------------------------------

--
-- Structure de la table `points_eau`
--

CREATE TABLE `points_eau` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `type_source` enum('puits','rivière','forage','réservoir') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `id_agriculteur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `points_eau`
--

INSERT INTO `points_eau` (`id`, `nom`, `latitude`, `longitude`, `type_source`, `description`, `id_agriculteur`) VALUES
(2, 'puits sud', 4.04678300, 9.76749000, 'puits', NULL, 23),
(3, 'puits sud', 4.04982347, 9.77152495, 'puits', NULL, 23),
(4, 'puits sud', 4.04943802, 9.77148207, 'puits', NULL, 23),
(5, 'puits', 4.04911681, 9.77126747, 'puits', NULL, 23),
(7, 'puits', 4.04858100, 9.76337000, 'puits', NULL, 23),
(8, 'puits', 11.59621900, -5.72861800, 'puits', NULL, 23),
(9, 'rivière nord', 2.23021400, -4.88056700, 'puits', NULL, 23),
(10, 'puits sud', 45.32897300, -22.11105700, 'puits', NULL, 23),
(11, 'babayaga', 4.05303600, 9.75742600, 'puits', NULL, 23),
(12, 'uio', 3.98704000, 9.65818800, 'puits', NULL, 23),
(13, 'puits sud', 4.54322400, -11.49989500, 'puits', NULL, 23),
(19, 'Puits de proximité ISSAM', 3.80650000, 11.53200000, 'puits', NULL, 23),
(20, 'Source Borne 10', 3.80400000, 11.52900000, 'forage', NULL, 23),
(21, 'Point eau Tropique-Express', 3.81000000, 11.53500000, 'rivière', NULL, 23);

-- --------------------------------------------------------

--
-- Structure de la table `recommandation`
--

CREATE TABLE `recommandation` (
  `id` int(11) NOT NULL,
  `date_recom` datetime NOT NULL,
  `statut_recom` varchar(255) NOT NULL,
  `contenu_recom` varchar(255) NOT NULL,
  `id_plantation` int(11) NOT NULL,
  `id_utilisateur_recom` int(11) NOT NULL,
  `id_conseiller` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `recommandation`
--

INSERT INTO `recommandation` (`id`, `date_recom`, `statut_recom`, `contenu_recom`, `id_plantation`, `id_utilisateur_recom`, `id_conseiller`) VALUES
(4, '2025-08-14 00:00:00', 'en_attente', 'Utiliser le fertilisant \'supercroissance\' pour les cultures de blé', 7, 23, 25),
(5, '2025-09-11 00:00:00', 'en_attente', 'Utiliser le fertilisant \'supercroissance\' pour les cultures de blé', 24, 23, 25),
(6, '2025-09-11 00:00:00', 'en_attente', 'Utiliser le fertilisant \'supercroissance\' pour les cultures de blé', 24, 23, 25),
(7, '2025-10-27 00:00:00', 'en_attente', 'Utiliser le fertilisant \'supercroissance\' pour les cultures de blé', 4, 2, 25);

-- --------------------------------------------------------

--
-- Structure de la table `rendement_mens`
--

CREATE TABLE `rendement_mens` (
  `id` int(11) NOT NULL,
  `valeur_production` varchar(255) NOT NULL,
  `unite` varchar(50) DEFAULT 't',
  `date_enregis` date NOT NULL DEFAULT current_timestamp(),
  `annee` int(11) NOT NULL,
  `mois` int(11) NOT NULL,
  `id_agriculteur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `rendement_mens`
--

INSERT INTO `rendement_mens` (`id`, `valeur_production`, `unite`, `date_enregis`, `annee`, `mois`, `id_agriculteur`) VALUES
(1, '1200.00', 'kg/ha', '2025-07-17', 2024, 1, 23),
(2, '1800.00', 'kg/ha', '2025-07-18', 2024, 2, 23),
(3, '4000.00', 'kg/ha', '2025-07-19', 2024, 3, 23),
(4, '2500.00', 'kg/ha', '2025-07-20', 2024, 4, 23),
(5, '5500.00', 'kg/ha', '2025-07-21', 2024, 5, 23),
(6, '4800.00', 'kg/ha', '2025-07-22', 2024, 6, 23),
(7, '2500.00', 'kg/ha', '2025-07-02', 2023, 7, 23),
(8, '1800.00', 'kg/ha', '2025-07-03', 2023, 8, 23),
(9, '2200.00', 'kg/ha', '2025-07-04', 2023, 9, 23),
(10, '1500.00', 'kg/ha', '2025-07-05', 2023, 10, 23),
(11, '1000.00', 'kg/ha', '2025-07-06', 2023, 10, 23),
(12, '800.00', 'kg/ha', '2025-07-08', 2023, 12, 23),
(13, '2500.00', 'kg/ha', '2025-09-23', 2024, 1, 23),
(14, '2500.00', 'kg/ha', '2025-11-06', 2025, 2, 23),
(15, '1200.00', 'kg/ha', '2025-11-18', 2023, 2, 23);

-- --------------------------------------------------------

--
-- Structure de la table `reponse`
--

CREATE TABLE `reponse` (
  `id` int(11) NOT NULL,
  `message_agriculteur` varchar(255) NOT NULL,
  `id_conseiller` int(11) NOT NULL,
  `id_agriculteur_assiste` int(11) NOT NULL,
  `date_envoi` datetime NOT NULL DEFAULT current_timestamp(),
  `message_conseiller` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reponse`
--

INSERT INTO `reponse` (`id`, `message_agriculteur`, `id_conseiller`, `id_agriculteur_assiste`, `date_envoi`, `message_conseiller`) VALUES
(1, 'rrrrrrr', 41, 23, '2025-11-06 16:42:18', ''),
(2, 'rrrrrr', 41, 23, '2025-11-06 16:42:30', ''),
(3, 'Bonjour', 41, 23, '2025-11-07 12:10:25', ''),
(4, 'D\'accord j\'ai compris merci', 25, 23, '2025-11-07 12:18:01', '');

-- --------------------------------------------------------

--
-- Structure de la table `stock_intrant`
--

CREATE TABLE `stock_intrant` (
  `id` int(11) NOT NULL,
  `quantite_actuelle` decimal(10,0) NOT NULL,
  `seuil_alerte` decimal(10,0) NOT NULL,
  `date_derniere_mise_a_jour` datetime NOT NULL,
  `id_intrant` int(11) NOT NULL,
  `id_agriculteur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `stock_intrant`
--

INSERT INTO `stock_intrant` (`id`, `quantite_actuelle`, `seuil_alerte`, `date_derniere_mise_a_jour`, `id_intrant`, `id_agriculteur`) VALUES
(6, -23843, 1, '2026-03-15 18:38:41', 4, 23),
(11, 50, 1, '2026-03-23 00:00:00', 3, 23),
(12, 59, 1, '2026-03-23 00:00:00', 5, 23);

-- --------------------------------------------------------

--
-- Structure de la table `tache`
--

CREATE TABLE `tache` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `statut` varchar(255) NOT NULL,
  `priorite` varchar(255) NOT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `id_agriculteur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tache`
--

INSERT INTO `tache` (`id`, `titre`, `statut`, `priorite`, `date_debut`, `date_fin`, `id_agriculteur`) VALUES
(1, 'semis printanier', 'en_attente', 'ouvrier_test', '2025-11-06 00:00:00', '2025-11-27 00:00:00', 23),
(2, 'Labour hivernal', 'en_cour', 'ouvrier_test', '2025-09-01 00:00:00', '2025-09-10 00:00:00', 23),
(3, 'Désherbage', 'Terminée', 'ouvrier_test', '2025-11-06 00:00:00', '2025-11-28 00:00:00', 23),
(4, 'Irrigation', 'Terminée', 'sophie', '2025-11-06 00:00:00', '2025-11-20 00:00:00', 23),
(5, 'Désherbage', 'en_cour', 'ouvrier_test', '2025-11-05 00:00:00', '2025-11-20 00:00:00', 23),
(6, 'irrigation', 'Terminée', 'ouvrier_test', '2025-11-07 00:00:00', '2025-11-14 00:00:00', 23),
(7, 'semis printanier', 'Terminée', 'irene', '2025-11-18 07:43:00', '2025-11-12 07:43:00', 23),
(8, 'semis printanier', 'en_attente', 'ouvrier_test', '2025-12-03 15:15:00', '2025-12-19 15:15:00', 23),
(9, 'Désherbage', 'en_attente', 'ouvrier_test', '2026-03-08 13:48:00', '2026-03-22 13:48:00', 23);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL,
  `email` varchar(45) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `nom` varchar(45) NOT NULL,
  `prenom` varchar(45) NOT NULL,
  `telephone` varchar(45) NOT NULL,
  `pays` varchar(150) NOT NULL,
  `date_inscription` datetime NOT NULL,
  `type_utilisateur` varchar(45) NOT NULL,
  `reset_token` varchar(255) NOT NULL,
  `reset_token_expiration` datetime NOT NULL,
  `photo` varchar(255) NOT NULL,
  `langue` varchar(5) DEFAULT 'fr',
  `timezone` varchar(50) DEFAULT 'Africa/Douala',
  `notif_arrosage` tinyint(1) DEFAULT 1,
  `notif_stock` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `email`, `mot_de_passe`, `nom`, `prenom`, `telephone`, `pays`, `date_inscription`, `type_utilisateur`, `reset_token`, `reset_token_expiration`, `photo`, `langue`, `timezone`, `notif_arrosage`, `notif_stock`) VALUES
(2, 'irene@gmail.com', '1234', 'ateba', 'david', '659643236', 'Cameroun', '2025-07-31 00:00:00', 'administrateur', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(5, 'test@example.com', '$2y$10$LIXwZA4TGBn/e.9BirRki.5mw8FCq1MseAkz7sfMKln4dX64xWFsK', 'irene', 'vivi', '658645423', 'Cameroun', '2025-07-29 00:00:00', 'administrateur', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(6, 'test@ex.com', '$2y$10$w9.a4pMV.DgDJUCL0fINSOjJ7u6/bUnarruUzO3hdSxQpGKRMZMqy', 'irene', 'vi', '654323812', 'Cameroun', '2025-07-31 00:00:00', 'administrateur', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(7, 'irene@example.com', '$2y$10$lh22nbxmCiybb6pGNLvB1OpevSCbeF.Yklaa0TEdB4aeQFCs6riSq', 'irene', 'viti', '656643335', 'Cameroun', '2025-07-31 00:00:00', 'administrateur', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(8, 'irene@exemple.com', '$2y$10$3HCFisHhNnvC8poekvZh7eUq2PapRMbT1kWTlv.vaNb1HBadjblDa', 'Rom', 'ti', '654342857', 'Cameroun', '2025-07-31 00:00:00', 'administrateur', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(10, 'ateba@exemple.com', '$2y$10$TFpnTLXrZmVwhWgeX3eaPeVGk9FOeurCUn9K/tuDfrKWBlW3Q/ltq', 'ruth', 'tata', '655442802', 'Cameroun', '2025-07-30 00:00:00', 'administrateur', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(16, 'tmte@exemple.com', '$2y$10$72olZ0r/MENkd/pIPwiRwO4hGvldWS6MBlm7L.iUiVFjrd8V3fiPS', 'rI', 'titi', '688766666', 'Cameroun', '2025-07-31 00:00:00', 'administrateur', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(17, 'tmne@exemple.com', '$2y$10$NVBxCWgdftvAOIXEi8Ubqet1kC3S2yOaNDMc3y4AX/EMVtM9u657S', 'rI', 'titi', '654342857', 'Cameroun', '2025-07-31 00:00:00', 'administrateur', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(20, 'tmue@exemple.com', '$2y$10$n.Sq.03WBcT0lesN9miZou5iUciI4Xmq7X1c0ig7Vr3fo2O2LxzS.', 'rio', 'tyty', '66552233', 'Cameroun', '2025-07-31 00:00:00', 'agriculteur', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(21, 'tmie@exemple.com', '$2y$10$dVB1xZO45K8euBJ9.LZYa.d0vg1OwIOS55Khs8CORr3kP1dQ/SFzi', 'riu', 'tyty', '66552234', 'Cameroun', '2025-07-31 00:00:00', 'administrateur', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(22, 'tmre@exemple.com', '$2y$10$wbM0ir9aUK990SIy9.SwfesrY8c6dOvn8K1J1/aO.cf7d7RCV21G2', 'ria', 'tyty', '66552230', 'Cameroun', '2025-07-31 00:00:00', 'conseiller_agricole', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(23, 'tmui@exemple.com', '$2y$10$WhrMEbNYmDnum9hiivGkcuZP03RpJYAHGqRK3anNh2Ubrj8InqAY.', 'ris', 'tyty', '66552238', 'Cameroun', '2025-07-29 00:00:00', 'agriculteur', 'fd4ade747bfa08216eeeca8966825901a361fe2ba4d9d05099febfd76130397b', '2025-08-11 21:24:10', 'user_23_1774329220.jpg', 'en', 'Africa/Douala', 1, 0),
(25, 'tmse@exemple.com', '$2y$10$gZBAJhu2uCPXrmTqqK0PoemP12F9HOttqg7co5VEHbi.Pnl4XTZCq', 'riso', 'tyty', '66552237', 'Cameroun', '2025-07-23 00:00:00', 'conseiller_agricole', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(26, 'tmab@exemple.com', '$2y$10$bBCNU5EyBnWNE7t.mkj9keJpwsJr03yy0wxQjDuXYlyttz.kEJ722', 'ange', 'pascal', '655432122', 'Cameroun', '2025-08-28 00:00:00', 'administrateur', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(27, 'akonogilbert@gmail.com', '$2y$10$K1uy54aEQdojErr7rl0EV.QhdwqY.nz/ndyg/SI5Xjwg7J.f4k9/2', 'helsinky', 'james', '692679775', 'Cameroun', '2025-08-10 00:00:00', 'agriculteur', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(28, 'tmsms@exemple.com', '$2y$10$ycoUqkQMxnsAu0zX22xZz.mcNlRoNj0/m1.8Edfs/pU9ooErhJLIO', 'ate', 'davi', '659672812', 'Cameroun', '2025-08-15 00:00:00', 'agriculteur', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(29, 'tmsei@exemple.com', '$2y$10$zrkalaXwa5cS8GhaVdp6Ee8wKGQQYrfwq/ts42RWPvT0/vISAql8O', 'at', 'dav', '654342850', 'Cameroun', '2025-08-15 00:00:00', 'agriculteur', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(30, 'tmseoo@exemple.com', '$2y$10$TFJMDFvd75RCNJ/2V2V.YeRv/TE/1TeNHR2wGIBtSgyqafuOoPYcK', 'at', 'dav', '66553300', 'Cameroun', '2025-08-15 00:00:00', 'agriculteur', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(38, 'tmseot@exemple.com', '$2y$10$zpu65jqNoufRhL8en.zwFu0FEhQhNXFoM1S3bnjjPctaUtqqiHCG.', 'Roma', 'pati', '654342855', 'Cameroun', '2025-09-12 00:00:00', 'conseiller_agricole', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(39, 'tmsau@exemple.com', '$2y$10$mR8eTXnaH5a5IYNSCSaNRu6RaTFtqnWmogUkJl1wMqscw6Nukm7OW', 'Romari', 'rin', '654342820', 'Cameroun', '2025-09-12 00:00:00', 'agriculteur', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(40, 'tmsov@exemple.com', '$2y$10$bU2jUDSV0AikhR4UzON76eTcrtsMf3GYaapXDc99NayEw2wxC.Tdu', 'venus', 'mani', '654342875', 'Cameroun', '2025-09-12 00:00:00', 'conseiller_agricole', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(41, 'kg@gmail.com', '$2y$10$q7Fm73sGRv/LxhmsMdzsh.h6aDMgE181y3ujGnz9e/oMYW2u5YeOO', 'atangana', 'chaoline', '675053858', 'Cameroun', '2025-09-19 00:00:00', 'conseiller_agricole', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(42, 'tmsgie@exemple.com', '$2y$10$1bTNiIC9mw78a6VP3YrWMeA5f/P3b9xtTgLhi7fGVsEm2wl/KOjmO', 'ggO', 'giI', '6596432300', 'Cameroun', '2025-10-27 00:00:00', 'agriculteur', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(43, 'tmuivv@exemple.com', '$2y$10$c2eegDuyPLZN6b9UPORiv.h9mcBCAemIWG1HrzWexvMlp8RtZEkfa', 'hi', 'pi', '333444555', 'Cameroun', '2025-11-05 05:54:51', 'conseiller_agricole', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(44, 'tmOO@exemple.com', '$2y$10$ncpTLRMFkXsAFneRU5arLeYagQ3E1tyLNWPabhCbVZny2mSeBgjkW', 'vivi', 'to', '666666666666', 'Cameroun', '2025-12-16 13:40:20', 'agriculteur', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(45, 'tm22@exemple.com', '$2y$10$Fgyf8RTEW5h0GXh0OKpZo.ydCDilxmDMSsgM4OlHh.frVyPbcEbFa', 'vivi', 'to', '666666666666', 'Cameroun', '2025-12-16 13:41:10', 'agriculteur', '', '0000-00-00 00:00:00', '', 'fr', 'Africa/Douala', 1, 0),
(46, 'tmtoto@exemple.com', '$2y$10$culPFR4ibLRQZeFrZmceLux.a9yAl9DhcbXkFgwb/EG7g1MqwIp8K', 'toto', 'bobo', '666666', 'Cameroun', '2026-01-05 12:54:23', 'agriculteur', '', '1000-01-01 00:00:00', '1767614063_Snapchat_25215917.jpg', 'fr', 'Africa/Douala', 1, 0),
(47, 'tmdidi@exemple.com', '$2y$10$wa.fjhY/tIr2SnVJ5nFkLO7vNenWgfXY3xV.mDX69jJ44c.kLCpjy', 'ateb', 'davi', '659641234', 'Cameroun', '2026-03-08 18:36:00', 'agriculteur', '', '1000-01-01 00:00:00', '1772991359_Capture_d___cran_2026_03_06_033739.png', 'fr', 'Africa/Douala', 1, 0),
(48, 'tmiriri@exemple.com', '$2y$10$54w7puN7uBqUjFnbAk6aGO2qQQAsSQqBWgiiyRDPksULJys6gvX0G', 'atebiii', 'daviiiiiii', '6596435555', 'Cameroun', '2026-03-08 18:39:21', 'agriculteur', '', '1000-01-01 00:00:00', '1772991561_Capture_d___cran_2025_07_31_112143.png', 'fr', 'Africa/Douala', 1, 0),
(49, 'tmuuuuu@exemple.com', '$2y$10$lWXgTNso/VpFn1EgrDlwJ.dMacy0C4k9ssxctI080L27plo3uQfR.', 'atebuuuuu', 'daviuuu', '659643577', 'Cameroun', '2026-03-08 18:41:50', 'agriculteur', '', '1000-01-01 00:00:00', '1772991710_Capture_d_____cran_2025_07_31_112032.png', 'fr', 'Africa/Douala', 1, 0);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `application_intrant`
--
ALTER TABLE `application_intrant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_plantation` (`id_plantation`),
  ADD KEY `id_intrant` (`id_intrant`),
  ADD KEY `id_utilisaeur_applicateur` (`id_agriculteur`);

--
-- Index pour la table `assistance_conseiller`
--
ALTER TABLE `assistance_conseiller`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_conseiller` (`id_conseiller`),
  ADD KEY `id_agriculteur_assiste` (`id_agriculteur_assiste`);

--
-- Index pour la table `culture`
--
ALTER TABLE `culture`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_agriculteur` (`id_agriculteur`);

--
-- Index pour la table `intrant`
--
ALTER TABLE `intrant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_agriculteur` (`id_agriculteur`);

--
-- Index pour la table `messages_contact`
--
ALTER TABLE `messages_contact`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `meteo_cache`
--
ALTER TABLE `meteo_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_agriculteur` (`id_agriculteur`);

--
-- Index pour la table `observation`
--
ALTER TABLE `observation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_plantation` (`id_plantation`),
  ADD KEY `id_utilisateur_createur` (`id_agriculteur`);

--
-- Index pour la table `parcelle`
--
ALTER TABLE `parcelle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_agriculteur_proprietaire` (`id_agriculteur_proprietaire`);

--
-- Index pour la table `plantation`
--
ALTER TABLE `plantation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_parcelle` (`id_parcelle`),
  ADD KEY `id_culture` (`id_culture`),
  ADD KEY `id_agriculteur` (`id_agriculteur`);

--
-- Index pour la table `points_eau`
--
ALTER TABLE `points_eau`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_points_eau_agriculteur` (`id_agriculteur`);

--
-- Index pour la table `recommandation`
--
ALTER TABLE `recommandation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_plantation` (`id_plantation`),
  ADD KEY `id_utilisateur_createur_recom` (`id_utilisateur_recom`),
  ADD KEY `id_conseiller` (`id_conseiller`);

--
-- Index pour la table `rendement_mens`
--
ALTER TABLE `rendement_mens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_agriculteur` (`id_agriculteur`);

--
-- Index pour la table `reponse`
--
ALTER TABLE `reponse`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_conseiller` (`id_conseiller`),
  ADD KEY `id_agriculteur_assiste` (`id_agriculteur_assiste`);

--
-- Index pour la table `stock_intrant`
--
ALTER TABLE `stock_intrant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_intrant` (`id_intrant`),
  ADD KEY `id_agriculteur` (`id_agriculteur`);

--
-- Index pour la table `tache`
--
ALTER TABLE `tache`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_agriculteur` (`id_agriculteur`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `application_intrant`
--
ALTER TABLE `application_intrant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `assistance_conseiller`
--
ALTER TABLE `assistance_conseiller`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT pour la table `culture`
--
ALTER TABLE `culture`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `intrant`
--
ALTER TABLE `intrant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `messages_contact`
--
ALTER TABLE `messages_contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `meteo_cache`
--
ALTER TABLE `meteo_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `observation`
--
ALTER TABLE `observation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `parcelle`
--
ALTER TABLE `parcelle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `plantation`
--
ALTER TABLE `plantation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT pour la table `points_eau`
--
ALTER TABLE `points_eau`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `recommandation`
--
ALTER TABLE `recommandation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `rendement_mens`
--
ALTER TABLE `rendement_mens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `reponse`
--
ALTER TABLE `reponse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `stock_intrant`
--
ALTER TABLE `stock_intrant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `tache`
--
ALTER TABLE `tache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `application_intrant`
--
ALTER TABLE `application_intrant`
  ADD CONSTRAINT `application_intrant_ibfk_1` FOREIGN KEY (`id_plantation`) REFERENCES `plantation` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `application_intrant_ibfk_2` FOREIGN KEY (`id_intrant`) REFERENCES `intrant` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `application_intrant_ibfk_3` FOREIGN KEY (`id_agriculteur`) REFERENCES `utilisateur` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `assistance_conseiller`
--
ALTER TABLE `assistance_conseiller`
  ADD CONSTRAINT `assistance_conseiller_ibfk_1` FOREIGN KEY (`id_conseiller`) REFERENCES `utilisateur` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `assistance_conseiller_ibfk_2` FOREIGN KEY (`id_agriculteur_assiste`) REFERENCES `utilisateur` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `culture`
--
ALTER TABLE `culture`
  ADD CONSTRAINT `culture_ibfk_1` FOREIGN KEY (`id_agriculteur`) REFERENCES `utilisateur` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `intrant`
--
ALTER TABLE `intrant`
  ADD CONSTRAINT `intrant_ibfk_1` FOREIGN KEY (`id_agriculteur`) REFERENCES `utilisateur` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `meteo_cache`
--
ALTER TABLE `meteo_cache`
  ADD CONSTRAINT `fk_meteo_agriculteur` FOREIGN KEY (`id_agriculteur`) REFERENCES `utilisateur` (`id`);

--
-- Contraintes pour la table `observation`
--
ALTER TABLE `observation`
  ADD CONSTRAINT `observation_ibfk_1` FOREIGN KEY (`id_plantation`) REFERENCES `plantation` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `observation_ibfk_2` FOREIGN KEY (`id_agriculteur`) REFERENCES `utilisateur` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `parcelle`
--
ALTER TABLE `parcelle`
  ADD CONSTRAINT `parcelle_ibfk_1` FOREIGN KEY (`id_agriculteur_proprietaire`) REFERENCES `utilisateur` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `plantation`
--
ALTER TABLE `plantation`
  ADD CONSTRAINT `plantation_ibfk_1` FOREIGN KEY (`id_parcelle`) REFERENCES `parcelle` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `plantation_ibfk_2` FOREIGN KEY (`id_culture`) REFERENCES `culture` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `plantation_ibfk_3` FOREIGN KEY (`id_agriculteur`) REFERENCES `utilisateur` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `points_eau`
--
ALTER TABLE `points_eau`
  ADD CONSTRAINT `fk_points_eau_agriculteur` FOREIGN KEY (`id_agriculteur`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `recommandation`
--
ALTER TABLE `recommandation`
  ADD CONSTRAINT `recommandation_ibfk_1` FOREIGN KEY (`id_plantation`) REFERENCES `plantation` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `recommandation_ibfk_2` FOREIGN KEY (`id_utilisateur_recom`) REFERENCES `utilisateur` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `recommandation_ibfk_3` FOREIGN KEY (`id_conseiller`) REFERENCES `utilisateur` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `rendement_mens`
--
ALTER TABLE `rendement_mens`
  ADD CONSTRAINT `rendement_mens_ibfk_1` FOREIGN KEY (`id_agriculteur`) REFERENCES `utilisateur` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `reponse`
--
ALTER TABLE `reponse`
  ADD CONSTRAINT `reponse_ibfk_1` FOREIGN KEY (`id_conseiller`) REFERENCES `utilisateur` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `reponse_ibfk_2` FOREIGN KEY (`id_agriculteur_assiste`) REFERENCES `utilisateur` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `stock_intrant`
--
ALTER TABLE `stock_intrant`
  ADD CONSTRAINT `stock_intrant_ibfk_1` FOREIGN KEY (`id_intrant`) REFERENCES `intrant` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `stock_intrant_ibfk_2` FOREIGN KEY (`id_agriculteur`) REFERENCES `utilisateur` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `tache`
--
ALTER TABLE `tache`
  ADD CONSTRAINT `tache_ibfk_1` FOREIGN KEY (`id_agriculteur`) REFERENCES `utilisateur` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

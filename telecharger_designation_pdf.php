<?php
require_once 'auth_check.php';
require_once 'config/database.php';
require_once 'classes/MatchManager.php';

// Inclure TCPDF
require_once('tcpdf/tcpdf.php');

$matchManager = new MatchManager($pdo);

$match_id = $_GET['id'] ?? null;
if (!$match_id) {
    header('Location: dashboard.php');
    exit;
}

$match = $matchManager->getMatchById($match_id);
if (!$match) {
    header('Location: dashboard.php');
    exit;
}

// Créer une nouvelle instance de TCPDF
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// Définir les informations du document
$pdf->SetCreator('Système de Désignation d\'Arbitres');
$pdf->SetAuthor('Fédération Sénégalaise de Football');
$pdf->SetTitle('Désignation d\'Arbitrage - Match ' . $match_id);

// Supprimer les en-têtes et pieds de page par défaut
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Définir les marges
$pdf->SetMargins(15, 15, 15);

// Ajouter une page
$pdf->AddPage();

// Définir la police
$pdf->SetFont('helvetica', '', 10);

// En-tête avec logo
$pdf->Image('./logo.jpg', 15, 15, 30, 30, 'JPG', '', '', false, 300, '', false, false, 0, false, false, false);
$pdf->SetXY(50, 15);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 8, 'FÉDÉRATION SÉNÉGALAISE DE FOOTBALL', 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, 'COMMISSION CENTRALE DES ARBITRES', 0, 1, 'C');
$pdf->Cell(0, 6, 'COMMISSION DE DESIGNATION S/CRA DAKAR', 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, '2025-2026', 0, 1, 'C');

// Informations de la compétition
$pdf->SetY(60);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Compétition : ' . $match['nom_competition'], 0, 1, 'C');

// Tableau des désignations
$pdf->SetY(80);

// En-têtes du tableau
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetFillColor(248, 249, 250);
$pdf->SetTextColor(0, 0, 0);

// Largeurs des colonnes
$w1 = 50; // Dates/Terrain
$w2 = 70; // Rencontre
$w3 = 90; // Arbitre/Assistants
$w4 = 25; // S/CRA

// En-têtes
$pdf->Cell($w1, 10, 'DATES/TERRAIN', 1, 0, 'C', true);
$pdf->Cell($w2, 10, 'RENCONTRE', 1, 0, 'C', true);
$pdf->Cell($w3, 10, 'ARBITRE/ASSISTANTS', 1, 0, 'C', true);
$pdf->Cell($w4, 10, 'S/CRA', 1, 1, 'C', true);

// Contenu du tableau
$pdf->SetFont('helvetica', '', 10);
$pdf->SetFillColor(255, 255, 255);

// Dates/Terrain
$pdf->Cell($w1, 8, date('d-m-Y', strtotime($match['date_match'])), 1, 0, 'C', true);
$pdf->Cell($w2, 8, '', 1, 0, 'C', true);
$pdf->Cell($w3, 8, '', 1, 0, 'C', true);
$pdf->Cell($w4, 8, '', 1, 1, 'C', true);

$pdf->Cell($w1, 8, $match['heure_match'], 1, 0, 'C', true);
$pdf->Cell($w2, 8, '', 1, 0, 'C', true);
$pdf->Cell($w3, 8, '', 1, 0, 'C', true);
$pdf->Cell($w4, 8, '', 1, 1, 'C', true);

$pdf->Cell($w1, 8, 'Ville : ' . $match['ville'], 1, 0, 'C', true);
$pdf->Cell($w2, 8, '', 1, 0, 'C', true);
$pdf->Cell($w3, 8, '', 1, 0, 'C', true);
$pdf->Cell($w4, 8, '', 1, 1, 'C', true);

$pdf->Cell($w1, 8, 'Stade : ' . $match['stade'], 1, 0, 'C', true);
$pdf->Cell($w2, 8, '', 1, 0, 'C', true);
$pdf->Cell($w3, 8, '', 1, 0, 'C', true);
$pdf->Cell($w4, 8, '', 1, 1, 'C', true);

// Rencontre
$pdf->SetY(88);
$pdf->SetX(65);
$pdf->Cell($w2, 8, $match['equipe_a_nom'], 1, 0, 'C', true);
$pdf->Cell($w3, 8, '', 1, 0, 'C', true);
$pdf->Cell($w4, 8, '', 1, 1, 'C', true);

$pdf->SetX(65);
$pdf->Cell($w2, 8, 'Vs', 1, 0, 'C', true);
$pdf->Cell($w3, 8, '', 1, 0, 'C', true);
$pdf->Cell($w4, 8, '', 1, 1, 'C', true);

$pdf->SetX(65);
$pdf->Cell($w2, 8, $match['equipe_b_nom'], 1, 0, 'C', true);
$pdf->Cell($w3, 8, '', 1, 0, 'C', true);
$pdf->Cell($w4, 8, '', 1, 1, 'C', true);

$pdf->SetX(65);
$pdf->Cell($w2, 8, 'Tour : ' . $match['tour'], 1, 0, 'C', true);
$pdf->Cell($w3, 8, '', 1, 0, 'C', true);
$pdf->Cell($w4, 8, '', 1, 1, 'C', true);

// Arbitres
$pdf->SetY(88);
$pdf->SetX(135);

// Arbitre principal
if ($match['arbitre_nom']) {
    $pdf->Cell($w3, 8, 'AR : ' . $match['arbitre_nom'] . ' ' . $match['arbitre_prenom'], 1, 0, 'L', true);
} else {
    $pdf->Cell($w3, 8, 'AR : -', 1, 0, 'L', true);
}
$pdf->Cell($w4, 8, '', 1, 1, 'C', true);

// Assistant 1
$pdf->SetX(135);
if ($match['assistant1_nom']) {
    $pdf->Cell($w3, 8, 'AA1 : ' . $match['assistant1_nom'] . ' ' . $match['assistant1_prenom'], 1, 0, 'L', true);
} else {
    $pdf->Cell($w3, 8, 'AA1 : -', 1, 0, 'L', true);
}
$pdf->Cell($w4, 8, '', 1, 1, 'C', true);

// Assistant 2
$pdf->SetX(135);
if ($match['assistant2_nom']) {
    $pdf->Cell($w3, 8, 'AA2 : ' . $match['assistant2_nom'] . ' ' . $match['assistant2_prenom'], 1, 0, 'L', true);
} else {
    $pdf->Cell($w3, 8, 'AA2 : -', 1, 0, 'L', true);
}
$pdf->Cell($w4, 8, '', 1, 1, 'C', true);

// 4ème officiel
$pdf->SetX(135);
if ($match['officiel4_nom']) {
    $pdf->Cell($w3, 8, '4ème : ' . $match['officiel4_nom'] . ' ' . $match['officiel4_prenom'], 1, 0, 'L', true);
} else {
    $pdf->Cell($w3, 8, '4ème : -', 1, 0, 'L', true);
}
$pdf->Cell($w4, 8, '', 1, 1, 'C', true);

// Assesseur
$pdf->SetX(135);
if ($match['assesseur_nom']) {
    $pdf->Cell($w3, 8, 'ASS : ' . $match['assesseur_nom'] . ' ' . $match['assesseur_prenom'], 1, 0, 'L', true);
} else {
    $pdf->Cell($w3, 8, 'ASS : -', 1, 0, 'L', true);
}
$pdf->Cell($w4, 8, '', 1, 1, 'C', true);

// S/CRA
$pdf->SetY(88);
$pdf->SetX(225);
$pdf->Cell($w4, 8, 'DAKAR', 1, 0, 'C', true);
$pdf->Cell($w4, 8, 'DAKAR', 1, 0, 'C', true);
$pdf->Cell($w4, 8, 'DAKAR', 1, 0, 'C', true);
$pdf->Cell($w4, 8, 'DAKAR', 1, 0, 'C', true);
$pdf->Cell($w4, 8, 'DAKAR', 1, 0, 'C', true);

// Sortie du PDF
$pdf->Output('designation_' . $match_id . '.pdf', 'D');
?> 
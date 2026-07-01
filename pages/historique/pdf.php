<?php
require_once '../../config/db.php';
require_once '../../vendor/autoload.php';
require_once '../../vendor/setasign/fpdf/fpdf.php';

$numAffect = trim($_GET['numAffect'] ?? '');
if (!$numAffect) die("Numéro d'affectation manquant.");

$stmt = $pdo->prepare("
    SELECT a.numAffect, a.dateAffect, a.datePriseService,
           e.civilite, e.nom, e.prenom, e.poste,
           la.design AS ancienLieu, ln.design AS nouveauLieu
    FROM AFFECTER a
    INNER JOIN EMPLOYE e  ON a.numEmp      = e.numEmp
    LEFT  JOIN LIEU    la ON a.ancienLieu  = la.idlieu
    LEFT  JOIN LIEU    ln ON a.nouveauLieu = ln.idlieu
    WHERE a.numAffect = ?
");
$stmt->execute([$numAffect]);
$data = $stmt->fetch();

if (!$data) die("Affectation introuvable.");

function fdate($d) {
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt ? $dt->format('d/m/Y') : $d;
}

$dateAffect       = fdate($data['dateAffect']);
$datePriseService = fdate($data['datePriseService']);
$civilite         = $data['civilite'];
$nomComplet       = $civilite . ' ' . strtoupper($data['nom']) . ' ' . $data['prenom'];
$poste            = $data['poste'];
$ancienLieu       = $data['ancienLieu'];
$nouveauLieu      = $data['nouveauLieu'];
$numAff           = $data['numAffect'];

class PDF extends FPDF {
    function Header() {}
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Times','I',9);
        $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
    }
}

$pdf = new PDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetMargins(25, 20, 25);
$pdf->SetAutoPageBreak(true, 20);

// EN-TETE
$pdf->SetFont('Times','B',14);
$pdf->Cell(0,7,'REPUBLIQUE DE MADAGASCAR',0,1,'C');
$pdf->SetFont('Times','',11);
$pdf->Cell(0,6,'Fihaonana - Fandrosoana - Fahafahana',0,1,'C');
$pdf->Ln(3);
$pdf->SetFont('Times','B',12);
$pdf->Cell(0,6,'Organisme / Ministere',0,1,'C');
$pdf->SetFont('Times','',11);
$pdf->Cell(0,6,'Direction des Ressources Humaines',0,1,'C');
$pdf->Ln(2);
$pdf->SetLineWidth(0.8);
$pdf->Line(25, $pdf->GetY(), 185, $pdf->GetY());
$pdf->Ln(8);

// TITRE
$pdf->SetFont('Times','BU',15);
$pdf->Cell(0,8,'ARRETE D\'AFFECTATION',0,1,'C');
$pdf->Ln(2);
$pdf->SetFont('Times','B',13);
$pdf->Cell(0,7,'N degrees '.$numAff.' du '.$dateAffect,0,1,'C');
$pdf->Ln(12);

// CORPS - paragraphe 1
$pdf->SetFont('Times','',13);
$ligne1 = $nomComplet.', qui occupe le poste de '.$poste.' a '.$ancienLieu.', est affecte a '.$nouveauLieu.' pour compter de la date de prise de service le '.$datePriseService.'.';
$pdf->SetFont('Times','',13);
// Indentation
$pdf->Cell(15);
$pdf->MultiCell(145, 8, $ligne1, 0, 'J');
$pdf->Ln(6);

// Paragraphe 2
$pdf->Cell(15);
$pdf->MultiCell(145, 8, 'Le present communique sera enregistre et communique partout ou besoin sera.', 0, 'J');
$pdf->Ln(14);

// PIED
$pdf->SetFont('Times','I',12);
$pdf->Cell(0,7,'Antananarivo, le '.$dateAffect,0,1,'L');
$pdf->Ln(16);

// SIGNATURE
$pdf->SetFont('Times','B',12);
$pdf->Cell(0,7,'Le Directeur des Ressources Humaines',0,1,'R');
$pdf->Ln(20);
$pdf->SetFont('Times','',12);
$pdf->Cell(0,7,'................................................',0,1,'R');
$pdf->SetFont('Times','I',11);
$pdf->Cell(0,6,'Signature et cachet',0,1,'R');

$pdf->Output('I','arrete_'.$numAff.'.pdf');
exit;
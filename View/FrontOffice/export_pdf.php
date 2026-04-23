<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/MARATHONS/lib/fpdf186/fpdf.php";
require_once "../../Controller/DossardController.php";

$id = $_GET['id_inscription'];

$controller = new DossardController();
$dossards = $controller->getByInscription($id);

$pdf = new FPDF();
$pdf->AddPage();

// ================= HEADER =================
$primary = [15,118,110];

$pdf->SetFillColor($primary[0], $primary[1], $primary[2]);
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('Arial','B',16);

$pdf->Cell(0,12,"BarchaThon - Dossards #$id",0,1,'C',true);
$pdf->Ln(5);

// ================= TABLE HEADER =================
$pdf->SetFillColor(220,240,240);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','B',12);

$pdf->Cell(40,10,"Nom",1,0,'C',true);
$pdf->Cell(25,10,"Numero",1,0,'C',true);
$pdf->Cell(25,10,"Taille",1,0,'C',true);
$pdf->Cell(35,10,"Couleur",1,0,'C',true);
$pdf->Cell(40,10,"QR Code",1,1,'C',true);

// ================= DATA =================
$pdf->SetFont('Arial','',10);

$fill = false;

foreach($dossards as $d){

    // couleur alternée
    if($fill){
        $pdf->SetFillColor(245,250,250);
    } else {
        $pdf->SetFillColor(255,255,255);
    }

    $pdf->Cell(40,25,$d['nom'],1,0,'C',true);
    $pdf->Cell(25,25,$d['numero'],1,0,'C',true);
    $pdf->Cell(25,25,$d['taille'],1,0,'C',true);
    $pdf->Cell(35,25,$d['couleur'],1,0,'C',true);

    // ===== QR CODE =====
    if(!empty($d['qr_code']) && file_exists("../../qr/".$d['qr_code'])) {

        // position image dans cellule
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        $pdf->Cell(40,25,"",1,0,'C',true);
        $pdf->Image("../../qr/".$d['qr_code'], $x + 8, $y + 2, 20, 20);

    } else {
        $pdf->Cell(40,25,"No QR",1,0,'C',true);
    }

    $pdf->Ln();

    $fill = !$fill;
}

// ================= FOOTER =================
$pdf->Ln(10);
$pdf->SetFont('Arial','I',9);
$pdf->SetTextColor(100,100,100);

$pdf->Cell(0,10,"Genere automatiquement - BarchaThon",0,1,'C');

$pdf->Output();

?>
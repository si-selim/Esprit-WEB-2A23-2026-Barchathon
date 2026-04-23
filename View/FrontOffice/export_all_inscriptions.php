<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/MARATHONS/lib/dompdf/autoload.inc.php";
require_once "../../Controller/InscriptionController.php";

use Dompdf\Dompdf;

$controller = new InscriptionController();
$liste = $controller->getAll();

// 🔥 HTML avec MEME STYLE
$html = '
<style>
body {
    font-family: Arial;
    background: #f7f6f2;
}

h1 {
    text-align:center;
    color:#0f766e;
}

table {
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

th {
    background:#0b2032;
    color:white;
    padding:12px;
    text-align:center;
}

td {
    padding:10px;
    text-align:center;
    border-bottom:1px solid #ddd;
}

tr:nth-child(even){
    background:#f0f4f8;
}
</style>

<h1>BarchaThon - Toutes les Inscriptions</h1>

<table>
<tr>
    <th>Date inscription</th>
    <th>Mode paiement</th>
    <th>Circuit</th>
    <th>Nb personnes</th>
    <th>Date paiement</th>
</tr>
';

foreach($liste as $row){
    $html .= "
    <tr>
        <td>{$row['date_inscription']}</td>
        <td>{$row['mode_de_paiement']}</td>
        <td>{$row['id_parcours']}</td>
        <td>{$row['nb_personnes']}</td>
        <td>{$row['date_paiement']}</td>
    </tr>";
}

$html .= "</table>";

// PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // 🔥 paysage pour tableau large
$dompdf->render();

$dompdf->stream("inscriptions.pdf");
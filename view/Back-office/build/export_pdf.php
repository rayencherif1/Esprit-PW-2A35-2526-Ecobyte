<?php

require_once __DIR__ . '/../../../Controller/allergie.Controller.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Initialisation
$controller = new AllergieC();
$allergies = $controller->listAllergie();

// Configuration DomPDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

// HTML du PDF
$html = '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <style>

        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 20px;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #2563eb;
            margin-bottom: 20px;
        }

        .date {
            text-align: right;
            margin-bottom: 20px;
            font-size: 12px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #2563eb;
            color: white;
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 13px;
        }

        td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 12px;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .faible {
            color: green;
            font-weight: bold;
        }

        .moyenne {
            color: orange;
            font-weight: bold;
        }

        .grave {
            color: red;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 11px;
            color: #777;
        }

    </style>
</head>

<body>

    <h1>🌿 Liste des Allergies</h1>

    <div class="date">
        Généré le : ' . date('d/m/Y H:i') . '
    </div>

    <table>

        <thead>
            <tr>
                <th>Nom</th>
                <th>Description</th>
                <th>Gravité</th>
                <th>Symptômes</th>
            </tr>
        </thead>

        <tbody>
';

// Génération des lignes
foreach ($allergies as $a) {

    $gravite = strtolower($a['gravite']);

    $html .= '
        <tr>

            <td>
                ' . htmlspecialchars($a['nom']) . '
            </td>

            <td>
                ' . htmlspecialchars($a['description']) . '
            </td>

            <td class="' . $gravite . '">
                ' . ucfirst(htmlspecialchars($a['gravite'])) . '
            </td>

            <td>
                ' . htmlspecialchars($a['symptomes']) . '
            </td>

        </tr>
    ';
}

$html .= '
        </tbody>

    </table>

    <div class="footer">
        Total des allergies : ' . count($allergies) . '
    </div>

</body>
</html>
';

// Charger le HTML
$dompdf->loadHtml($html);

// Format papier
$dompdf->setPaper('A4', 'landscape');

// Générer PDF
$dompdf->render();

// Télécharger
$dompdf->stream(
    "liste_allergies.pdf",
    ["Attachment" => true]
);

exit;
?>
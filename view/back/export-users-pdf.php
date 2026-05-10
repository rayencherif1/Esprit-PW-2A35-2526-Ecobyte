<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport des Utilisateurs - Ecobyte</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
        }
        .header p {
            margin: 5px 0;
            color: #7f8c8d;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            color: #2c3e50;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 12px;
            color: #95a5a6;
        }
        .no-print {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            padding: 10px 20px;
            border-radius: 8px;
        }
        .btn {
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }
        .btn-back {
            background: #95a5a6;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
            @page {
                margin: 1cm;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="?section=back&action=users" class="btn btn-back">Retour</a>
        <button onclick="window.print()" class="btn">Imprimer / Sauvegarder en PDF</button>
    </div>

    <div class="header">
        <h1>Rapport des Utilisateurs</h1>
        <p>Ecobyte - Système de Gestion</p>
        <p>Généré le : <?php echo date('d/m/Y H:i'); ?></p>
        <?php if (!empty($search)): ?>
            <p>Filtre de recherche : "<?php echo htmlspecialchars($search); ?>"</p>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom & Prénom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Physique</th>
                <th>Adresse</th>
                <th>Date d'inscription</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="7" style="text-align: center;">Aucun utilisateur trouvé.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo $u['id']; ?></td>
                    <td><?php echo htmlspecialchars($u['nom'] . ' ' . $u['prenom']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo htmlspecialchars($u['telephone'] ?? '-'); ?></td>
                    <td><?php echo ($u['poids'] ?? '-') . ' kg / ' . ($u['taille'] ?? '-') . ' cm'; ?></td>
                    <td><?php echo htmlspecialchars($u['adresse'] ?? '-'); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($u['date_creation'])); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>© <?php echo date('Y'); ?> Ecobyte - Document Confidentiel</p>
    </div>

    <script>
        // Auto-trigger print dialog after a short delay
        window.onload = function() {
            setTimeout(function() {
                // window.print();
            }, 500);
        };
    </script>
</body>
</html>

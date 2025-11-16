<?php
// rapoarte.php - Vizualizare împrumuturi și rapoarte
require_once 'config.php';

// Obține împrumuturi active
$stmt = $pdo->query("
    SELECT
        i.id,
        i.cod_cititor,
        i.cod_carte,
        i.data_imprumut,
        c.titlu,
        c.autor,
        c.locatie_completa,
        cit.nume,
        cit.prenume,
        cit.telefon,
        DATEDIFF(NOW(), i.data_imprumut) as zile_imprumut
    FROM imprumuturi i
    JOIN carti c ON i.cod_carte = c.cod_bare
    JOIN cititori cit ON i.cod_cititor = cit.cod_bare
    WHERE i.status = 'activ'
    ORDER BY i.data_imprumut DESC
");
$imprumuturi_active = $stmt->fetchAll();

// Obține istoric returnări (ultimele 50)
$stmt = $pdo->query("
    SELECT
        i.id,
        i.cod_cititor,
        i.cod_carte,
        i.data_imprumut,
        i.data_returnare,
        c.titlu,
        c.autor,
        cit.nume,
        cit.prenume,
        DATEDIFF(i.data_returnare, i.data_imprumut) as zile_total
    FROM imprumuturi i
    JOIN carti c ON i.cod_carte = c.cod_bare
    JOIN cititori cit ON i.cod_cititor = cit.cod_bare
    WHERE i.status = 'returnat'
    ORDER BY i.data_returnare DESC
    LIMIT 50
");
$istoric = $stmt->fetchAll();

// Obține statistici generale
$statistici = [
    'total_carti' => $pdo->query("SELECT COUNT(*) FROM carti")->fetchColumn(),
    'total_cititori' => $pdo->query("SELECT COUNT(*) FROM cititori")->fetchColumn(),
    'imprumuturi_active' => count($imprumuturi_active),
    'total_imprumuturi' => $pdo->query("SELECT COUNT(*) FROM imprumuturi")->fetchColumn(),
    'carti_returnate' => $pdo->query("SELECT COUNT(*) FROM imprumuturi WHERE status = 'returnat'")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapoarte Bibliotecă</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 15px;
            padding: 20px 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header {
            background: white;
            border-radius: 15px;
            padding: 20px 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #667eea;
            font-size: 2em;
            margin: 0;
        }

        .header-buttons {
            display: flex;
            gap: 10px;
        }

        .home-btn, .back-btn {
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
            font-weight: 600;
        }

        .home-btn {
            background: #28a745;
        }

        .home-btn:hover {
            background: #218838;
        }

        .back-btn {
            background: #667eea;
        }

        .back-btn:hover {
            background: #764ba2;
        }

        .section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .section h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-card h3 {
            font-size: 2em;
            margin-bottom: 5px;
        }

        .stat-card p {
            opacity: 0.9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #667eea;
            color: white;
            font-weight: 600;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 1.2em;
        }

        .location-info {
            font-size: 0.9em;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Rapoarte Bibliotecă</h1>
            <div class="header-buttons">
                <a href="index.php" class="home-btn">🏠 Acasă</a>
                <a href="index.php" class="back-btn">← Înapoi la scanare</a>
            </div>
        </div>

        <!-- Statistici generale -->
        <div class="section">
            <h2>📈 Statistici Generale</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $statistici['total_carti']; ?></h3>
                    <p>Total cărți în bibliotecă</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $statistici['total_cititori']; ?></h3>
                    <p>Cititori înregistrați</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $statistici['imprumuturi_active']; ?></h3>
                    <p>Împrumuturi active</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $statistici['total_imprumuturi']; ?></h3>
                    <p>Total împrumuturi</p>
                </div>
            </div>
        </div>

        <!-- Împrumuturi active -->
        <div class="section">
            <h2>📤 Împrumuturi active (<?php echo count($imprumuturi_active); ?>)</h2>
            <?php if (count($imprumuturi_active) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Cititor</th>
                            <th>Telefon</th>
                            <th>Carte</th>
                            <th>Autor</th>
                            <th>Locație</th>
                            <th>Data împrumut</th>
                            <th>Zile împrumut</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($imprumuturi_active as $imp): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($imp['nume'] . ' ' . $imp['prenume']); ?></strong></td>
                                <td><?php echo htmlspecialchars($imp['telefon']); ?></td>
                                <td><?php echo htmlspecialchars($imp['titlu']); ?></td>
                                <td><?php echo htmlspecialchars($imp['autor']); ?></td>
                                <td><span class="location-info"><?php echo htmlspecialchars($imp['locatie_completa']); ?></span></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($imp['data_imprumut'])); ?></td>
                                <td><?php echo $imp['zile_imprumut']; ?> zile</td>
                                <td>
                                    <?php
                                    if ($imp['zile_imprumut'] > 30) {
                                        echo '<span class="badge badge-danger">Întârziere!</span>';
                                    } elseif ($imp['zile_imprumut'] > 14) {
                                        echo '<span class="badge badge-warning">Atenție</span>';
                                    } else {
                                        echo '<span class="badge badge-success">OK</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">📭 Nu există împrumuturi active</div>
            <?php endif; ?>
        </div>

        <!-- Istoric returnări -->
        <div class="section">
            <h2>📥 Istoric returnări (ultimele 50)</h2>
            <?php if (count($istoric) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Cititor</th>
                            <th>Carte</th>
                            <th>Autor</th>
                            <th>Data împrumut</th>
                            <th>Data returnare</th>
                            <th>Zile împrumut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($istoric as $ist): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($ist['nume'] . ' ' . $ist['prenume']); ?></strong></td>
                                <td><?php echo htmlspecialchars($ist['titlu']); ?></td>
                                <td><?php echo htmlspecialchars($ist['autor']); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($ist['data_imprumut'])); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($ist['data_returnare'])); ?></td>
                                <td>
                                    <span class="badge badge-info"><?php echo $ist['zile_total']; ?> zile</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">📭 Nu există istoric de returnări</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

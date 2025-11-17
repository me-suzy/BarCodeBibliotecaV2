<?php
require_once 'config.php';
require_once 'functions_vizare.php';

$cititori_nevizati = getCitoriNevizati($pdo);
$an_curent = date('Y');
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Lista Permise Nevizate - <?= $an_curent ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">⚠️ Cititori cu Permise NEVIZATE pentru <?= $an_curent ?></h1>
        
        <div class="alert alert-warning">
            <strong>Total nevizați: <?= count($cititori_nevizati) ?></strong>
        </div>

        <table class="table table-striped table-hover">
            <thead class="table-danger">
                <tr>
                    <th>Cod Bare</th>
                    <th>Nume</th>
                    <th>Prenume</th>
                    <th>Email</th>
                    <th>Telefon</th>
                    <th>Ultima Vizare</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cititori_nevizati as $cititor): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($cititor['cod_bare']) ?></strong></td>
                    <td><?= htmlspecialchars($cititor['nume']) ?></td>
                    <td><?= htmlspecialchars($cititor['prenume']) ?></td>
                    <td><?= htmlspecialchars($cititor['email']) ?></td>
                    <td><?= htmlspecialchars($cititor['telefon']) ?></td>
                    <td>
                        <?php if ($cititor['ultima_vizare']): ?>
                            <span class="badge bg-warning"><?= date('d.m.Y', strtotime($cititor['ultima_vizare'])) ?></span>
                        <?php else: ?>
                            <span class="badge bg-danger">Niciodată</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="scanare_rapida.php" class="btn btn-primary">← Înapoi la Scanare</a>
    </div>
</body>
</html>
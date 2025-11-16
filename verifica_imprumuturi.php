<?php
// Script pentru verificare și adăugare împrumuturi
require_once 'config.php';

echo "<h1>🔍 Verificare și adăugare împrumuturi</h1>";

// Verifică câte împrumuturi există deja
$stmt = $pdo->query("SELECT COUNT(*) FROM imprumuturi WHERE status = 'activ'");
$active = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM imprumuturi WHERE status = 'returnat'");
$returned = $stmt->fetchColumn();

echo "<h2>📊 Situație actuală:</h2>";
echo "<p>Împrumuturi active: <strong>$active</strong></p>";
echo "<p>Împrumuturi returnate: <strong>$returned</strong></p>";
echo "<p>Total împrumuturi: <strong>" . ($active + $returned) . "</strong></p>";

// Dacă nu avem suficiente împrumuturi, adăugăm câteva esențiale
if ($active < 5) {
    echo "<hr><h2>🚀 Adăugare împrumuturi esențiale...</h2>";

    $imprumuturi_esentiale = [
        // Împrumuturi foarte recente pentru testare imediată
        ['cod_cititor' => 'USER001', 'cod_carte' => 'BOOK001', 'data_imprumut' => date('Y-m-d H:i:s', strtotime('-30 minutes')), 'status' => 'activ'],
        ['cod_cititor' => 'USER002', 'cod_carte' => 'BOOK002', 'data_imprumut' => date('Y-m-d H:i:s', strtotime('-2 hours')), 'status' => 'activ'],
        ['cod_cititor' => 'USER003', 'cod_carte' => 'BOOK003', 'data_imprumut' => date('Y-m-d H:i:s', strtotime('-1 day')), 'status' => 'activ'],
        ['cod_cititor' => 'USER004', 'cod_carte' => 'BOOK004', 'data_imprumut' => date('Y-m-d H:i:s', strtotime('-3 days')), 'status' => 'activ'],
        ['cod_cititor' => 'USER005', 'cod_carte' => 'BOOK005', 'data_imprumut' => date('Y-m-d H:i:s', strtotime('-1 week')), 'status' => 'activ'],

        // Unele returnate pentru istoric
        ['cod_cititor' => 'USER001', 'cod_carte' => 'BOOK006', 'data_imprumut' => date('Y-m-d H:i:s', strtotime('-10 days')), 'data_returnare' => date('Y-m-d H:i:s', strtotime('-8 days')), 'status' => 'returnat'],
        ['cod_cititor' => 'USER002', 'cod_carte' => 'BOOK007', 'data_imprumut' => date('Y-m-d H:i:s', strtotime('-15 days')), 'data_returnare' => date('Y-m-d H:i:s', strtotime('-12 days')), 'status' => 'returnat'],
    ];

    $adaugate = 0;
    foreach ($imprumuturi_esentiale as $imprumut) {
        try {
            // Verifică dacă împrumutul există deja (cititor + carte activ)
            $check_stmt = $pdo->prepare("
                SELECT id FROM imprumuturi
                WHERE cod_cititor = ? AND cod_carte = ? AND status = ?
            ");
            $check_stmt->execute([$imprumut['cod_cititor'], $imprumut['cod_carte'], $imprumut['status']]);

            if ($check_stmt->rowCount() == 0) {
                // Nu există, adăugăm
                $stmt = $pdo->prepare("
                    INSERT INTO imprumuturi (cod_cititor, cod_carte, data_imprumut, data_returnare, status)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $data_returnare = isset($imprumut['data_returnare']) ? $imprumut['data_returnare'] : null;

                $stmt->execute([
                    $imprumut['cod_cititor'],
                    $imprumut['cod_carte'],
                    $imprumut['data_imprumut'],
                    $data_returnare,
                    $imprumut['status']
                ]);

                echo "<p style='color: green;'>✅ Adăugat: {$imprumut['cod_cititor']} → {$imprumut['cod_carte']} ({$imprumut['status']})</p>";
                $adaugate++;
            } else {
                echo "<p style='color: orange;'>⚠️ Există deja: {$imprumut['cod_cititor']} → {$imprumut['cod_carte']} ({$imprumut['status']})</p>";
            }

        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Eroare: {$imprumut['cod_cititor']} → {$imprumut['cod_carte']}: " . $e->getMessage() . "</p>";
        }
    }

    if ($adaugate > 0) {
        echo "<p style='color: blue; font-weight: bold;'>📊 Au fost adăugate $adaugate împrumuturi noi!</p>";
    }
}

// Verificare finală
$stmt = $pdo->query("SELECT COUNT(*) FROM imprumuturi WHERE status = 'activ'");
$active_final = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM imprumuturi WHERE status = 'returnat'");
$returned_final = $stmt->fetchColumn();

echo "<hr><h2>✅ Situație finală:</h2>";
echo "<p>Împrumuturi active: <strong>$active_final</strong></p>";
echo "<p>Împrumuturi returnate: <strong>$returned_final</strong></p>";
echo "<p>Total împrumuturi: <strong>" . ($active_final + $returned_final) . "</strong></p>";

if ($active_final > 0) {
    echo "<p style='color: green; font-size: 18px; margin-top: 20px;'>🎉 Sistemul are acum împrumuturi active de testat!</p>";
    echo "<p><a href='imprumuturi.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📖 Vezi împrumuturile active</a></p>";
} else {
    echo "<p style='color: red; font-size: 18px; margin-top: 20px;'>❌ Încă nu avem împrumuturi active. Contactează suportul.</p>";
}

echo "<p><a href='index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px; display: inline-block;'>🏠 Înapoi la bibliotecă</a></p>";
?>

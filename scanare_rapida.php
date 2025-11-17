<?php
require_once 'config.php';
require_once 'functions_vizare.php';
session_start();

$mesaj = '';
$tip_mesaj = '';
$status_vizare = null;
$cod_cititor_curent = null;

// Procesare vizare permis
if (isset($_POST['vizeaza_permis'])) {
    $cod_cititor = trim($_POST['cod_cititor_vizare']);
    $rezultat = vizeazaPermis($pdo, $cod_cititor);
    
    if ($rezultat['success']) {
        $mesaj = $rezultat['mesaj'];
        $tip_mesaj = 'success';
        $status_vizare = verificaVizarePermis($pdo, $cod_cititor);
        $cod_cititor_curent = $cod_cititor;
        
        $stmt = $pdo->prepare("SELECT * FROM cititori WHERE cod_bare = ?");
        $stmt->execute([$cod_cititor]);
        $_SESSION['cititor_activ'] = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $mesaj = $rezultat['mesaj'];
        $tip_mesaj = 'danger';
    }
}

// Procesare scanare cod de bare
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['vizeaza_permis'])) {
    $cod_scanat = trim($_POST['cod_scanat'] ?? '');

    if (empty($cod_scanat)) {
        $mesaj = "⚠️ Cod invalid!";
        $tip_mesaj = "warning";
    } else {
        try {
            // Detectează CITITOR (USER***)
            if (preg_match('/^USER\d+$/i', $cod_scanat)) {
                $stmt = $pdo->prepare("SELECT * FROM cititori WHERE cod_bare = ?");
                $stmt->execute([$cod_scanat]);
                $cititor = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($cititor) {
                    $_SESSION['cititor_activ'] = $cititor;
                    
                    // ← NOU: VIZARE AUTOMATĂ la prima scanare din an nou
                    $rezultat_auto_vizare = vizeazaPermisAutomat($pdo, $cod_scanat);
                    
                    // Verifică status vizare DUPĂ vizare automată
                    $status_vizare = verificaVizarePermis($pdo, $cod_scanat);
                    $cod_cititor_curent = $cod_scanat;
                    
                    // Înregistrează sesiune
                    $stmt = $pdo->prepare("INSERT INTO sesiuni_biblioteca (cod_cititor, data, ora_intrare, timestamp_intrare) VALUES (?, CURDATE(), CURTIME(), NOW())");
                    $stmt->execute([$cod_scanat]);
                    
                    // Mesaj diferit pentru vizare automată
                    if ($rezultat_auto_vizare['vizat'] && strpos($rezultat_auto_vizare['mesaj'], 'AUTOMAT') !== false) {
                        $mesaj = $rezultat_auto_vizare['mesaj'] . "<br>Bun venit, {$cititor['nume']} {$cititor['prenume']}!";
                        $tip_mesaj = "success";
                    } elseif ($status_vizare['vizat']) {
                        $mesaj = "✅ Bun venit, {$cititor['nume']} {$cititor['prenume']}! Permis VIZAT.";
                        $tip_mesaj = "success";
                    } else {
                        $mesaj = "⚠️ Bun venit, {$cititor['nume']} {$cititor['prenume']}! ATENȚIE: " . $status_vizare['mesaj'];
                        $tip_mesaj = "warning";
                    }
                } else {
                    $mesaj = "❌ Cititor necunoscut: $cod_scanat";
                    $tip_mesaj = "danger";
                }

            // Detectează CARTE (BOOK***)
            } elseif (preg_match('/^BOOK\d+$/i', $cod_scanat)) {
                if (!isset($_SESSION['cititor_activ'])) {
                    $mesaj = "⚠️ Scanați mai întâi carnetul cititorului!";
                    $tip_mesaj = "warning";
                } else {
                    // Verifică vizare ÎNAINTE de împrumut
                    $status_vizare_temp = verificaVizarePermis($pdo, $_SESSION['cititor_activ']['cod_bare']);
                    
                    if (!$status_vizare_temp['vizat']) {
                        $mesaj = "🔴 ÎMPRUMUT BLOCAT! Permisul nu este vizat pentru anul curent!";
                        $tip_mesaj = "danger";
                        $status_vizare = $status_vizare_temp;
                        $cod_cititor_curent = $_SESSION['cititor_activ']['cod_bare'];
                    } else {
                        // Procesare împrumut normal
                        $stmt = $pdo->prepare("SELECT * FROM carti WHERE cod_bare = ?");
                        $stmt->execute([$cod_scanat]);
                        $carte = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($carte) {
                            $stmt = $pdo->prepare("SELECT * FROM imprumuturi WHERE cod_carte = ? AND data_returnare IS NULL");
                            $stmt->execute([$cod_scanat]);
                            $imprumut_existent = $stmt->fetch(PDO::FETCH_ASSOC);

                            if ($imprumut_existent) {
                                // RETURNARE
                                $stmt = $pdo->prepare("UPDATE imprumuturi SET data_returnare = NOW() WHERE id = ?");
                                $stmt->execute([$imprumut_existent['id']]);
                                $mesaj = "📚 Carte returnată: {$carte['titlu']}";
                                $tip_mesaj = "info";
                            } else {
                                // ÎMPRUMUT NOU
                                $data_scadenta = date('Y-m-d', strtotime('+14 days'));
                                $stmt = $pdo->prepare("INSERT INTO imprumuturi (cod_cititor, cod_carte, data_imprumut, data_scadenta) VALUES (?, ?, NOW(), ?)");
                                $stmt->execute([
                                    $_SESSION['cititor_activ']['cod_bare'],
                                    $cod_scanat,
                                    $data_scadenta
                                ]);
                                $mesaj = "✅ Carte împrumutată: {$carte['titlu']} (Scadență: $data_scadenta)";
                                $tip_mesaj = "success";
                            }
                        } else {
                            $mesaj = "❌ Carte necunoscută: $cod_scanat";
                            $tip_mesaj = "danger";
                        }
                    }
                }
            } else {
                $mesaj = "⚠️ Cod necunoscut: $cod_scanat";
                $tip_mesaj = "warning";
            }

        } catch (PDOException $e) {
            $mesaj = "❌ Eroare: " . $e->getMessage();
            $tip_mesaj = "danger";
        }
    }
}

// Încarcă datele cititorului activ pentru afișare
$cititor_activ = $_SESSION['cititor_activ'] ?? null;

// Dacă avem cititor activ, verifică status vizare
if ($cititor_activ && !$status_vizare) {
    $status_vizare = verificaVizarePermis($pdo, $cititor_activ['cod_bare']);
    $cod_cititor_curent = $cititor_activ['cod_bare'];
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanare Rapidă - Bibliotecă</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .scan-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .scan-input {
            font-size: 2rem;
            text-align: center;
            border: 3px solid #667eea;
            border-radius: 15px;
            padding: 20px;
        }
        .scan-input:focus {
            border-color: #764ba2;
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
        }
        
        @keyframes pulse-red {
            0%, 100% { 
                transform: scale(1); 
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            }
            50% { 
                transform: scale(1.05); 
                box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
            }
        }
        
        .status-vizare-container {
            margin: 30px 0;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
        }
        
        .status-vizare-nevizat {
            background: #ffe6e6;
            border: 3px solid #dc3545;
            animation: pulse-red 2s infinite;
        }
        
        .status-vizare-vizat {
            background: #d4edda;
            border: 3px solid #28a745;
        }
        
        .btn-vizeaza {
            font-size: 1.3rem;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: bold;
            margin-top: 15px;
            animation: pulse-red 2s infinite;
        }
        
        .cititor-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .icon-large {
            font-size: 3rem;
            display: block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="scan-container">
        <h1 class="text-center mb-4">📚 Scanare Rapidă Bibliotecă</h1>

        <?php if ($mesaj): ?>
        <div class="alert alert-<?= $tip_mesaj ?> alert-dismissible fade show" role="alert">
            <?= $mesaj ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($status_vizare && $cod_cititor_curent): ?>
        <div class="status-vizare-container <?= $status_vizare['vizat'] ? 'status-vizare-vizat' : 'status-vizare-nevizat' ?>">
            <span class="icon-large"><?= $status_vizare['icon'] ?></span>
            <h3><?= $status_vizare['mesaj'] ?></h3>
            
            <?php if ($status_vizare['data_vizare']): ?>
            <p class="mb-0">Data vizare: <strong><?= date('d.m.Y', strtotime($status_vizare['data_vizare'])) ?></strong></p>
            <?php endif; ?>
            
            <?php if (!$status_vizare['vizat']): ?>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="cod_cititor_vizare" value="<?= htmlspecialchars($cod_cititor_curent) ?>">
                <button type="submit" name="vizeaza_permis" class="btn btn-danger btn-vizeaza">
                    ⚠️ VIZEAZĂ PERMIS ACUM
                </button>
            </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($cititor_activ): ?>
        <div class="cititor-info">
            <h5>👤 Cititor activ:</h5>
            <p class="mb-0">
                <strong><?= htmlspecialchars($cititor_activ['nume'] . ' ' . $cititor_activ['prenume']) ?></strong><br>
                <small class="text-muted"><?= htmlspecialchars($cititor_activ['cod_bare']) ?></small>
            </p>
        </div>
        <?php endif; ?>

        <form method="POST" id="formScanare">
            <div class="mb-3">
                <input 
                    type="text" 
                    name="cod_scanat" 
                    id="cod_scanat" 
                    class="form-control scan-input" 
                    placeholder="Scanați codul de bare..." 
                    autofocus
                    autocomplete="off">
            </div>
        </form>

<div class="d-grid gap-2">
    <a href="imprumuturi.php" class="btn btn-primary btn-lg">📋 Vezi Împrumuturi</a>
    <a href="raport_prezenta.php" class="btn btn-info btn-lg">📊 Raport Prezență</a>
    <a href="lista_nevizati.php" class="btn btn-warning btn-lg">⚠️ Lista Permise Nevizate</a>
    <button type="button" class="btn btn-secondary btn-lg" onclick="location.href='?reset=1'">🔄 Resetează Cititor</button>
</div>

<!-- Buton Acasă separat, verde și mai mic -->
<div class="d-flex justify-content-center mt-3">
    <a href="index.php" class="btn btn-success btn-lg w-50">🏠 Acasă</a>
</div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus pe câmp după submit
        document.getElementById('cod_scanat').focus();
    </script>
</body>
</html>
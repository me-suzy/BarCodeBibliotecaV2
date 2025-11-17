<?php
// index.php - Pagina principală cu scanare coduri de bare
require_once 'config.php';
session_start();

// Acțiune pentru resetare cititor
if (isset($_GET['actiune']) && $_GET['actiune'] === 'reseteaza_cititor') {
    unset($_SESSION['cititor_activ']);
    header('Location: index.php');
    exit;
}

// Procesare formular
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actiune = $_POST['actiune'] ?? '';
    $cod_cititor = trim($_POST['cod_cititor'] ?? '');
    $cod_carte = trim($_POST['cod_carte'] ?? '');

    if ($actiune === 'selecteaza_cititor' && $cod_cititor) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM cititori WHERE cod_bare = ?");
            $stmt->execute([$cod_cititor]);
            $cititor = $stmt->fetch();

            if ($cititor) {
                $_SESSION['cititor_activ'] = [
                    'cod_bare' => $cititor['cod_bare'],
                    'nume' => $cititor['nume'],
                    'prenume' => $cititor['prenume']
                ];
                $mesaj = "✅ Cititor selectat: {$cititor['nume']} {$cititor['prenume']}";
                $tip_mesaj = "success";
            } else {
                $mesaj = "⚠️ Cititorul nu există în baza de date!";
                $tip_mesaj = "warning";
            }
        } catch (PDOException $e) {
            $mesaj = "❌ Eroare: " . $e->getMessage();
            $tip_mesaj = "danger";
        }
    }

    if ($actiune === 'imprumuta' && $cod_carte) {
        try {
            if (!isset($_SESSION['cititor_activ'])) {
                $mesaj = "⚠️ Selectează ÎNTÂI cititorul!";
                $tip_mesaj = "warning";
            } else {
                // Verifică dacă cartea există
                $stmt = $pdo->prepare("SELECT * FROM carti WHERE cod_bare = ?");
                $stmt->execute([$cod_carte]);
                $carte = $stmt->fetch();

                if (!$carte) {
                    $mesaj = "⚠️ Cartea nu există în baza de date!";
                    $tip_mesaj = "warning";
                } else {
                    // Verifică dacă cartea este deja împrumutată
                    $stmt = $pdo->prepare("SELECT * FROM imprumuturi WHERE cod_carte = ? AND status = 'activ'");
                    $stmt->execute([$cod_carte]);

                    if ($stmt->rowCount() > 0) {
                        $mesaj = "⚠️ Cartea '{$carte['titlu']}' este deja împrumutată!";
                        $tip_mesaj = "warning";
                    } else {
                        // Înregistrează împrumutul
                        $cod_cititor = $_SESSION['cititor_activ']['cod_bare'];
                        $stmt = $pdo->prepare("INSERT INTO imprumuturi (cod_cititor, cod_carte, status) VALUES (?, ?, 'activ')");
                        $stmt->execute([$cod_cititor, $cod_carte]);

                        $mesaj = "✅ Carte împrumutată cu succes!\n" .
                                "📕 {$carte['titlu']}\n" .
                                "👤 {$_SESSION['cititor_activ']['nume']} {$_SESSION['cititor_activ']['prenume']}";
                        $tip_mesaj = "success";
                    }
                }
            }
        } catch (PDOException $e) {
            $mesaj = "❌ Eroare: " . $e->getMessage();
            $tip_mesaj = "danger";
        }
    }

    if ($actiune === 'returneaza' && $cod_carte) {
        try {
            $stmt = $pdo->prepare("
                SELECT i.*, c.nume, c.prenume 
                FROM imprumuturi i
                JOIN cititori c ON i.cod_cititor = c.cod_bare
                WHERE i.cod_carte = ? AND i.status = 'activ'
            ");
            $stmt->execute([$cod_carte]);
            $imprumut = $stmt->fetch();

            if ($imprumut) {
                $update_stmt = $pdo->prepare("
                    UPDATE imprumuturi
                    SET status = 'returnat', data_returnare = NOW()
                    WHERE cod_carte = ? AND status = 'activ'
                ");
                $update_stmt->execute([$cod_carte]);

                $mesaj = "✅ Cartea a fost returnată cu succes!\n" .
                         "Împrumutată de: " . htmlspecialchars($imprumut['nume'] . ' ' . $imprumut['prenume']) .
                         " (" . htmlspecialchars($imprumut['cod_cititor']) . ")";
                $tip_mesaj = "success";
            } else {
                $mesaj = "⚠️ Cartea nu este împrumutată!";
                $tip_mesaj = "warning";
            }
        } catch (PDOException $e) {
            $mesaj = "❌ Eroare: " . $e->getMessage();
            $tip_mesaj = "danger";
        }
    }
}

// Obține statistici
$total_carti = $pdo->query("SELECT COUNT(*) FROM carti")->fetchColumn();
$total_cititori = $pdo->query("SELECT COUNT(*) FROM cititori")->fetchColumn();
$carti_imprumutate = $pdo->query("SELECT COUNT(*) FROM imprumuturi WHERE status = 'activ'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Bibliotecă - Scanare Coduri de Bare</title>
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
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .header h1 {
            color: #667eea;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card.clickable {
            cursor: pointer;
            text-decoration: none;
            display: block;
        }

        .stat-card.clickable:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }

        .stat-card h3 {
            font-size: 2em;
            margin-bottom: 5px;
        }

        .stat-card p {
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .cititor-activ {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cititor-info h2 {
            font-size: 1.8em;
            margin-bottom: 5px;
        }

        .cititor-info p {
            opacity: 0.9;
            font-size: 1.1em;
        }

        .btn-reseteaza {
            background: rgba(255,255,255,0.3);
            color: white;
            border: 2px solid white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-reseteaza:hover {
            background: white;
            color: #11998e;
        }

        .scan-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .scan-section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.8em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
            font-size: 1.1em;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1.1em;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .button-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 25px;
        }

        button {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-selecteaza {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-imprumuta {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .btn-returneaza {
            background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
            color: white;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 1.1em;
            font-weight: 500;
            white-space: pre-line;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

.nav-links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); 
    /* ← Se ajustează automat: desktop = 4 pe linie, mobile = 2 pe linie */
    gap: 15px;
    margin-top: 20px;
}

.nav-links a {
    padding: 10px 20px;
    background: #667eea;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    transition: background 0.3s;
    text-align: center;
}

.nav-links a:hover {
    background: #764ba2;
}

    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 Sistem Bibliotecă</h1>
            <p style="color: #666; font-size: 1.1em;">Scanare coduri de bare pentru împrumuturi</p>

            <div class="stats">
                <a href="carti.php" class="stat-card clickable">
                    <h3><?php echo $total_carti; ?></h3>
                    <p>Total cărți</p>
                </a>
                <a href="cititori.php" class="stat-card clickable">
                    <h3><?php echo $total_cititori; ?></h3>
                    <p>Cititori înregistrați</p>
                </a>
                <a href="imprumuturi.php" class="stat-card clickable">
                    <h3><?php echo $carti_imprumutate; ?></h3>
                    <p>Cărți împrumutate</p>
                </a>
            </div>

<div class="nav-links">
    <a href="rapoarte.php">📊 Rapoarte</a>
    <a href="scanare_rapida.php">🔍 Scanare Rapidă</a>
    <a href="imprumuturi.php">📋 Listă Împrumuturi</a>
    <a href="raport_prezenta.php">📈 Raport Prezență</a>
    <a href="status_vizari.php">✅ Status Vizări</a> <!-- ← NOU -->
    <a href="lista_nevizati.php">⚠️ Doar Nevizați</a>
    <a href="adauga_carte.php">➕ Adaugă carte</a>
    <a href="adauga_cititor.php">👤 Adaugă cititor</a>
</div>

        </div>

        <?php if (isset($mesaj)): ?>
            <div class="alert alert-<?php echo $tip_mesaj; ?>">
                <?php echo $mesaj; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['cititor_activ'])): ?>
            <div class="cititor-activ">
                <div class="cititor-info">
                    <h2>👤 Cititor activ: <?php echo htmlspecialchars($_SESSION['cititor_activ']['nume'] . ' ' . $_SESSION['cititor_activ']['prenume']); ?></h2>
                    <p>Cod: <?php echo htmlspecialchars($_SESSION['cititor_activ']['cod_bare']); ?></p>
                </div>
                <a href="?actiune=reseteaza_cititor" class="btn-reseteaza">🔄 Schimbă cititor</a>
            </div>
        <?php endif; ?>

        <div class="scan-section">
            <h2>🔍 Scanare Coduri</h2>

            <?php if (!isset($_SESSION['cititor_activ'])): ?>
                <!-- Pas 1: Selectează cititor -->
                <form method="POST">
                    <div class="form-group">
                        <label for="cod_cititor">1️⃣ Scanează carnetul cititorului:</label>
                        <input type="text"
                               id="cod_cititor"
                               name="cod_cititor"
                               placeholder="Scanează sau introdu codul cititorului (ex: USER001)"
                               autofocus
                               required>
                    </div>
                    <button type="submit" name="actiune" value="selecteaza_cititor" class="btn-selecteaza">
                        ✅ Selectează cititor
                    </button>
                </form>
            <?php else: ?>
                <!-- Pas 2: Scanează cărți -->
                <form method="POST" id="scanForm">
                    <div class="form-group">
                        <label for="cod_carte">📖 Scanează codul cărții:</label>
                        <input type="text"
                               id="cod_carte"
                               name="cod_carte"
                               placeholder="Scanează sau introdu codul cărții (ex: BOOK001)"
                               autofocus
                               required>
                    </div>

                    <div class="button-group">
                        <button type="submit" name="actiune" value="imprumuta" class="btn-imprumuta">
                            📤 Împrumută carte
                        </button>
                        <button type="submit" name="actiune" value="returneaza" class="btn-returneaza">
                            📥 Returnează carte
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        <?php if (isset($mesaj) && $tip_mesaj === 'success'): ?>
            setTimeout(() => {
                document.getElementById('scanForm')?.reset();
                document.getElementById('cod_carte')?.focus();
            }, 1500);
        <?php endif; ?>
    </script>
</body>
</html>
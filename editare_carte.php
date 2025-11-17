<?php
// editare_carte.php - Editare date carte
require_once 'config.php';

$mesaj = '';
$tip_mesaj = '';

// Verifică dacă avem ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID carte lipsă');
}

$id = (int)$_GET['id'];

// Obține datele cărții
$stmt = $pdo->prepare("SELECT * FROM carti WHERE id = ?");
$stmt->execute([$id]);
$carte = $stmt->fetch();

if (!$carte) {
    die('Cartea nu a fost găsită');
}

// Procesare ștergere
if (isset($_POST['delete']) && $_POST['delete'] === 'true') {
    try {
        // Verifică dacă cartea are împrumuturi în istoric
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM imprumuturi WHERE cod_carte = ?");
        $check_stmt->execute([$carte['cod_bare']]);
        $imprumuturi_count = $check_stmt->fetchColumn();

        $sterge_istoric = isset($_POST['sterge_istoric']) && $_POST['sterge_istoric'] === 'true';

        if ($imprumuturi_count > 0 && !$sterge_istoric) {
            // Nu are permisiune să șteargă istoric, arată warning
            $mesaj = "❌ Nu poți șterge această carte!\n\n" .
                     "Cartea are {$imprumuturi_count} împrumuturi în istoric.\n\n" .
                     "Dacă vrei să ștergi cartea împreună cu tot istoricul, " .
                     "apasă butonul 'Șterge cu tot istoricul' de jos.";
            $tip_mesaj = "danger";
        } else {
            // Are permisiune SAU nu are împrumuturi
            if ($imprumuturi_count > 0) {
                // Șterge mai întâi împrumuturile
                $del_stmt = $pdo->prepare("DELETE FROM imprumuturi WHERE cod_carte = ?");
                $del_stmt->execute([$carte['cod_bare']]);
            }
            
            // Acum șterge cartea
            $stmt = $pdo->prepare("DELETE FROM carti WHERE id = ?");
            $stmt->execute([$id]);
            
            echo "<script>alert('✅ Cartea și {$imprumuturi_count} împrumuturi au fost șterse!'); window.location.href='carti.php';</script>";
            exit;
        }
    } catch (PDOException $e) {
        $mesaj = "❌ Eroare la ștergere: " . $e->getMessage();
        $tip_mesaj = "danger";
    }
}

// Procesare formular
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete'])) {
    $cod_bare = trim($_POST['cod_bare'] ?? '');
    $titlu = trim($_POST['titlu'] ?? '');
    $autor = trim($_POST['autor'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $cota = trim($_POST['cota'] ?? '');
    $raft = trim($_POST['raft'] ?? '');
    $nivel = trim($_POST['nivel'] ?? '');
    $pozitie = trim($_POST['pozitie'] ?? '');
    $sectiune = trim($_POST['sectiune'] ?? '');
    $observatii_locatie = trim($_POST['observatii_locatie'] ?? '');

    try {
        $stmt = $pdo->prepare("
            UPDATE carti
            SET cod_bare = ?, titlu = ?, autor = ?, isbn = ?, cota = ?,
                raft = ?, nivel = ?, pozitie = ?, sectiune = ?, observatii_locatie = ?
            WHERE id = ?
        ");
        $stmt->execute([$cod_bare, $titlu, $autor, $isbn, $cota, $raft, $nivel, $pozitie, $sectiune, $observatii_locatie, $id]);

        $mesaj = "✅ Cartea a fost actualizată cu succes!";
        $tip_mesaj = "success";

        // Reîncarcă datele
        $stmt = $pdo->prepare("SELECT * FROM carti WHERE id = ?");
        $stmt->execute([$id]);
        $carte = $stmt->fetch();

    } catch (PDOException $e) {
        $mesaj = "❌ Eroare: " . $e->getMessage();
        $tip_mesaj = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editare Carte - Sistem Bibliotecă</title>
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
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        h1 {
            color: #667eea;
            margin-bottom: 30px;
            font-size: 2.2em;
            text-align: center;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group-full {
            grid-column: 1 / -1;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
            font-size: 1em;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .required {
            color: #dc3545;
            font-weight: bold;
        }

        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .delete-btn {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            margin-top: 15px;
        }

        .delete-btn:hover {
            background: linear-gradient(135deg, #c82333 0%, #a02622 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,0,0,0.2);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .action-buttons a, .action-buttons button {
            flex: 1;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            grid-column: 1 / -1;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .back-link, .home-link {
            display: inline-block;
            margin-top: 20px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .home-link {
            background: #28a745;
            margin-right: 10px;
        }

        .home-link:hover {
            background: #218838;
        }

        .back-link {
            background: #667eea;
            color: white;
        }

        .back-link:hover {
            background: #764ba2;
        }

        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            grid-column: 1 / -1;
        }

        .info-box h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        .location-preview {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📚 Editare Carte</h1>

        <div class="info-box">
            <h3>💡 Informații utile</h3>
            <ul style="margin-left: 20px;">
                <li>Codul de bare trebuie să fie unic în sistem</li>
                <li>Locația ajută cititorii să găsească cartea rapid</li>
                <li>Cota este clasificarea bibliotecară standard</li>
            </ul>
        </div>

        <?php if (isset($mesaj)): ?>
            <div class="alert alert-<?php echo $tip_mesaj; ?>">
                <?php echo $mesaj; ?>
            </div>
        <?php endif; ?>

		<form method="POST" id="carteForm">
			<div class="form-grid">
				<!-- Informații de bază -->
				<div class="form-group">
					<label>Cod de bare <span class="required">*</span></label>
					<input type="text" name="cod_bare" value="<?php echo htmlspecialchars($carte['cod_bare']); ?>" required>
				</div>

				<div class="form-group">
					<label>Titlu <span class="required">*</span></label>
					<input type="text" name="titlu" value="<?php echo htmlspecialchars($carte['titlu']); ?>" required>
				</div>

				<div class="form-group">
					<label>Autor</label>
					<input type="text" name="autor" value="<?php echo htmlspecialchars($carte['autor'] ?: ''); ?>">
				</div>

				<div class="form-group">
					<label>ISBN</label>
					<input type="text" name="isbn" value="<?php echo htmlspecialchars($carte['isbn'] ?: ''); ?>">
				</div>

				<!-- Sistem de localizare -->
				<div class="form-group">
					<label>Cota bibliotecară</label>
					<input type="text" name="cota" value="<?php echo htmlspecialchars($carte['cota'] ?: ''); ?>">
				</div>

				<div class="form-group">
					<label>Raft</label>
					<select name="raft">
						<option value="">Alege raft</option>
						<?php for($i = 'A'; $i <= 'Z'; $i++): ?>
							<option value="<?php echo $i; ?>" <?php echo $carte['raft'] == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
				</div>

				<div class="form-group">
					<label>Nivel</label>
					<select name="nivel">
						<option value="">Alege nivel</option>
						<?php for($i = 1; $i <= 10; $i++): ?>
							<option value="<?php echo $i; ?>" <?php echo $carte['nivel'] == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
				</div>

				<div class="form-group">
					<label>Poziție</label>
					<input type="text" name="pozitie" value="<?php echo htmlspecialchars($carte['pozitie']); ?>" maxlength="2">
				</div>

				<div class="form-group">
					<label>Secțiune</label>
					<select name="sectiune">
						<option value="">Alege secțiune</option>
						<option value="Literatură română" <?php echo $carte['sectiune'] == 'Literatură română' ? 'selected' : ''; ?>>Literatură română</option>
						<option value="Literatură universală" <?php echo $carte['sectiune'] == 'Literatură universală' ? 'selected' : ''; ?>>Literatură universală</option>
						<option value="Știință" <?php echo $carte['sectiune'] == 'Știință' ? 'selected' : ''; ?>>Știință</option>
						<option value="Istorie" <?php echo $carte['sectiune'] == 'Istorie' ? 'selected' : ''; ?>>Istorie</option>
						<option value="Filosofie" <?php echo $carte['sectiune'] == 'Filosofie' ? 'selected' : ''; ?>>Filosofie</option>
						<option value="Arte" <?php echo $carte['sectiune'] == 'Arte' ? 'selected' : ''; ?>>Arte</option>
						<option value="Drept" <?php echo $carte['sectiune'] == 'Drept' ? 'selected' : ''; ?>>Drept</option>
						<option value="Medicină" <?php echo $carte['sectiune'] == 'Medicină' ? 'selected' : ''; ?>>Medicină</option>
						<option value="Tehnică" <?php echo $carte['sectiune'] == 'Tehnică' ? 'selected' : ''; ?>>Tehnică</option>
						<option value="Alte" <?php echo $carte['sectiune'] == 'Alte' ? 'selected' : ''; ?>>Alte</option>
					</select>
				</div>

				<div class="form-group-full">
					<label>Observații locație</label>
					<textarea name="observatii_locatie"><?php echo htmlspecialchars($carte['observatii_locatie'] ?: ''); ?></textarea>
				</div>
			</div>

			<button type="submit">💾 Salvează modificările</button>
		</form>

		<div class="action-buttons">
			<a href="index.php" class="home-link">🏠 Acasă</a>
			<a href="carti.php" class="back-link">← Înapoi la lista cărți</a>
		</div>

		<!-- BUTOANE DE ȘTERGERE -->
		<form method="POST" style="margin-top: 20px;" onsubmit="return confirm('⚠️ Ești sigur că vrei să ștergi această carte?\n\nAceastă acțiune NU poate fi anulată!')">
			<input type="hidden" name="delete" value="true">
			<button type="submit" class="delete-btn">🗑️ Șterge cartea (doar dacă nu are istoric)</button>
		</form>

		<form method="POST" style="margin-top: 10px;" onsubmit="return confirm('🚨 ATENȚIE!\n\nVei șterge cartea ȘI TOATE împrumuturile ei din istoric!\n\nAceastă acțiune NU poate fi anulată!\n\nEști absolut sigur?')">
			<input type="hidden" name="delete" value="true">
			<input type="hidden" name="sterge_istoric" value="true">
			<button type="submit" class="delete-btn" style="background: linear-gradient(135deg, #ff0000 0%, #990000 100%);">
				💥 Șterge cartea CU TOT ISTORICUL
			</button>
		</form>


    </div>

    <script>
        // Actualizare previzualizare locație în timp real
        function updateLocationPreview() {
            const raft = document.querySelector('select[name="raft"]').value;
            const nivel = document.querySelector('select[name="nivel"]').value;
            const pozitie = document.querySelector('input[name="pozitie"]').value;

            if (raft && nivel && pozitie) {
                const locatie = `Raft ${raft} - Nivel ${nivel} - Poziția ${pozitie}`;
                let preview = document.querySelector('.location-preview');
                if (!preview) {
                    const container = document.querySelector('.form-group-full');
                    preview = document.createElement('div');
                    preview.className = 'location-preview';
                    container.appendChild(preview);
                }
                preview.textContent = `📍 Locație: ${locatie}`;
            }
        }

        // Adaugă event listeners pentru actualizare în timp real
        document.querySelector('select[name="raft"]').addEventListener('change', updateLocationPreview);
        document.querySelector('select[name="nivel"]').addEventListener('change', updateLocationPreview);
        document.querySelector('input[name="pozitie"]').addEventListener('input', updateLocationPreview);

        // Inițializează previzualizarea
        updateLocationPreview();

        // Resetare formular după succes
        <?php if (isset($mesaj) && $tip_mesaj === 'success'): ?>
            setTimeout(() => {
                // Reîncarcă previzualizarea cu datele noi
                updateLocationPreview();
            }, 500);
        <?php endif; ?>
    </script>
</body>
</html>

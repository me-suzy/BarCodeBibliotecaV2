<?php
// adauga_carte.php - Adaugă cărți noi în sistem
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cod_bare = trim($_POST['cod_bare']);
    $titlu = trim($_POST['titlu']);
    $autor = trim($_POST['autor']);
    $isbn = trim($_POST['isbn']);
    $cota = trim($_POST['cota']);
    $raft = trim($_POST['raft']);
    $nivel = trim($_POST['nivel']);
    $pozitie = trim($_POST['pozitie']);
    $sectiune = trim($_POST['sectiune']);
    $observatii_locatie = trim($_POST['observatii_locatie']);

    try {
        $stmt = $pdo->prepare("INSERT INTO carti (cod_bare, titlu, autor, isbn, cota, raft, nivel, pozitie, sectiune, observatii_locatie) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$cod_bare, $titlu, $autor, $isbn, $cota, $raft, $nivel, $pozitie, $sectiune, $observatii_locatie]);

        $mesaj = "✅ Cartea a fost adăugată cu succes!";
        $tip_mesaj = "success";
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
    <title>Adaugă Carte - Sistem Bibliotecă</title>
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

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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
        <h1>➕ Adaugă carte nouă</h1>

        <div class="info-box">
            <h3>💡 Informații utile</h3>
            <ul style="margin-left: 20px;">
                <li>Codul de bare trebuie să fie unic (ex: BOOK001, BOOK002)</li>
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
                    <input type="text" name="cod_bare" placeholder="BOOK001" required>
                </div>

                <div class="form-group">
                    <label>Titlu <span class="required">*</span></label>
                    <input type="text" name="titlu" placeholder="Enigma Otiliei" required>
                </div>

                <div class="form-group">
                    <label></label>
					<label>Autor <span class="required">*</span></label>
                    <input type="text" name="autor" placeholder="George Călinescu" required>
                </div>

                <div class="form-group">
                    <label>ISBN</label>
                    <input type="text" name="isbn" placeholder="9789734640560">
                </div>

                <!-- Sistem de localizare -->
                <div class="form-group">
                    <label>Cota bibliotecară</label>
                    <input type="text" name="cota" placeholder="821.135.1 CAL e">
                </div>

<div class="form-group">
    <label>Raft</label> 
    <select name="raft">
        <option value="">Alege raft</option>
        <?php for($i = 'A'; $i <= 'Z'; $i++): ?>
            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
        <?php endfor; ?>
    </select>
</div>

<div class="form-group">
    <label>Nivel</label>
    <select name="nivel">
        <option value="">Alege nivel</option> 
        <?php for($i = 1; $i <= 10; $i++): ?>
            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
        <?php endfor; ?>
    </select>
</div>

<div class="form-group">
    <label>Poziție</label>
    <input type="text" name="pozitie" placeholder="01" maxlength="2">
</div>

                <div class="form-group">
                    <label>Secțiune</label>
                    <select name="sectiune">
                        <option value="">Alege secțiune</option>
                        <option value="Literatură română">Literatură română</option>
                        <option value="Literatură universală">Literatură universală</option>
                        <option value="Știință">Știință</option>
                        <option value="Istorie">Istorie</option>
                        <option value="Filosofie">Filosofie</option>
                        <option value="Arte">Arte</option>
                        <option value="Drept">Drept</option>
                        <option value="Medicină">Medicină</option>
                        <option value="Tehnică">Tehnică</option>
                        <option value="Alte">Alte</option>
                    </select>
                </div>

                <div class="form-group-full">
                    <label>Observații locație</label>
                    <textarea name="observatii_locatie" placeholder="Ex: Carte rară, păstrați cu grijă sau Indicatoare suplimentare pentru localizare"></textarea>
                </div>
            </div>

            <button type="submit">Adaugă carte</button>
        </form>

        <a href="index.php" class="home-link">🏠 Acasă</a>
        <a href="index.php" class="back-link">← Înapoi la scanare</a>
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

        // Resetare formular după succes
        <?php if (isset($mesaj) && $tip_mesaj === 'success'): ?>
            setTimeout(() => {
                document.getElementById('carteForm').reset();
                // Șterge previzualizarea locației
                const preview = document.querySelector('.location-preview');
                if (preview) preview.remove();
            }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>

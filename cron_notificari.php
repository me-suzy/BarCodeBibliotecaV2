<?php
// cron_notificari.php - Script automat pentru trimitere notificări
// Rulează zilnic prin CRON sau Task Scheduler

require_once 'config.php';

// Configurare email (preia din config sau hardcoded)
$from_email = 'biblioteca@example.com';
$from_name = 'Biblioteca Municipală';

// Log start
echo "=== CRON Notificări START: " . date('Y-m-d H:i:s') . " ===\n";

// 1. REMINDER RETURNARE (12-13 zile de la împrumut)
echo "\n1. Procesare REMINDER-e...\n";

$stmt = $pdo->query("
    SELECT 
        i.id as imprumut_id,
        i.cod_cititor,
        i.cod_carte,
        i.data_imprumut,
        c.titlu,
        c.autor,
        c.locatie_completa,
        cit.nume,
        cit.prenume,
        cit.email,
        cit.telefon,
        DATEDIFF(NOW(), i.data_imprumut) as zile_imprumut
    FROM imprumuturi i
    JOIN carti c ON i.cod_carte = c.cod_bare
    JOIN cititori cit ON i.cod_cititor = cit.cod_bare
    WHERE i.status = 'activ' 
    AND DATEDIFF(NOW(), i.data_imprumut) BETWEEN 12 AND 13
    AND NOT EXISTS (
        SELECT 1 FROM notificari 
        WHERE cod_cititor = i.cod_cititor 
        AND tip_notificare = 'reminder'
        AND DATE(data_trimitere) = CURDATE()
    )
");

$reminder_count = 0;
while ($imp = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (!empty($imp['email'])) {
        $subiect = "⏰ Reminder: Returnarea cărții în 2 zile";
        $mesaj = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2 style='color: #667eea;'>Bună {$imp['prenume']},</h2>
            <p>Îți aducem aminte că cartea împrumutată trebuie returnată în <strong>2 zile</strong>:</p>
            <div style='background: #f8f9fa; padding: 15px; border-left: 4px solid #667eea; margin: 20px 0;'>
                <p><strong>📕 Carte:</strong> {$imp['titlu']}</p>
                <p><strong>✍️ Autor:</strong> {$imp['autor']}</p>
                <p><strong>📅 Data împrumut:</strong> " . date('d.m.Y', strtotime($imp['data_imprumut'])) . "</p>
                <p><strong>📍 Locație:</strong> {$imp['locatie_completa']}</p>
            </div>
            <p>Termenul recomandat de returnare este de <strong>14 zile</strong>.</p>
            <p>Te așteptăm la bibliotecă! 📚</p>
            <hr>
            <p style='font-size: 0.9em; color: #666;'>
                {$from_name}<br>
                Email: {$from_email}
            </p>
        </body>
        </html>
        ";
        
        $headers = "From: {$from_name} <{$from_email}>\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        if (mail($imp['email'], $subiect, $mesaj, $headers)) {
            // Salvează în log
            $pdo->prepare("INSERT INTO notificari (cod_cititor, tip_notificare, canal, destinatar, subiect, mesaj, status) VALUES (?, 'reminder', 'email', ?, ?, ?, 'trimis')")
                ->execute([$imp['cod_cititor'], $imp['email'], $subiect, strip_tags($mesaj)]);
            
            echo "  ✅ Reminder trimis: {$imp['nume']} {$imp['prenume']} - {$imp['email']}\n";
            $reminder_count++;
        } else {
            echo "  ❌ EROARE trimitere: {$imp['email']}\n";
        }
    }
}
echo "Total reminder-e trimise: $reminder_count\n";

// 2. ALERTE ÎNTÂRZIERE (14+ zile)
echo "\n2. Procesare ALERTE ÎNTÂRZIERE...\n";

$stmt = $pdo->query("
    SELECT 
        i.id as imprumut_id,
        i.cod_cititor,
        i.cod_carte,
        i.data_imprumut,
        c.titlu,
        c.autor,
        cit.nume,
        cit.prenume,
        cit.email,
        cit.telefon,
        DATEDIFF(NOW(), i.data_imprumut) as zile_intarziere
    FROM imprumuturi i
    JOIN carti c ON i.cod_carte = c.cod_bare
    JOIN cititori cit ON i.cod_cititor = cit.cod_bare
    WHERE i.status = 'activ' 
    AND DATEDIFF(NOW(), i.data_imprumut) > 14
    AND (
        NOT EXISTS (
            SELECT 1 FROM notificari 
            WHERE cod_cititor = i.cod_cititor 
            AND tip_notificare = 'intarziere'
            AND DATE(data_trimitere) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        )
    )
");

$intarziere_count = 0;
while ($imp = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (!empty($imp['email'])) {
        $subiect = "🚨 URGENT: Carte nereturnată - {$imp['zile_intarziere']} zile întârziere";
        $mesaj = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2 style='color: #dc3545;'>Bună {$imp['prenume']},</h2>
            <p><strong>ATENȚIE!</strong> Cartea împrumutată este nereturnată de <strong style='color: #dc3545;'>{$imp['zile_intarziere']} zile</strong>:</p>
            <div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0;'>
                <p><strong>📕 Carte:</strong> {$imp['titlu']}</p>
                <p><strong>✍️ Autor:</strong> {$imp['autor']}</p>
                <p><strong>📅 Data împrumut:</strong> " . date('d.m.Y', strtotime($imp['data_imprumut'])) . "</p>
                <p><strong>⏰ Zile întârziere:</strong> {$imp['zile_intarziere']} zile</p>
            </div>
            <p><strong>Te rugăm să returnezi cartea cât mai curând posibil!</strong></p>
            <p>Pentru informații suplimentare, contactează-ne.</p>
            <hr>
            <p style='font-size: 0.9em; color: #666;'>
                {$from_name}<br>
                Email: {$from_email}
            </p>
        </body>
        </html>
        ";
        
        $headers = "From: {$from_name} <{$from_email}>\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        if (mail($imp['email'], $subiect, $mesaj, $headers)) {
            $pdo->prepare("INSERT INTO notificari (cod_cititor, tip_notificare, canal, destinatar, subiect, mesaj, status) VALUES (?, 'intarziere', 'email', ?, ?, ?, 'trimis')")
                ->execute([$imp['cod_cititor'], $imp['email'], $subiect, strip_tags($mesaj)]);
            
            echo "  🚨 Alertă trimisă: {$imp['nume']} {$imp['prenume']} - {$imp['zile_intarziere']} zile\n";
            $intarziere_count++;
        } else {
            echo "  ❌ EROARE trimitere: {$imp['email']}\n";
        }
    }
}
echo "Total alerte trimise: $intarziere_count\n";

echo "\n=== CRON Notificări END: " . date('Y-m-d H:i:s') . " ===\n";
echo "TOTAL: $reminder_count reminder-e + $intarziere_count alerte = " . ($reminder_count + $intarziere_count) . " notificări\n";
?>
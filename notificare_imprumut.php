<?php
// notificare_imprumut.php - Funcție pentru trimitere email automat la împrumut

function trimite_email_imprumut($pdo, $cod_cititor, $cod_carte) {
    // Obține date cititor
    $stmt = $pdo->prepare("SELECT * FROM cititori WHERE cod_bare = ?");
    $stmt->execute([$cod_cititor]);
    $cititor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cititor || empty($cititor['email'])) {
        return false; // Nu are email
    }
    
    // Obține date carte
    $stmt = $pdo->prepare("SELECT * FROM carti WHERE cod_bare = ?");
    $stmt->execute([$cod_carte]);
    $carte = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$carte) {
        return false;
    }
    
    // Calculează data recomandată returnare (14 zile)
    $data_returnare = date('d.m.Y', strtotime('+14 days'));
    
    // Configurare email
    $from_email = 'biblioteca@example.com';
    $from_name = 'Biblioteca Municipală';
    
    // Construiește emailul
    $subiect = "📚 Confirmare Împrumut Carte - Biblioteca";
    $mesaj = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
        <div style='max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #667eea; text-align: center;'>✅ Împrumut Confirmat</h2>
            
            <p>Bună <strong>" . htmlspecialchars($cititor['prenume']) . "</strong>,</p>
            
            <p>Ai împrumutat cu succes următoarea carte din biblioteca noastră:</p>
            
            <div style='background: #f8f9fa; padding: 20px; border-left: 4px solid #667eea; margin: 20px 0;'>
                <h3 style='color: #667eea; margin-top: 0;'>📕 " . htmlspecialchars($carte['titlu']) . "</h3>
                <p><strong>✍️ Autor:</strong> " . htmlspecialchars($carte['autor'] ?? 'Necunoscut') . "</p>
                <p><strong>📅 Data împrumut:</strong> " . date('d.m.Y H:i') . "</p>
                <p><strong>📆 Data recomandată returnare:</strong> <span style='color: #dc3545; font-weight: bold;'>{$data_returnare}</span></p>
                " . (!empty($carte['locatie_completa']) ? "<p><strong>📍 Locație carte:</strong> " . htmlspecialchars($carte['locatie_completa']) . "</p>" : "") . "
            </div>
            
            <div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;'>
                <p style='margin: 0;'><strong>⏰ Reminder:</strong> Termenul recomandat de returnare este de <strong>14 zile</strong>. Vei primi o notificare automată cu 2 zile înainte de expirare.</p>
            </div>
            
            <p><strong>Lectură plăcută! 📖</strong></p>
            
            <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>
            
            <p style='font-size: 0.9em; color: #666;'>
                <strong>{$from_name}</strong><br>
                📧 Email: {$from_email}<br>
                📞 Telefon: 0231-123-456<br>
                🌐 Web: www.biblioteca-exemplu.ro
            </p>
            
            <p style='font-size: 0.8em; color: #999; margin-top: 20px;'>
                Acest email a fost generat automat de sistemul bibliotecii.<br>
                Cod împrumut: " . htmlspecialchars($cod_carte) . " pentru " . htmlspecialchars($cod_cititor) . "
            </p>
        </div>
    </body>
    </html>
    ";
    
    $headers = "From: {$from_name} <{$from_email}>\r\n";
    $headers .= "Reply-To: {$from_email}\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // Trimite emailul
    $email_success = @mail($cititor['email'], $subiect, $mesaj, $headers);
    
    if ($email_success) {
        // Încearcă să salveze în log notificări (dacă tabelul există)
        try {
            // Verifică dacă tabelul există
            $check = $pdo->query("SHOW TABLES LIKE 'notificari'")->fetch();
            
            if ($check) {
                $pdo->prepare("INSERT INTO notificari (cod_cititor, tip_notificare, canal, destinatar, subiect, mesaj, status) VALUES (?, 'imprumut', 'email', ?, ?, ?, 'trimis')")
                    ->execute([$cod_cititor, $cititor['email'], $subiect, strip_tags($mesaj)]);
            }
        } catch (PDOException $e) {
            // Ignoră eroarea de log - emailul a fost trimis oricum
        }
        
        return true;
    }
    
    return false;
}
?>
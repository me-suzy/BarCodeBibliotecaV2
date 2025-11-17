<?php
// aleph_api.php - API pentru interogare Aleph și extragere date cărți
header('Content-Type: application/json; charset=utf-8');

// Configurare Aleph
$ALEPH_SERVER = "84.146.121.46";
$ALEPH_PORT = "8991";
$ALEPH_BASE_URL = "http://{$ALEPH_SERVER}:{$ALEPH_PORT}/F";

// Primește cota din request
$cota = isset($_GET['cota']) ? trim($_GET['cota']) : '';

if (empty($cota)) {
    echo json_encode([
        'success' => false,
        'mesaj' => 'Introduceți o cotă bibliotecară pentru căutare'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // PASUL 1: Inițializează sesiune Aleph
    $init_url = "{$ALEPH_BASE_URL}?func=file&file_name=find-b";
    $session_response = @file_get_contents($init_url);
    
    if ($session_response === false) {
        throw new Exception("Nu se poate conecta la serverul Aleph");
    }
    
    // Extrage session ID din URL
    preg_match('/\/F\/([A-Z0-9\-]+)\?/', $session_response, $matches);
    $session_id = isset($matches[1]) ? $matches[1] : '';
    
    if (empty($session_id)) {
        throw new Exception("Nu s-a putut obține sesiune Aleph");
    }
    
    // PASUL 2: Căutare după COTĂ (folosim CZU - Clasificare Zecimală Universală)
    $search_url = "{$ALEPH_BASE_URL}/{$session_id}?func=find-b&request=" . urlencode($cota) . "&find_code=CZU&adjacent=N&local_base=RAI01";
    
    $search_response = @file_get_contents($search_url);
    
    if ($search_response === false) {
        throw new Exception("Eroare la căutare în Aleph");
    }
    
    // Verifică dacă sunt rezultate
    if (strpos($search_response, 'Niciun rezultat') !== false || 
        strpos($search_response, 'No results') !== false) {
        echo json_encode([
            'success' => false,
            'mesaj' => "Nicio carte găsită cu cota: {$cota}"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // PASUL 3: Extrage link către prima carte
    preg_match('/<a href="([^"]+)"[^>]*>1\.<\/a>/', $search_response, $link_matches);
    
    if (empty($link_matches[1])) {
        throw new Exception("Nu s-a putut extrage detaliile cărții");
    }
    
    $detail_url = "http://{$ALEPH_SERVER}:{$ALEPH_PORT}" . $link_matches[1];
    $detail_response = @file_get_contents($detail_url);
    
    if ($detail_response === false) {
        throw new Exception("Nu se pot încărca detaliile cărții");
    }
    
    // PASUL 4: Parse HTML pentru extragere date
    $date_carte = parseAlephHTML($detail_response);
    
    if (empty($date_carte['titlu'])) {
        throw new Exception("Nu s-au putut extrage datele cărții");
    }
    
    // Returnează date în format JSON
    echo json_encode([
        'success' => true,
        'data' => $date_carte
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'mesaj' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

// Funcție pentru parsing HTML din Aleph
function parseAlephHTML($html) {
    $data = [
        'titlu' => '',
        'autor' => '',
        'isbn' => '',
        'editura' => '',
        'an' => '',
        'locatie' => '',
        'sectiune' => ''
    ];
    
    // Curăță HTML
    $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
    
    // Parse cu DOMDocument
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    
    // Găsește toate td-urile cu date
    $tds = $dom->getElementsByTagName('td');
    
    foreach ($tds as $index => $td) {
        $text = trim($td->textContent);
        
        // TITLU (căutare după "Titlu")
        if (strpos($text, 'Titlu') !== false || strpos($text, 'Titl') !== false) {
            $next_td = $tds->item($index + 1);
            if ($next_td) {
                $data['titlu'] = cleanText($next_td->textContent);
            }
        }
        
        // AUTOR (căutare după "Autor")
        if (strpos($text, 'Autor') !== false) {
            $next_td = $tds->item($index + 1);
            if ($next_td) {
                $autor_text = cleanText($next_td->textContent);
                // Extrage doar numele (fără link-uri)
                $data['autor'] = preg_replace('/\s+/', ' ', $autor_text);
            }
        }
        
        // ISBN (căutare după "ISBN")
        if (strpos($text, 'ISBN') !== false) {
            $next_td = $tds->item($index + 1);
            if ($next_td) {
                $isbn_text = cleanText($next_td->textContent);
                // Extrage doar cifrele ISBN
                preg_match('/[\d\-]+/', $isbn_text, $isbn_match);
                $data['isbn'] = isset($isbn_match[0]) ? $isbn_match[0] : '';
            }
        }
        
        // EDITURA (căutare după "Editură" sau "Editura")
        if (strpos($text, 'Edit') !== false) {
            $next_td = $tds->item($index + 1);
            if ($next_td) {
                $data['editura'] = cleanText($next_td->textContent);
            }
        }
        
        // AN (căutare după "An")
        if ($text === 'An' || strpos($text, 'An') !== false) {
            $next_td = $tds->item($index + 1);
            if ($next_td) {
                $an_text = cleanText($next_td->textContent);
                preg_match('/\d{4}/', $an_text, $an_match);
                $data['an'] = isset($an_match[0]) ? $an_match[0] : '';
            }
        }
        
        // LOCAȚIE/COTĂ (căutare după "COTĂ" sau "Localizare")
        if (strpos($text, 'COTĂ') !== false || strpos($text, 'Localizare') !== false) {
            $next_td = $tds->item($index + 1);
            if ($next_td) {
                $data['locatie'] = cleanText($next_td->textContent);
            }
        }
        
        // SUBIECT TEMATIC (pentru deducere secțiune)
        if (strpos($text, 'Subiect tematic') !== false) {
            $next_td = $tds->item($index + 1);
            if ($next_td) {
                $subiect = cleanText($next_td->textContent);
                $data['sectiune'] = deduceSecțiune($subiect);
            }
        }
    }
    
    return $data;
}

// Curăță text de caractere speciale HTML
function cleanText($text) {
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = strip_tags($text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}

// Deduce secțiunea pe baza subiectului tematic
function deduceSecțiune($subiect) {
    $subiect_lower = mb_strtolower($subiect);
    
    if (strpos($subiect_lower, 'construcţii') !== false || strpos($subiect_lower, 'structur') !== false) {
        return 'Tehnică';
    }
    if (strpos($subiect_lower, 'literatură') !== false || strpos($subiect_lower, 'roman') !== false) {
        return 'Literatură română';
    }
    if (strpos($subiect_lower, 'fizică') !== false || strpos($subiect_lower, 'chimie') !== false) {
        return 'Ştiință';
    }
    if (strpos($subiect_lower, 'istorie') !== false) {
        return 'Istorie';
    }
    if (strpos($subiect_lower, 'medicin') !== false || strpos($subiect_lower, 'sănătate') !== false) {
        return 'Medicină';
    }
    if (strpos($subiect_lower, 'drept') !== false || strpos($subiect_lower, 'juridic') !== false) {
        return 'Drept';
    }
    
    return 'Alte';
}
?>
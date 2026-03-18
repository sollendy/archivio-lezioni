<?php
header('Content-Type: application/json; charset=utf-8');

$presenze_file     = __DIR__ . '/../data/presenze.json';
$lezioni_file      = __DIR__ . '/../data/lezioni.json';
$partecipanti_file = __DIR__ . '/../data/partecipanti.json';

$presenze     = json_decode(file_get_contents($presenze_file), true);
$lezioni      = json_decode(file_get_contents($lezioni_file), true);
$partecipanti = json_decode(file_get_contents($partecipanti_file), true);

// GET: restituisce tutte le presenze registrate
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode($presenze);
    exit;
}

// POST: registra una presenza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['lezione_id'], $input['partecipante_id'], $input['presente'])) {
        http_response_code(400);
        echo json_encode(['errore' => 'Campi obbligatori mancanti: lezione_id, partecipante_id, presente']);
        exit;
    }

    $lezione_id      = (int) $input['lezione_id'];
    $partecipante_id = (int) $input['partecipante_id'];
    $presente        = (bool) $input['presente'];

    // Verifica che la lezione esista
    $lezione_ids = array_column($lezioni, 'id');
    if (!in_array($lezione_id, $lezione_ids)) {
        http_response_code(404);
        echo json_encode(['errore' => "Lezione con id {$lezione_id} non trovata"]);
        exit;
    }

    // Verifica che il partecipante esista
    $partecipante_ids = array_column($partecipanti, 'id');
    if (!in_array($partecipante_id, $partecipante_ids)) {
        http_response_code(404);
        echo json_encode(['errore' => "Partecipante con id {$partecipante_id} non trovato"]);
        exit;
    }

    // Verifica duplicato: stessa lezione + stesso partecipante
    foreach ($presenze as $p) {
        if ($p['lezione_id'] === $lezione_id && $p['partecipante_id'] === $partecipante_id) {
            http_response_code(409);
            echo json_encode(['errore' => 'Presenza già registrata per questo partecipante in questa lezione']);
            exit;
        }
    }

    $nuova = [
        'lezione_id'      => $lezione_id,
        'partecipante_id' => $partecipante_id,
        'presente'        => $presente,
    ];

    $presenze[] = $nuova;
    file_put_contents($presenze_file, json_encode($presenze, JSON_PRETTY_PRINT));

    http_response_code(201);
    echo json_encode(['messaggio' => 'Presenza registrata con successo', 'presenza' => $nuova]);
    exit;
}

http_response_code(405);
echo json_encode(['errore' => 'Metodo non supportato']);

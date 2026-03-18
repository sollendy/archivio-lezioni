<?php
header('Content-Type: application/json; charset=utf-8');

$lezioni  = json_decode(file_get_contents(__DIR__ . '/../data/lezioni.json'), true);
$presenze = json_decode(file_get_contents(__DIR__ . '/../data/presenze.json'), true);

// Conta le presenti (presente === true) per ogni lezione
$contatori = [];
foreach ($presenze as $p) {
    if ($p['presente'] === true) {
        $contatori[$p['lezione_id']] = ($contatori[$p['lezione_id']] ?? 0) + 1;
    }
}

$risultato = array_map(function ($lezione) use ($contatori) {
    $lezione['num_partecipanti'] = $contatori[$lezione['id']] ?? 0;
    return $lezione;
}, $lezioni);

echo json_encode($risultato);

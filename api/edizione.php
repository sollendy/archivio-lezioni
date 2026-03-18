<?php
header('Content-Type: application/json; charset=utf-8');

$edizione    = json_decode(file_get_contents(__DIR__ . '/../data/edizione.json'), true);
$lezioni     = json_decode(file_get_contents(__DIR__ . '/../data/lezioni.json'), true);

$ore_svolte = array_sum(array_column($lezioni, 'ore'));

echo json_encode([
    'titolo'          => $edizione['titolo'],
    'budget_totale'   => $edizione['budget_totale'],
    'ore_svolte'      => $ore_svolte,
    'budget_residuo'  => $edizione['budget_totale'] - $ore_svolte,
]);
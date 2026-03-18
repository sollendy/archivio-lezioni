<?php
header('Content-Type: application/json; charset=utf-8');

$partecipanti = json_decode(file_get_contents(__DIR__ . '/../data/partecipanti.json'), true);

echo json_encode($partecipanti);

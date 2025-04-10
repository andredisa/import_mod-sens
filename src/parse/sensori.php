<?php
require_once '../gestione/funzioni.php';

function parseSensors($csvFile) {
    $rows = [];

    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        fgetcsv($handle); // Salta intestazione

        while (($data = fgetcsv($handle)) !== FALSE) {
            $linea = trim($data[0]);
            $sensore = trim($data[1]);
            $typeId = strtoupper(trim($data[2]));
            $descrizioneRaw = trim($data[3]);

            $id = "L" . $linea . "S" . str_pad($sensore, 2, "0", STR_PAD_LEFT);

            $key = (int)$linea * 1000 + (int)$sensore; // ordinamento per linea e sensore

            // Se già presente una riga valida, NON sovrascrivere con una NONE
            if (isset($rows[$key])) {
                if ($typeId !== 'NONE') {
                    $rows[$key] = [
                        'id' => $id,
                        'descrizione' => cleanString($descrizioneRaw),
                        'preferibile' => true
                    ];
                }
            } else {
                $rows[$key] = [
                    'id' => $id,
                    'descrizione' => $typeId === 'NONE' ? '' : cleanString($descrizioneRaw),
                    'preferibile' => $typeId !== 'NONE'
                ];
            }
        }

        fclose($handle);
    }

    // Ordina per chiave numerica (linea*1000 + sensore)
    ksort($rows);

    // Rimuovi il flag interno e restituisci array [id => descrizione]
    $sensors = [];
    foreach ($rows as $row) {
        $sensors[$row['id']] = $row['descrizione'];
    }

    return $sensors;
}

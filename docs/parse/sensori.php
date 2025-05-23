<?php

require_once '../gestione/funzioni.php';
function parseSensors($csvFile)
{
    $rows = [];

    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        fgetcsv($handle); // Salta intestazione

        while (($data = fgetcsv($handle)) !== FALSE) {
            $linea = trim($data[0]);
            $sensoreRaw = trim($data[1]);
            $typeId = strtoupper(trim($data[2]));
            $descrizioneRaw = trim($data[3]);

            // Gestione sensori decimali (es. 1.1, 2.3)
            $sensoreStr = str_pad(floor($sensoreRaw), 2, "0", STR_PAD_LEFT);
            $sensore = (float) $sensoreRaw;
            $sensoreId = strpos($sensoreRaw, '.') !== false ? "{$sensoreStr}." . explode('.', $sensoreRaw)[1] : $sensoreStr;

            $id = "L" . $linea . "S" . $sensoreId;

            // Chiave di ordinamento precisa, considera i decimali
            $key = (int) $linea * 10000 + (float) ($sensore * 10); // Moltiplico per 10 per gestire fino a .9

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

    // Ordina per chiave numerica (linea*10000 + sensore*10)
    ksort($rows);
    // Rimuovi il flag interno e restituisci array [id => descrizione]
    $sensors = [];
    foreach ($rows as $row) {
        $sensors[$row['id']] = $row['descrizione'];
    }
    return $sensors;
}

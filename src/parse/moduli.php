<?php
require_once '../gestione/funzioni.php';

function parseModules($csvFile)
{
    $rows = [];

    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        fgetcsv($handle); // Salta intestazione

        while (($data = fgetcsv($handle)) !== FALSE) {
            $linea = trim($data[0]);
            $moduloRaw = trim($data[1]);
            $typeId = strtoupper(trim($data[2]));
            $descrizioneRaw = trim($data[3]);

            // Gestione moduli decimali (es. 1.1, 2.3)
            $moduloStr = str_pad(floor($moduloRaw), 2, "0", STR_PAD_LEFT);
            $modulo = (float) $moduloRaw;
            $moduloId = strpos($moduloRaw, '.') !== false ? "{$moduloStr}." . explode('.', $moduloRaw)[1] : $moduloStr;

            $id = "L" . $linea . "M" . $moduloId;

            // Chiave di ordinamento precisa, considera i decimali
            $key = (int) $linea * 10000 + (float) ($modulo * 10); // Moltiplico per 10 per gestire max .9

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

    ksort($rows); // Ordina in base alla chiave
    $modules = [];
    foreach ($rows as $row) {
        $modules[$row['id']] = $row['descrizione'];
    }

    return $modules;

}

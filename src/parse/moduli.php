<?php
require_once '../gestione/funzioni.php';

function parseModules($csvFile) {
    $rows = [];

    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        fgetcsv($handle); // Salta intestazione

        while (($data = fgetcsv($handle)) !== FALSE) {
            $linea = trim($data[0]);
            $modulo = trim($data[1]);
            $typeId = strtoupper(trim($data[2]));
            $descrizioneRaw = trim($data[3]);

            $id = "L" . $linea . "M" . str_pad($modulo, 2, "0", STR_PAD_LEFT);

            $key = (int)$linea * 1000 + (int)$modulo; // ordinamento per linea e modulo

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

    ksort($rows);

    $modules = [];
    foreach ($rows as $row) {
        $modules[$row['id']] = $row['descrizione'];
    }

    return $modules;
}

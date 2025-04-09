<?php
require_once '../gestione/funzioni.php';

function parseSensors($csvFile) {
    $sensors = [];
    // Leggere il file CSV
    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        // Saltare la prima riga (intestazione)
        fgetcsv($handle);
        
        // Leggere ogni riga
        while (($data = fgetcsv($handle)) !== FALSE) {
            // Creazione dell'identificatore del sensore
            $linea = trim($data[0]);
            $sensore = trim($data[1]);
            $descrizione = cleanString(trim($data[3]));  // Pulire la descrizione
            
            // Se la descrizione non è vuota, aggiungere l'elemento all'array
            if (!empty($descrizione)) {
                // Generazione dell'identificativo senza spazi tra "L" e il resto
                $sensorID = "L" . $linea . "S" . str_pad($sensore, 2, "0", STR_PAD_LEFT);
                
                // Aggiungere al risultato
                $sensors[$sensorID] = $descrizione;
            }
        }
        fclose($handle);
    }
    // Ordinare i sensori prima per linea, poi per sensore
    ksort($sensors);
    return $sensors;
}
?>

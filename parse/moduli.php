<?php
require_once '../gestione/funzioni.php';

function parseModules($csvFile) {
    $modules = [];
    // Leggere il file CSV
    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        // Saltare la prima riga (intestazione)
        fgetcsv($handle);
        // Leggere ogni riga
        while (($data = fgetcsv($handle)) !== FALSE) {
            // Creazione dell'identificatore del modulo
            $linea = trim($data[0]);
            $modulo = trim($data[1]);
            $descrizione = cleanString(trim($data[3]));  // Pulire la descrizione
            
            // Se la descrizione non è vuota, aggiungere l'elemento all'array
            if (!empty($descrizione)) {
                // Generazione dell'identificativo senza spazi tra "L" e il resto
                $moduleID = "L" . $linea . "M" . str_pad($modulo, 2, "0", STR_PAD_LEFT);
                
                // Aggiungere al risultato
                $modules[$moduleID] = $descrizione;
            }
        }
        fclose($handle);
    }
    // Ordinare i moduli prima per linea, poi per modulo
    ksort($modules);
    return $modules;
}
?>

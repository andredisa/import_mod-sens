<?php

require '../vendor/autoload.php'; // Includi l'autoload di Composer
require_once '../parse/sensori.php';
require_once '../parse/moduli.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// Verifica se i file sono stati caricati correttamente
if (
    isset($_FILES['sensoriFile']) && $_FILES['sensoriFile']['error'] == 0 &&
    isset($_FILES['moduliFile']) && $_FILES['moduliFile']['error'] == 0 &&
    isset($_FILES['fileExcel']) && $_FILES['fileExcel']['error'] == 0
) {

    // Ottieni i file CSV caricati
    $sensoriFile = $_FILES['sensoriFile']['tmp_name'];
    $moduliFile = $_FILES['moduliFile']['tmp_name'];
    $excelFile = $_FILES['fileExcel']['tmp_name'];

    // Carica i dati dai file CSV in array
    $sensori = parseSensors($sensoriFile);
    $moduli = parseModules($moduliFile);

    // Carica il file Excel da modificare
    $spreadsheet = IOFactory::load($excelFile); // Carica il file Excel caricato

    // Seleziona il foglio "ALL-6"
    $sheet = $spreadsheet->getSheetByName('ALL-6');
    if ($sheet === null) {
        echo "Il foglio 'ALL-6' non esiste!";
        exit;
    }

    // Pulizia delle righe dalla 4 in poi
    $highestRow = $sheet->getHighestRow();
    for ($row = 4; $row <= $highestRow; $row++) {
        $sheet->removeRow($row);
    }

    // Aggiungi l'intestazione alla riga 4
    $sheet->setCellValue('A4', 'Id')
        ->setCellValue('B4', 'Tipo');

    // Funzione per stampare i sensori e moduli
    function printData($sheet, $data, $startRow) {
        $rowIndex = $startRow;
        
        foreach ($data as $sensorID => $descrizione) {
            // Se $descrizione è un array, convertilo in una stringa
            if (is_array($descrizione)) {
                $descrizione = implode(', ', $descrizione);  // Unisci gli elementi dell'array in una stringa
            }
            
            // Stampa i dati nella riga corrente
            $sheet->setCellValue('A' . $rowIndex, $sensorID)  // Usa direttamente l'ID del sensore
                  ->setCellValue('B' . $rowIndex, $descrizione);  // Usa direttamente la descrizione (che è ora una stringa)
            $rowIndex++; // Passa alla riga successiva
        }
    }

    // Stampa i sensori nell'Excel
    printData($sheet, $sensori, 5);  // Sensori

    // Stampa i moduli nell'Excel
    printData($sheet, $moduli, $sheet->getHighestRow() + 1);  // Moduli

    // Salva il file Excel modificato
    $outputFileName = 'file_modificato_' . time() . '.xlsx';
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($outputFileName);

    // Fornisci il file modificato per il download
    echo "File caricato e modificato con successo! <br>";
    echo "Puoi <a href='$outputFileName' download>scaricare il file modificato</a>";

} else {
    echo "Errore nel caricamento dei file!";
}
?>

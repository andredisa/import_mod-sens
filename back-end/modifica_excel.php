<?php
require 'vendor/autoload.php'; // Includi l'autoload di Composer

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// Verifica se è stato caricato un file
if (isset($_FILES['fileExcel']) && $_FILES['fileExcel']['error'] == 0) {

    // Ottieni il percorso temporaneo del file caricato
    $uploadedFile = $_FILES['fileExcel']['tmp_name'];
    
    // Carica il file Excel
    $spreadsheet = IOFactory::load($uploadedFile);

    // Seleziona il foglio "ALL-6"
    $sheet = $spreadsheet->getSheetByName('ALL-6');
    
    if ($sheet === null) {
        echo "Il foglio 'ALL-6' non esiste!";
        exit;
    }

    // Modifica una cella a caso nella pagina ALL-6
    // Esempio: Modifica la cella A1 con un nuovo valore
    $sheet->setCellValue('A1', 'Modifica eseguita ' . date('Y-m-d H:i:s'));

    // Salva il file modificato
    $outputFileName = 'file_modificato_' . time() . '.xlsx';
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($outputFileName);

    // Fornisci il file modificato per il download
    echo "File caricato e modificato con successo! <br>";
    echo "Puoi <a href='$outputFileName' download>scaricare il file modificato</a>";
} else {
    echo "Errore nel caricamento del file!";
}
?>

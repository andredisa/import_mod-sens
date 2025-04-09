<?php
require '../vendor/autoload.php';
require_once '../parse/sensori.php';
require_once '../parse/moduli.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

if (
    isset($_FILES['sensoriFile']) && $_FILES['sensoriFile']['error'] == 0 &&
    isset($_FILES['moduliFile']) && $_FILES['moduliFile']['error'] == 0 &&
    isset($_FILES['fileExcel']) && $_FILES['fileExcel']['error'] == 0
) {

    $sensoriFile = $_FILES['sensoriFile']['tmp_name'];
    $moduliFile = $_FILES['moduliFile']['tmp_name'];
    $excelFile = $_FILES['fileExcel']['tmp_name'];

    $sensori = parseSensors($sensoriFile);
    $moduli = parseModules($moduliFile);

    $spreadsheet = IOFactory::load($excelFile);

    $sheet = $spreadsheet->getSheetByName('ALL-6');
    if ($sheet === null) {
        echo "Il foglio 'ALL-6' non esiste!";
        exit;
    }

    // Sciogli tutte le celle unite dalla riga 4 in giù
    $mergedCells = $sheet->getMergeCells();
    foreach ($mergedCells as $range) {
        preg_match('/[A-Z]+(\d+):[A-Z]+(\d+)/', $range, $matches);
        $startRow = (int) $matches[1];

        if ($startRow >= 4) {
            $sheet->unmergeCells($range);
        }
    }

    // Pulisce tutte le righe da 4 in poi in un colpo solo
    $highestRow = $sheet->getHighestRow();
    if ($highestRow >= 4) {
        $sheet->removeRow(4, $highestRow - 3);
    }

    // Unisci i sensori e i moduli in un unico array
    $data = array_merge($sensori, $moduli);

    // Stili per i bordi
    $borderThin = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,  // Tipo di bordo (linea sottile)
                'color' => ['argb' => '000000'],  // Colore del bordo (nero)
            ],
        ],
    ];

    $borderThick = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_MEDIUM,  // Tipo di bordo (bordo medio)
                'color' => ['argb' => '000000'],  // Colore del bordo (nero)
            ],
        ],
    ];

    // Funzione per stampare i dati con bordi
    function printData($sheet, $data, $startRow, $borderThin, $borderThick)
    {
        $rowIndex = $startRow;
        $righePerBlocco = 65;  // Righe per blocco (56 totali - 1 riga per firma)
        $total = count($data);
        $i = 0;
        $signaturePrinted = false;  // Flag per gestire la stampa della firma una sola volta

        // Stampa l'intestazione iniziale alla riga 4 (con bordi spessi)
        $sheet->setCellValue('A' . $rowIndex, 'Id')
            ->setCellValue('B' . $rowIndex, 'Tipo')
            ->setCellValue('C' . $rowIndex, 'Anzianità')
            ->setCellValue('D' . $rowIndex, 'Data Visita 1')
            ->setCellValue('E' . $rowIndex, 'Esito')
            ->setCellValue('F' . $rowIndex, 'Tecnico')
            ->setCellValue('G' . $rowIndex, 'Data Visita 2')
            ->setCellValue('H' . $rowIndex, 'Esito')
            ->setCellValue('I' . $rowIndex, 'Tecnico');
        // Adattare la larghezza delle colonne automaticamente
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);

        // Applica bordi spessi all'intestazione
        $sheet->getStyle('A' . $rowIndex . ':I' . $rowIndex)->applyFromArray($borderThick);
        $rowIndex++;  // Vai alla riga successiva dopo l'intestazione

        // Stampa i dati
        while ($i < $total) {
            // Stampa fino a 51 righe di dati
            $righeStampate = 0;
            while ($i < $total && $righeStampate < $righePerBlocco) {
                $id = key($data);  // Ottieni la chiave (id) dell'array
                $descrizione = current($data);  // Ottieni il valore della descrizione

                // Stampa i dati nelle celle
                $sheet->setCellValue('A' . $rowIndex, $id);
                $sheet->setCellValue('B' . $rowIndex, $descrizione);

                // Applica i bordi sottili a tutta la riga da A a I
                $sheet->getStyle('A' . $rowIndex . ':I' . $rowIndex)->applyFromArray($borderThin);
                $rowIndex++;

                // Avanza al prossimo elemento nell'array
                next($data);
                $i++;
                $righeStampate++;
            }

            // Riga per la firma dopo ogni 51 righe stampate (bordo spesso)
            if ($righeStampate == $righePerBlocco) {
                $sheet->mergeCells("A$rowIndex:C$rowIndex");
                $sheet->mergeCells("D$rowIndex:F$rowIndex");
                $sheet->mergeCells("G$rowIndex:I$rowIndex");

                $sheet->setCellValue("A$rowIndex", 'FIRMA  RESP. CLIENTE');
                $sheet->setCellValue("D$rowIndex", 'Visita 1');
                $sheet->setCellValue("G$rowIndex", 'Visita 2');

                // Applica i bordi spessi alla riga della firma
                $sheet->getStyle("A$rowIndex:I$rowIndex")->applyFromArray($borderThick);
                $rowIndex++;

                $signaturePrinted = true;  // La firma è stata stampata
            }

            // Ristampa l'intestazione dopo ogni firma (bordo spesso)
            if ($i < $total) {
                $sheet->setCellValue('A' . $rowIndex, 'Id')
                    ->setCellValue('B' . $rowIndex, 'Tipo')
                    ->setCellValue('C' . $rowIndex, 'Anzianità')
                    ->setCellValue('D' . $rowIndex, 'Data Visita 1')
                    ->setCellValue('E' . $rowIndex, 'Esito')
                    ->setCellValue('F' . $rowIndex, 'Tecnico')
                    ->setCellValue('G' . $rowIndex, 'Data Visita 2')
                    ->setCellValue('H' . $rowIndex, 'Esito')
                    ->setCellValue('I' . $rowIndex, 'Tecnico');

                // Applica i bordi spessi all'intestazione dopo la firma
                $sheet->getStyle('A' . $rowIndex . ':I' . $rowIndex)->applyFromArray($borderThick);
                $rowIndex++;  // Vai alla riga successiva dopo l'intestazione
            }
        }

        // Se siamo alla fine dei dati e la firma non è stata ancora stampata, aggiungila (bordo spesso)
        if ($i >= $total && !$signaturePrinted) {
            $sheet->mergeCells("A$rowIndex:C$rowIndex");
            $sheet->mergeCells("D$rowIndex:F$rowIndex");
            $sheet->mergeCells("G$rowIndex:I$rowIndex");

            $sheet->setCellValue("A$rowIndex", 'FIRMA  RESP. CLIENTE');
            $sheet->setCellValue("D$rowIndex", 'Visita 1');
            $sheet->setCellValue("G$rowIndex", 'Visita 2');

            // Applica i bordi spessi alla riga della firma
            $sheet->getStyle("A$rowIndex:I$rowIndex")->applyFromArray($borderThick);
            $rowIndex++;
        }

        return $rowIndex;
    }

    // Chiama la funzione passando i dati uniti
    $nextRow = printData($sheet, $data, 4, $borderThin, $borderThick);  // 4 è la riga iniziale (intestazione)

    // Salvataggio del file modificato
    $outputFileName = 'file_modificato_' . time() . '.xlsx';
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($outputFileName);

    echo "File caricato e modificato con successo! <br>";
    echo "Puoi <a href='$outputFileName' download>scaricare il file modificato</a>";
} else {
    echo "Errore nel caricamento dei file!";
}
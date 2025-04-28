<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Border;  // <<< Import Border per usare stile

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileExcel']) && isset($_FILES['ExcelFile'])) {
    $fileTmpPath = $_FILES['fileExcel']['tmp_name'];
    $fileName = $_FILES['fileExcel']['name'];

    $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
    if (!in_array($fileType, ['xls', 'xlsx'])) {
        die("Errore: il file da convertire deve essere un Excel (.xls, .xlsx)");
    }

    $fileUpdatePath = $_FILES['ExcelFile']['tmp_name'];
    $fileUpdateName = $_FILES['ExcelFile']['name'];
    $fileUpdateType = pathinfo($fileUpdateName, PATHINFO_EXTENSION);

    if (!in_array($fileUpdateType, ['xls', 'xlsx'])) {
        die("Errore: il file da aggiornare deve essere un Excel (.xls, .xlsx)");
    }

    try {
        // ===== 1. Carica il primo file ed estrae i dati =====
        $spreadsheetConvert = IOFactory::load($fileTmpPath);
        $resultArray = [];

        foreach ($spreadsheetConvert->getAllSheets() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rowData = [];
                foreach ($row->getCellIterator() as $cell) {
                    $rowData[] = $cell->getCalculatedValue();
                }

                if (isset($rowData[2])) {
                    $secondValue = $rowData[1] ?? null;
                    $thirdValue = $rowData[2];

                    if (preg_match('/^L\d+[SM]\d+(?:\.\d+)?/i', $thirdValue)) {
                        $id = (is_numeric($secondValue)) ? $secondValue : '';
                        $resultArray[] = [
                            'id' => $id,
                            'description' => $thirdValue
                        ];
                    }
                }
            }
        }

        // ===== 2. Ordina i dati =====
        function estraiChiaviOrdine($description)
        {
            if (preg_match('/L(\d+)([SM])(\d+)(?:\.(\d+))?/i', $description, $matches)) {
                $linea = (int) $matches[1];
                $tipo = $matches[2];
                $numero = (int) $matches[3];
                $sottosezione = isset($matches[4]) ? (int) $matches[4] : 0;
                return [$linea, $numero, $sottosezione, $tipo];
            }
            return [PHP_INT_MAX, PHP_INT_MAX, PHP_INT_MAX, 'Z'];
        }

        $sensori = array_filter($resultArray, fn($item) => preg_match('/L\d+S\d+/i', $item['description']));
        $moduli = array_filter($resultArray, fn($item) => preg_match('/L\d+M\d+/i', $item['description']));

        usort($sensori, fn($a, $b) => estraiChiaviOrdine($a['description']) <=> estraiChiaviOrdine($b['description']));
        usort($moduli, fn($a, $b) => estraiChiaviOrdine($a['description']) <=> estraiChiaviOrdine($b['description']));

        $arrayOrdinatoFinale = array_merge($sensori, $moduli);

        // ===== 3. Carica il file da aggiornare =====
        $spreadsheetUpdate = IOFactory::load($fileUpdatePath);
        $sheet = $spreadsheetUpdate->getSheetByName('ALL-6');
        if ($sheet === null) {
            die("Errore: Il foglio 'ALL-6' non esiste!");
        }

        // Sciogli celle unite dalla riga 4 in giù
        foreach ($sheet->getMergeCells() as $range) {
            if (preg_match('/[A-Z]+(\d+):[A-Z]+(\d+)/', $range, $matches)) {
                if ((int) $matches[1] >= 4) {
                    $sheet->unmergeCells($range);
                }
            }
        }

        // Pulisce righe da 4 in giù
        $highestRow = $sheet->getHighestRow();
        if ($highestRow >= 4) {
            $sheet->removeRow(4, $highestRow - 3);
        }

        // ==== Stili ====
        $borderThin = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']]]];
        $borderThick = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => '000000']]]];
        $smallFontStyle = ['font' => ['size' => 8]];

        function printData($sheet, $arrayOrdinatoFinale, $startRow, $borderThin, $borderThick)
        {
            $rowIndex = $startRow;
            $righePerBlocco = 81;  // Righe per blocco (56 totali - 1 riga per firma)
            $total = count($arrayOrdinatoFinale);
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
                    $entry = $arrayOrdinatoFinale[$i];
                    $id = $entry['id'];
                    $descrizione = $entry['description'];

                    $sheet->setCellValue('A' . $rowIndex, $entry['id']);
                    $sheet->setCellValue('B' . $rowIndex, $entry['description']);


                    // Applica i bordi sottili a tutta la riga da A a I
                    $sheet->getStyle('A' . $rowIndex . ':I' . $rowIndex)->applyFromArray($borderThin);
                    $rowIndex++;

                    // Avanza al prossimo elemento nell'array
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
                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('B')->setAutoSize(true);
                    $sheet->getColumnDimension('C')->setAutoSize(true);
                    $sheet->getColumnDimension('D')->setAutoSize(true);
                    $sheet->getColumnDimension('E')->setAutoSize(true);
                    $sheet->getColumnDimension('F')->setAutoSize(true);
                    $sheet->getColumnDimension('G')->setAutoSize(true);
                    $sheet->getColumnDimension('H')->setAutoSize(true);
                    $sheet->getColumnDimension('I')->setAutoSize(true);

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

        printData($sheet, $arrayOrdinatoFinale, 4, $borderThin, $borderThick, $smallFontStyle);

        // ===== Salvataggio =====
        $outputFileName = 'file_modificato_' . time() . '.xlsx';
        $writer = IOFactory::createWriter($spreadsheetUpdate, 'Xlsx');
        $writer->save($outputFileName);

        // ===== Output HTML success =====
        echo "
        <!DOCTYPE html>
        <html lang='it'>
        <head>
            <meta charset='UTF-8'>
            <title>File Caricato e Modificato</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css' rel='stylesheet'>
        </head>
        <body>
        <div class='container mt-5'>
            <div class='alert alert-success'>
                <strong>Successo!</strong> Il file è stato modificato correttamente.
            </div>
            <div class='text-center'>
                <a href='$outputFileName' download class='btn btn-success'>
                    <i class='bi bi-download'></i> Scarica il file modificato
                </a>
            </div>
        </div>
        </body>
        </html>
        ";
    } catch (Exception $e) {
        die("Errore durante la conversione: " . $e->getMessage());
    }
} else {
    echo "<p class='alert alert-danger'>Errore nel caricamento dei file!</p>";
}
?>
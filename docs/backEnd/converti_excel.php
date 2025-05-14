<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Border;  // <<< Import Border per usare stile

// Pulisce i file modificati più vecchi di 10 minuti (600 secondi)
$files = glob('file_modificato_*.xlsx');
$now = time();

foreach ($files as $file) {
    if (is_file($file) && ($now - filemtime($file)) > 600) {
        unlink($file);
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileExcel']) && isset($_FILES['ExcelFile'])) {
    $fileTmpPath = $_FILES['fileExcel']['tmp_name'];
    $fileName = $_FILES['fileExcel']['name'];

    $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
    if (!in_array($fileType, ['xls', 'xlsx'])) {
        die("Errore: il file da convertire deve essere un Excel (.xls, .xlsx)");
    }

    $righeTotaliPagina = isset($_POST['righeTotaliPagina']) && is_numeric($_POST['righeTotaliPagina']) ? (int) $_POST['righeTotaliPagina'] : 81;
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

        function printData($sheet, $arrayOrdinatoFinale, $startRow, $borderThin, $borderThick, $smallFontStyle, $righeTotaliPagina)
        {
            $rowIndex = $startRow;
            $total = count($arrayOrdinatoFinale);
            $i = 0;

            while ($i < $total) {
                // Stampa intestazione tabella
                $sheet->setCellValue('A' . $rowIndex, 'Id')
                    ->setCellValue('B' . $rowIndex, 'Tipo')
                    ->setCellValue('C' . $rowIndex, 'Anzianità')
                    ->setCellValue('D' . $rowIndex, 'Data Visita 1')
                    ->setCellValue('E' . $rowIndex, 'Esito')
                    ->setCellValue('F' . $rowIndex, 'Tecnico')
                    ->setCellValue('G' . $rowIndex, 'Data Visita 2')
                    ->setCellValue('H' . $rowIndex, 'Esito')
                    ->setCellValue('I' . $rowIndex, 'Tecnico');
                $sheet->getStyle('A' . $rowIndex . ':I' . $rowIndex)->applyFromArray($borderThick);
                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setAutoSize(true);
                $sheet->getColumnDimension('E')->setAutoSize(true);
                $sheet->getColumnDimension('F')->setAutoSize(true);
                $sheet->getColumnDimension('G')->setAutoSize(true);
                $sheet->getColumnDimension('H')->setAutoSize(true);
                $sheet->getColumnDimension('I')->setAutoSize(true);
                $rowIndex++;

                // Calcola righe rimanenti
                $righeRimanenti = $total - $i;
                $righeDaStampare = min($righeTotaliPagina, $righeRimanenti);

                // Se le righe sono esattamente quante ne servono per riempire il blocco, lascia una per la firma
                if ($righeDaStampare == $righeTotaliPagina) {
                    $righeDaStampare--;
                }

                // Stampa righe dati
                for ($j = 0; $j < $righeDaStampare && $i < $total; $j++, $i++) {
                    $entry = $arrayOrdinatoFinale[$i];
                    $sheet->setCellValue('A' . $rowIndex, $entry['id']);
                    $sheet->setCellValue('B' . $rowIndex, $entry['description']);
                    $sheet->getStyle('A' . $rowIndex . ':I' . $rowIndex)->applyFromArray($borderThin);
                    $rowIndex++;
                }

                // Stampa sempre la riga firma dopo ogni blocco (anche parziale)
                $sheet->mergeCells("A$rowIndex:C$rowIndex");
                $sheet->mergeCells("D$rowIndex:F$rowIndex");
                $sheet->mergeCells("G$rowIndex:I$rowIndex");

                $sheet->setCellValue("A$rowIndex", 'FIRMA  RESP. CLIENTE');
                $sheet->setCellValue("D$rowIndex", 'Visita 1');
                $sheet->setCellValue("G$rowIndex", 'Visita 2');

                $sheet->getStyle("A$rowIndex:I$rowIndex")->applyFromArray($borderThick);
                $rowIndex++;
            }

            return $rowIndex;
        }


        printData($sheet, $arrayOrdinatoFinale, 4, $borderThin, $borderThick, $smallFontStyle, $righeTotaliPagina);

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
            <link href='../frontEnd/modifica_excel_style.css' rel='stylesheet'>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css' rel='stylesheet'>
        </head>
        <body>
        <div class='back'>
            <input type='button' class='btn btn-primary' value='Torna Indietro' onclick='history.back()'>
        </div>
        <div class='container mt-5'>
            <div class='alert alert-success'>
                <strong>Successo!</strong> Il file è stato modificato correttamente.
            </div>
            <div class='text-center'>
                <a href='download.php?file=$outputFileName' class='btn btn-download'>
                    <i class='bi bi-download'></i> Scaricare il file modificato
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
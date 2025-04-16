<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fileExcel'])) {
    $fileTmpPath = $_FILES['fileExcel']['tmp_name'];
    $fileName = $_FILES['fileExcel']['name'];
    
    $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
    if ($fileType != 'xls' && $fileType != 'xlsx') {
        die("Errore: il file deve essere un Excel (.xls, .xlsx)");
    }

    try {
        $spreadsheet = IOFactory::load($fileTmpPath);
        $resultArray = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
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

        // === Ordinamento personalizzato ===

        function estraiChiaviOrdine($description) {
            if (preg_match('/L(\d+)([SM])(\d+)(?:\.(\d+))?/i', $description, $matches)) {
                $linea = (int)$matches[1];
                $tipo = $matches[2];
                $numero = (int)$matches[3];
                $sottosezione = isset($matches[4]) ? (int)$matches[4] : 0;
                return [$linea, $numero, $sottosezione, $tipo];
            }
            return [PHP_INT_MAX, PHP_INT_MAX, PHP_INT_MAX, 'Z'];
        }

        $sensori = [];
        $moduli = [];

        foreach ($resultArray as $item) {
            if (preg_match('/L\d+S\d+/i', $item['description'])) {
                $sensori[] = $item;
            } elseif (preg_match('/L\d+M\d+/i', $item['description'])) {
                $moduli[] = $item;
            }
        }

        usort($sensori, function($a, $b) {
            return estraiChiaviOrdine($a['description']) <=> estraiChiaviOrdine($b['description']);
        });

        usort($moduli, function($a, $b) {
            return estraiChiaviOrdine($a['description']) <=> estraiChiaviOrdine($b['description']);
        });

        $arrayOrdinatoFinale = array_merge($sensori, $moduli);

        echo "<h3>Array ordinato:</h3><pre>";
        print_r($arrayOrdinatoFinale);
        echo "</pre>";

    } catch (Exception $e) {
        die("Errore durante la conversione: " . $e->getMessage());
    }
} else {
    echo "Nessun file caricato.";
}
?>

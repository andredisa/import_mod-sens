<?php

if (isset($_GET['file'])) {
    $file = basename($_GET['file']); // Sicurezza: rimuove path esterni
    $filePath = __DIR__ . '/' . $file;

    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        unlink($filePath); // Elimina dopo il download
        exit;
    } else {
        echo "File non trovato.";
    }
} else {
    echo "Parametro file mancante.";
}
?>
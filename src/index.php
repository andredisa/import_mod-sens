<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carica e Modifica File Excel</title>
    <!-- Link a Bootstrap 5 (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Link al tuo file CSS personalizzato -->
    <link rel="stylesheet" href="frontEnd/styles.css">
</head>

<body>
    <div class="container">
        <h2>Carica il file Excel e i due CSV (Sensori e Moduli)</h2>
        <form action="backEnd/modifica_excel.php" method="POST" enctype="multipart/form-data" id="uploadForm">
            <div class="mb-4">
                <label for="fileExcel" class="form-label">Excel:</label>
                <input type="file" class="form-control" id="fileExcel" name="fileExcel" accept=".xlsx,.xls" required>
            </div>
            <div class="mb-4">
                <label for="sensoriFile" class="form-label">Carica il file dei sensori CSV o TXT:</label>
                <input type="file" class="form-control" id="sensoriFile" name="sensoriFile" accept=".csv,.txt" required>
            </div>
            <div class="mb-4">
                <label for="moduliFile" class="form-label">Carica il file dei moduli CSV o TXT:</label>
                <input type="file" class="form-control" id="moduliFile" name="moduliFile" accept=".csv,.txt" required>
            </div>
            <button type="submit" class="btn btn-primary btn-lg">Carica e Modifica</button>
        </form>
</body>
</html>
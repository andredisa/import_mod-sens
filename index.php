<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Carica e modifica file Excel</title>
</head>

<body>
    <h2>Carica il file Excel e i due CSV (Sensori e Moduli)</h2>
    <form action="backEnd/modifica_excel.php" method="POST" enctype="multipart/form-data">
        <label>Excel:</label><br>
        <input type="file" name="fileExcel" accept=".xlsx,.xls" required><br><br>

        <label for="sensoriFile">Carica il file dei sensori CSV:</label>
        <input type="file" name="sensoriFile" accept=".csv" required>
        <br><br>
        
        <label for="moduliFile">Carica il file dei moduli CSV:</label>
        <input type="file" name="moduliFile" accept=".csv" required>
        <br><br>

        <input type="submit" value="Carica e Modifica">
    </form>

</body>

</html>

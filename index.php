<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carica e modifica file Excel</title>
</head>
<body>
    <h2>Carica il file Excel da modificare</h2>
    <form action="backEnd/modifica_excel.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="fileExcel" accept=".xlsx, .xls" required>
        <br><br>
        <input type="submit" value="Carica e Modifica">
    </form>
</body>
</html>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione File</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="frontEnd/style_index.css">
</head>

<body>
    <div class="container mt-5 menu1-active">
        <!-- Tabs -->
        <div class="navbar-tabs d-flex">
            <div class="tab-button active menu1" id="menu1">Menu 1</div>
            <div class="tab-button menu2" id="menu2">Menu 2</div>
        </div>

        <!-- Form dinamico -->
        <div class="tab-content" id="tabContent">
            <form action="backEnd/modifica_excel.php" method="POST" enctype="multipart/form-data" id="form1">
                <div class="mb-3">
                    <label for="fileExcel" class="form-label">Scegli il file Excel da aggiornare:</label>
                    <input type="file" class="form-control" id="fileExcel" name="fileExcel" accept=".xlsx,.xls"
                        required>
                </div>
                <div class="mb-3">
                    <label for="sensoriFile" class="form-label">Carica il file dei sensori CSV o TXT:</label>
                    <input type="file" class="form-control" id="sensoriFile" name="sensoriFile" accept=".csv,.txt"
                        required>
                </div>
                <div class="mb-3">
                    <label for="moduliFile" class="form-label">Carica il file dei moduli CSV o TXT:</label>
                    <input type="file" class="form-control" id="moduliFile" name="moduliFile" accept=".csv,.txt"
                        required>
                </div>
                <button type="submit" class="btn btn-primary">Carica e Modifica</button>
            </form>
        </div>
    </div>

    <!-- Template per menu2 -->
    <template id="form2Template">
        <form action="backEnd/converti_excel.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="fileExcelCSV" class="form-label">Scegli il file Excel da converitre in csv:</label>
                <input type="file" class="form-control" name="fileExcel" id="fileExcelCSV" accept=".xls,.xlsx" required>

                <label for="Excel" class="form-label">Scegli il file Excel da aggiornare:</label>
                <input type="file" class="form-control" name="ExcelFile" id="Excel" accept=".xls,.xlsx" required>
            </div>
            <button type="submit" class="btn btn-warning">Carica e Converti</button>
        </form>
    </template>

    <script>
        const menu1 = document.getElementById("menu1");
        const menu2 = document.getElementById("menu2");
        const tabContent = document.getElementById("tabContent");
        const form1Template = document.getElementById("form1Template");
        const form2Template = document.getElementById("form2Template");

        const container = document.querySelector(".container");

        menu1.addEventListener("click", () => {
            setActiveTab(menu1, "menu1", "menu2");
            tabContent.innerHTML = `
                <form action="backEnd/modifica_excel.php" method="POST" enctype="multipart/form-data" id="form1">
                    <div class="mb-3">
                        <label for="fileExcel" class="form-label">Excel:</label>
                        <input type="file" class="form-control" id="fileExcel" name="fileExcel" accept=".xlsx,.xls" required>
                    </div>
                    <div class="mb-3">
                        <label for="sensoriFile" class="form-label">Carica il file dei sensori CSV o TXT:</label>
                        <input type="file" class="form-control" id="sensoriFile" name="sensoriFile" accept=".csv,.txt" required>
                    </div>
                    <div class="mb-3">
                        <label for="moduliFile" class="form-label">Carica il file dei moduli CSV o TXT:</label>
                        <input type="file" class="form-control" id="moduliFile" name="moduliFile" accept=".csv,.txt" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Carica e Modifica</button>
                </form>
            `;
        });

        menu2.addEventListener("click", () => {
            setActiveTab(menu2, "menu2", "menu1");
            const clone = form2Template.content.cloneNode(true);
            tabContent.innerHTML = "";
            tabContent.appendChild(clone);
        });

        function setActiveTab(activeElement, activeClass, inactiveClass) {
            menu1.classList.remove("active", inactiveClass);
            menu2.classList.remove("active", inactiveClass);
            activeElement.classList.add("active", activeClass);
            container.classList.remove(`${inactiveClass}-active`);
            container.classList.add(`${activeClass}-active`);
        }
    </script>
</body>

</html>
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
            <div class="tab-button active menu1" id="menu1">file txt/csv (sensori,moduli)</div>
            <div class="tab-button menu2" id="menu2">file excel unico</div>
        </div>

        <!-- Form dinamico -->
        <div class="tab-content" id="tabContent">
            <form action="backEnd/modifica_excel.php" method="POST" enctype="multipart/form-data" id="form1">
                <div class="mb-3">
                    <label for="fileExcel" class="form-label">Scegli il file Excel da aggiornare:</label>
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
        </div>
    </div>

    <!-- Template per menu2 -->
    <template id="form2Template">
        <form action="backEnd/converti_excel.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="fileExcelCSV" class="form-label">Scegli il file Excel con i sensori e moduli:</label>
                <input type="file" class="form-control" name="fileExcel" id="fileExcelCSV" accept=".xls,.xlsx" required>

                <label for="Excel" class="form-label">Scegli il file Excel da aggiornare:</label>
                <input type="file" class="form-control" name="ExcelFile" id="Excel" accept=".xls,.xlsx" required>
            </div>
            <button type="submit" class="btn btn-warning">Carica e Modifica</button>
        </form>
    </template>
    <script>
    const menu1 = document.getElementById("menu1");
    const menu2 = document.getElementById("menu2");
    const tabContent = document.getElementById("tabContent");
    const form2Template = document.getElementById("form2Template");
    const container = document.querySelector(".container");

    function setActiveTab(activeEl, activeClass, inactiveClass) {
        [menu1, menu2].forEach(tab => {
            tab.classList.remove("active", "menu1", "menu2");
        });

        activeEl.classList.add("active", activeClass);

        container.classList.remove(`${inactiveClass}-active`);
        container.classList.add(`${activeClass}-active`);
    }

    function switchTabContent(newContentHTMLOrNode) {
        // Applica effetto fade-out all'elemento attuale
        tabContent.classList.add("fade-out");

        // Dopo l'animazione di uscita, sostituisci il contenuto
        setTimeout(() => {
            tabContent.classList.remove("fade-out");
            tabContent.innerHTML = "";

            if (typeof newContentHTMLOrNode === "string") {
                tabContent.innerHTML = newContentHTMLOrNode;
            } else {
                tabContent.appendChild(newContentHTMLOrNode);
            }

            tabContent.classList.add("fade-in");

            // Rimuove la classe dopo l'animazione per consentire future animazioni
            setTimeout(() => {
                tabContent.classList.remove("fade-in");
            }, 300);
        }, 250); // stesso valore della durata CSS
    }

    menu1.addEventListener("click", () => {
        setActiveTab(menu1, "menu1", "menu2");

        const form1HTML = `
            <form action="backEnd/modifica_excel.php" method="POST" enctype="multipart/form-data" id="form1">
                <div class="mb-3">
                    <label for="fileExcel" class="form-label">Excel:</label>
                    <input type="file" class="form-control" id="fileExcel" name="fileExcel" accept=".xlsx,.xls" required>
                </div>
                <div class="mb-3">
                    <label for="sensoriFile" class="form-label">File sensori CSV/TXT:</label>
                    <input type="file" class="form-control" id="sensoriFile" name="sensoriFile" accept=".csv,.txt" required>
                </div>
                <div class="mb-3">
                    <label for="moduliFile" class="form-label">File moduli CSV/TXT:</label>
                    <input type="file" class="form-control" id="moduliFile" name="moduliFile" accept=".csv,.txt" required>
                </div>
                <button type="submit" class="btn btn-primary">Carica e Modifica</button>
            </form>
        `;

        switchTabContent(form1HTML);
    });

    menu2.addEventListener("click", () => {
        setActiveTab(menu2, "menu2", "menu1");
        const clone = form2Template.content.cloneNode(true);
        switchTabContent(clone);
    });
</script>

</body>

</html>
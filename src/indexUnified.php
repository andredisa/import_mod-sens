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
        <div class="tab-content" id="tabContent"></div>
    </div>

    <!-- Template per menu1 -->
    <template id="form1Template">
        <form action="backEnd/modifica_excel.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="fileExcel1" class="form-label">Scegli il file Excel da aggiornare:</label>
                <input type="file" class="form-control" id="fileExcel1" name="fileExcel" accept=".xlsx,.xls" required>
            </div>
            <div class="mb-3">
                <label for="sensoriFile" class="form-label">File sensori CSV/TXT:</label>
                <input type="file" class="form-control" id="sensoriFile" name="sensoriFile" accept=".csv,.txt" required>
            </div>
            <div class="mb-3">
                <label for="moduliFile" class="form-label">File moduli CSV/TXT:</label>
                <input type="file" class="form-control" id="moduliFile" name="moduliFile" accept=".csv,.txt" required>
            </div>
            <div class="mb-3">
                <label for="righeTotaliPagina1" class="form-label">Imposta la posizione della riga per la firma (default 53):</label>
                <span class="tooltip-container" onclick="event.stopPropagation()">?
                    <span class="tooltip-text">
                        <div class="tooltip-title">Info</div>
                        <div class="tooltip-separator"></div>
                        <div>Quando inserisci la posizione, la firma verrà posizionata al numero indicato + 1 (esempio: 53 + 1, quindi la riga sarà alla posizione 54).</div>
                    </span>
                </span>
                <input type="number" class="form-control" name="righeTotaliPagina" id="righeTotaliPagina1" value="53" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Carica e Modifica</button>
        </form>
    </template>

    <!-- Template per menu2 -->
    <template id="form2Template">
        <form action="backEnd/converti_excel.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="fileExcelCSV" class="form-label">Scegli il file Excel con i sensori e moduli:</label>
                <input type="file" class="form-control" name="fileExcel" id="fileExcelCSV" accept=".xls,.xlsx" required>

                <label for="Excel" class="form-label">Scegli il file Excel da aggiornare:</label>
                <input type="file" class="form-control" name="ExcelFile" id="Excel" accept=".xls,.xlsx" required>
            </div>
            <div class="mb-3">
                <label for="righeTotaliPagina2" class="form-label">Imposta la posizione della riga per la firma (default 81):</label>
                <span class="tooltip-container" onclick="event.stopPropagation()">?
                    <span class="tooltip-text">
                        <div class="tooltip-title">Info</div>
                        <div class="tooltip-separator"></div>
                        <div>Quando inserisci la posizione, la firma verrà posizionata al numero indicato + 1 (esempio: 81 + 1, quindi la riga sarà alla posizione 82).</div>
                    </span>
                </span>
                <input type="number" class="form-control" name="righeTotaliPagina" id="righeTotaliPagina2" value="81" min="1" required>
            </div>
            <button type="submit" class="btn btn-warning">Carica e Modifica</button>
        </form>
    </template>

    <script>
        const menu1 = document.getElementById("menu1");
        const menu2 = document.getElementById("menu2");
        const tabContent = document.getElementById("tabContent");
        const form1Template = document.getElementById("form1Template");
        const form2Template = document.getElementById("form2Template");
        const container = document.querySelector(".container");

        function setActiveTab(activeEl, activeClass, inactiveClass) {
            [menu1, menu2].forEach(tab => tab.classList.remove("active"));
            activeEl.classList.add("active");
            container.classList.remove(`${inactiveClass}-active`);
            container.classList.add(`${activeClass}-active`);
        }

        function switchTabContent(newContentNode) {
            tabContent.classList.add("fade-out");

            setTimeout(() => {
                tabContent.innerHTML = "";
                tabContent.appendChild(newContentNode);
                tabContent.classList.remove("fade-out");
                tabContent.classList.add("fade-in");

                setTimeout(() => {
                    tabContent.classList.remove("fade-in");
                }, 300);
            }, 250);
        }

        // Inizializzazione con form1
        window.addEventListener("DOMContentLoaded", () => {
            const initialClone = form1Template.content.cloneNode(true);
            switchTabContent(initialClone);
        });

        menu1.addEventListener("click", () => {
            setActiveTab(menu1, "menu1", "menu2");
            const clone = form1Template.content.cloneNode(true);
            switchTabContent(clone);
        });

        menu2.addEventListener("click", () => {
            setActiveTab(menu2, "menu2", "menu1");
            const clone = form2Template.content.cloneNode(true);
            switchTabContent(clone);
        });
    </script>
</body>

</html>

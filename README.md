# 📊 Excel Generator from CSV - Sensors & Modules
> This **PHP project** generates an **Excel file** from three CSV files: **sensors**, **modules**, and an existing **Excel file**. The output is an updated, sorted **Excel file** ready for printing with **headers**, **borders**, and **signature rows**.

---

## 🚀 Main Features
- ✅ **Upload CSV files** for sensors and modules
- 🧠 Intelligent parsing:
  - Supports duplicate rows with the same **Line/Sensor** or **Line/Module**
  - If there are multiple rows with the same ID, **chooses the one with `Type-id ≠ NONE`**
  - If `Type-id = NONE`, includes the row but **without description**
- 🧹  Cleans descriptions (`cleanString()`)
- 🗂️ Sorts data by `Line` and `Sensor/Module`
- 📄 Cleans the **"ALL-6"** sheet of the Excel fil
- 📌 **Inserts data** with formatting and signature rows
- 🎨 Simple and responsive **HTML interface for upload**
- 📥 **Download** the updated Excel file

---

## 📁  Use
>Go to index.php from your browser.

**Upload the three files:**

- **Base Excel file** (must contain the "ALL-6" sheet)
- **CSV of sensors**
- **CSV of modules**

`Click on “Upload and generate”.`
`Download the modified Excel file.`


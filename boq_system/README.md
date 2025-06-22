# BOQ Automation System (Ghana Building Code-Based)

This project is a web-based application designed to help users generate a Bill of Quantities (BOQ) based on building specifications, site conditions, and material/labor rates, adhering to the Ghana Building Code (GS 1207:2018).

## Tech Stack

*   **Frontend**: HTML5, CSS (Bootstrap 5), Vanilla JS
*   **Backend**: PHP 8+ (OOP)
*   **Database**: MySQL
*   **Libraries**:
    *   PDF Export: DomPDF / TCPDF
    *   Excel Export: PHPSpreadsheet
*   **Authentication**: PHP Sessions / JWT
*   **UI Theme**: AdminLTE (planned)

## Folder Structure

```
boq_system/
├── public/                 # Web accessible files
│   ├── index.php           # Main entry point
│   └── assets/             # CSS, JS, images
├── src/                    # PHP source code
│   ├── auth/               # Authentication logic
│   ├── core/               # Core functions, DB connection
│   ├── modules/            # Feature modules
│   │   ├── project/
│   │   ├── site_conditions/
│   │   ├── materials/
│   │   └── boq/
│   └── exports/            # PDF/Excel export logic
├── templates/              # HTML/PHP templates
│   ├── pdf_template.php
│   ├── excel_template.php
└── data/                   # Data files
    ├── sql/                # SQL schemas, migrations
    └── uploads/            # User uploaded files (e.g., CSVs)
```

## Setup

(Instructions to be added later)

## Usage

(Instructions to be added later)

## Contributing

(Guidelines for contributing to be added later)

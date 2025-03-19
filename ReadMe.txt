/dashboard/
├── config/
│   └── database.php       # Configuration de la connexion à la BDD
├── includes/
│   ├── header.php         # En-tête HTML commun
│   ├── footer.php         # Pied de page HTML commun
│   └── functions.php      # Fonctions utilitaires
├── assets/
│   ├── css/               # Fichiers CSS (Bootstrap, etc.)
│   ├── js/                # JavaScript (DataTables, Chart.js)
│   └── images/            # Images du dashboard
├── exports/               # Dossier pour les fichiers d'export (CSV, Excel)
├── index.php              # Page principale du dashboard
└── api/
    ├── get_sales_data.php # API pour récupérer les données filtrées
    ├── export_csv.php     # Export des données en CSV
    └── export_excel.php   # Export des données en Excel

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    // Initialiser DataTable
    const salesTable = $('#salesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/fr-FR.json'
        },
        responsive: true
    });
    
    // Initialiser les graphiques
    const ctx1 = document.getElementById('salesByCountryChart').getContext('2d');
    const salesByCountryChart = new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chartLabels); ?>,
            datasets: [{
                label: 'CA HT (€)',
                data: <?php echo json_encode($chartData); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    const ctx2 = document.getElementById('salesDistributionChart').getContext('2d');
    const salesDistributionChart = new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($chartLabels); ?>,
            datasets: [{
                data: <?php echo json_encode($chartData); ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)',
                    'rgba(255, 159, 64, 0.5)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        }
    });
    
    // Gérer la soumission du formulaire de filtre
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'api/get_sales_data.php',
            type: 'GET',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                // Mettre à jour le tableau
                salesTable.clear();
                
                if (response.length > 0) {
                    response.forEach(function(item) {
                        salesTable.row.add([
                            item.country_name,
                            parseFloat(item.total_products_ht).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €',
                            parseFloat(item.total_shipping_ht).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €',
                            parseFloat(item.total_revenue_with_shipping_ht).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €',
                            parseFloat(item.total_refunds_ht).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €',
                            parseFloat(item.total_revenue_ht_after_refunds).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €',
                            item.total_orders
                        ]);
                    });
                }
                
                salesTable.draw();
                
                // Mettre à jour les graphiques
                const labels = response.map(item => item.country_name);
                const data = response.map(item => item.total_revenue_ht_after_refunds);
                
                salesByCountryChart.data.labels = labels;
                salesByCountryChart.data.datasets[0].data = data;
                salesByCountryChart.update();
                
                salesDistributionChart.data.labels = labels;
                salesDistributionChart.data.datasets[0].data = data;
                salesDistributionChart.update();
            },
            error: function(xhr, status, error) {
                console.error('Erreur AJAX:', error);
                alert('Une erreur est survenue lors du chargement des données.');
            }
        });
    });
    
    // Bouton d'actualisation
    $('#refreshBtn').on('click', function() {
        $('#filterForm')[0].reset();
        $('#filterForm').submit();
    });
    
    // Bouton d'export CSV
    $('#exportCsvBtn').on('click', function() {
        const queryParams = $('#filterForm').serialize();
        window.location.href = 'api/export_csv.php?' + queryParams;
    });
    
    // Bouton d'export Excel
    $('#exportExcelBtn').on('click', function() {
        const queryParams = $('#filterForm').serialize();
        window.location.href = 'api/export_excel.php?' + queryParams;
    });
});
</script>
</body>
</html>

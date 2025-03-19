<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Vérifier si le formulaire de connexion a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    // Informations d'identification valides
    $valid_username = "pierre";
    $valid_password = "Esteban34";

    // Récupérer les données du formulaire
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Vérifier les informations d'identification
    if ($username === $valid_username && $password === $valid_password) {
        $authenticated = true;
    } else {
        $error_message = "Nom d'utilisateur ou mot de passe incorrect.";
    }
}

// Récupérer les valeurs des filtres soumis
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : null;
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : null;
$category_id = isset($_POST['category']) ? $_POST['category'] : null;
$limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Récupérer les données pour les filtres
$countries = getCountries($conn);
$orderStatuses = getOrderStatuses($conn);
$categories = getCategories($conn);

// Récupérer les données de vente initiales (année courante)
$salesData = getSalesData($conn, $start_date, $end_date);
$productSalesData = getProductSalesData($conn, $start_date, $end_date, $category_id, $limit, $offset);

// Préparer les données pour les graphiques
$chartLabels = [];
$chartData = [];

foreach ($salesData as $data) {
    $chartLabels[] = $data['country_name'];
    $chartData[] = $data['total_revenue_ht_after_refunds'];
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <?php if (!isset($authenticated)): ?>
        <div class="card">
            <div class="card-body">
                <h2>Connexion</h2>
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Utilisateur :</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe :</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary">Se connecter</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="container-fluid mt-4">
            <h1 class="mb-4">Dashboard des Ventes PrestaShop</h1>

            <!-- Filtres -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Filtres</h5>
                </div>
                <div class="card-body">
                    <form id="filterForm" method="post" class="row g-3">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                        <div class="col-md-3">
                            <label for="country" class="form-label">Pays</label>
                            <select class="form-select" id="country" name="country">
                                <option value="">Tous les pays</option>
                                <?php foreach ($countries as $country): ?>
                                    <option value="<?php echo $country['id_country']; ?>"><?php echo $country['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="order_status" class="form-label">Statut de commande</label>
                            <select class="form-select" id="order_status" name="order_status">
                                <option value="">Tous les statuts</option>
                                <?php foreach ($orderStatuses as $status): ?>
                                    <option value="<?php echo $status['id_order_state']; ?>"><?php echo $status['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">Catégorie</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">Toutes les catégories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id_category']; ?>"><?php echo $category['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="limit" class="form-label">Entrées par page</label>
                            <select class="form-select" id="limit" name="limit">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Filtrer</button>
                            <button type="button" id="refreshBtn" class="btn btn-secondary">Actualiser</button>
                            <button type="button" id="exportCsvBtn" class="btn btn-success">Exporter en CSV</button>
                            <button type="button" id="exportExcelBtn" class="btn btn-success">Exporter en Excel</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Graphiques -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Ventes par pays</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="salesByCountryChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Répartition des ventes</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="salesDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableau des ventes -->
            <div class="card">
                <div class="card-header">
                    <h5>Détails des ventes par pays</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="salesTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Pays</th>
                                    <th>CA HT (Produits)</th>
                                    <th>CA HT (Livraison)</th>
                                    <th>CA HT Total</th>
                                    <th>Remboursements HT</th>
                                    <th>CA HT Final</th>
                                    <th>Commandes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($salesData as $data): ?>
                                <tr>
                                    <td><?php echo $data['country_name']; ?></td>
                                    <td><?php echo number_format($data['total_products_ht'], 2, ',', ' '); ?> €</td>
                                    <td><?php echo number_format($data['total_shipping_ht'], 2, ',', ' '); ?> €</td>
                                    <td><?php echo number_format($data['total_revenue_with_shipping_ht'], 2, ',', ' '); ?> €</td>
                                    <td><?php echo number_format($data['total_refunds_ht'], 2, ',', ' '); ?> €</td>
                                    <td><?php echo number_format($data['total_revenue_ht_after_refunds'], 2, ',', ' '); ?> €</td>
                                    <td><?php echo $data['total_orders']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Détails des ventes par produit -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Détails des ventes par produit</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="productSalesTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Nom du produit</th>
                                    <th>CA HT</th>
                                    <th>Nombre de commandes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productSalesData as $data): ?>
                                <tr>
                                    <td><?php echo $data['id_product']; ?></td>
                                    <td><?php echo $data['product_name']; ?></td>
                                    <td><?php echo number_format($data['total_revenue_ht'], 2, ',', ' '); ?> €</td>
                                    <td><?php echo $data['total_orders']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $page == 5 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

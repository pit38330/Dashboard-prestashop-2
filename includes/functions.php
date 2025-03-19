<?php
function getSalesData($conn, $start_date = null, $end_date = null, $country_id = null, $order_status = null) {
    // Base de la requête SQL
    $sql = "SELECT
        a.id_country,
        cl.name AS country_name,
        SUM(o.total_products) AS total_products_ht,
        SUM(o.total_shipping_tax_excl) AS total_shipping_ht,
        SUM(o.total_products) + SUM(o.total_shipping_tax_excl) AS total_revenue_with_shipping_ht,
        SUM(o.total_products + os.total_shipping_tax_excl) AS total_refunds_ht,
        SUM(o.total_products) - COALESCE(SUM(os.total_products_tax_excl + os.total_shipping_tax_excl), 0) AS total_revenue_ht_after_refunds,
        COUNT(o.id_order) AS total_orders,
        GROUP_CONCAT(o.reference SEPARATOR ', ') AS order_references
    FROM
        ps_orders o
    JOIN
        ps_address a ON o.id_address_delivery = a.id_address
    JOIN
        ps_country_lang cl ON a.id_country = cl.id_country
    LEFT JOIN
        ps_order_slip os ON o.id_order = os.id_order
    WHERE
        o.valid = 1
        AND cl.id_lang = 1";

    // Ajout des filtres dynamiques
    if ($start_date && $end_date) {
        $sql .= " AND o.date_add BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
    } else {
        $sql .= " AND YEAR(o.date_add) = " . date('Y');
    }

    if ($country_id) {
        $sql .= " AND a.id_country = $country_id";
    }

    if ($order_status) {
        $sql .= " AND o.current_state = $order_status";
    }

    // Finir la requête
    $sql .= " GROUP BY a.id_country, cl.name
        ORDER BY total_revenue_with_shipping_ht DESC";

    // Exécuter la requête
    $result = $conn->query($sql);
    $data = array();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    return $data;
}

function getCountries($conn) {
    $sql = "SELECT id_country, name FROM ps_country_lang WHERE id_lang = 1 ORDER BY name";
    $result = $conn->query($sql);
    $countries = array();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $countries[] = $row;
        }
    }

    return $countries;
}

function getOrderStatuses($conn) {
    $sql = "SELECT id_order_state, name FROM ps_order_state_lang WHERE id_lang = 1 ORDER BY name";
    $result = $conn->query($sql);
    $statuses = array();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $statuses[] = $row;
        }
    }

    return $statuses;
}

function getProductSalesData($conn, $start_date = null, $end_date = null, $category_id = null, $limit = 25, $offset = 0) {
    // Base de la requête SQL
    $sql = "SELECT
        p.id_product,
        pl.name AS product_name,
        SUM(od.product_quantity) AS total_quantity,
        SUM(od.product_price * od.product_quantity) AS total_revenue_ht,
        COUNT(DISTINCT o.id_order) AS total_orders
    FROM
        ps_order_detail od
    JOIN
        ps_orders o ON od.id_order = o.id_order
    JOIN
        ps_product p ON od.product_id = p.id_product
    JOIN
        ps_product_lang pl ON p.id_product = pl.id_product
    JOIN
        ps_category_product cp ON p.id_product = cp.id_product
    WHERE
        o.valid = 1
        AND pl.id_lang = 1";

    // Ajout des filtres dynamiques
    if ($start_date && $end_date) {
        $sql .= " AND o.date_add BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
    } else {
        $sql .= " AND YEAR(o.date_add) = " . date('Y');
    }

    if ($category_id) {
        $sql .= " AND cp.id_category = $category_id";
    }

    // Finir la requête avec pagination
    $sql .= " GROUP BY p.id_product, pl.name
        ORDER BY total_revenue_ht DESC
        LIMIT $limit OFFSET $offset";

    // Exécuter la requête
    $result = $conn->query($sql);
    $data = array();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    return $data;
}

function getCategories($conn) {
    $sql = "SELECT id_category, name FROM ps_category_lang WHERE id_lang = 1 ORDER BY name";
    $result = $conn->query($sql);
    $categories = array();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }

    return $categories;
}
?>

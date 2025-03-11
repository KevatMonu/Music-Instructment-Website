<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "musicstore_database");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's orders with pagination
$limit = 10; // Number of orders per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total orders for pagination
$countStmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE user_ref = ?");
$countStmt->bind_param("i", $user_id);
$countStmt->execute();
$totalResult = $countStmt->get_result();
$totalOrders = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalOrders / $limit);

// Fetch orders with sorting
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'order_created';
$sortOrder = isset($_GET['order']) && $_GET['order'] == 'asc' ? 'ASC' : 'DESC';

// Only allow sorting by valid columns
$allowedSortColumns = ['order_id', 'order_created', 'total_cost', 'order_status', 'order_type'];
if (!in_array($sortBy, $allowedSortColumns)) {
    $sortBy = 'order_created';
}

// Filter by order status if requested
$statusFilter = '';
$filterParams = [];
$filterTypes = '';

if (isset($_GET['status']) && in_array($_GET['status'], ['completed', 'cancelled'])) {
    $statusFilter = " AND order_status = ?";
    $filterParams[] = $_GET['status'];
    $filterTypes .= "s";
}

// Filter by order type if requested
if (isset($_GET['type']) && in_array($_GET['type'], ['buy', 'rent'])) {
    $statusFilter .= " AND order_type = ?";
    $filterParams[] = $_GET['type'];
    $filterTypes .= "s";
}

// Prepare the query with filters
$query = "SELECT * FROM orders WHERE user_ref = ?" . $statusFilter . 
         " ORDER BY $sortBy $sortOrder LIMIT ? OFFSET ?";

// Add parameters for the main query
array_unshift($filterParams, $user_id);
$filterTypes = "i" . $filterTypes . "ii";
$filterParams[] = $limit;
$filterParams[] = $offset;

$stmt = $conn->prepare($query);
$stmt->bind_param($filterTypes, ...$filterParams);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();

// Helper function for sorting links
function getSortLink($column, $currentSort, $currentOrder) {
    $newOrder = ($currentSort == $column && $currentOrder == 'desc') ? 'asc' : 'desc';
    $params = $_GET;
    $params['sort'] = $column;
    $params['order'] = $newOrder;
    return '?' . http_build_query($params);
}

// Helper function for filter links
function getFilterLink($param, $value) {
    $params = $_GET;
    if (isset($params[$param]) && $params[$param] == $value) {
        unset($params[$param]); // Toggle off if already active
    } else {
        $params[$param] = $value;
    }
    // Reset pagination when changing filters
    unset($params['page']);
    return '?' . http_build_query($params);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Music Store</title>
    <link rel="stylesheet" href="https://unpkg.com/lucide-static@latest/font/lucide.css">
    <link rel="stylesheet" href = "css/user_order_detail.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>My Orders</h1>
            <div class="filters">
                <div class="filter-group">
                    <span>Filter by status:</span>
                    <a href="<?php echo getFilterLink('status', 'completed'); ?>" class="filter-link <?php echo (isset($_GET['status']) && $_GET['status'] == 'completed') ? 'active' : ''; ?>">Completed</a>
                    <a href="<?php echo getFilterLink('status', 'cancelled'); ?>" class="filter-link <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? 'active' : ''; ?>">Cancelled</a>
                </div>
                <div class="filter-group">
                    <span>Filter by type:</span>
                    <a href="<?php echo getFilterLink('type', 'buy'); ?>" class="filter-link <?php echo (isset($_GET['type']) && $_GET['type'] == 'buy') ? 'active' : ''; ?>">Purchase</a>
                    <a href="<?php echo getFilterLink('type', 'rent'); ?>" class="filter-link <?php echo (isset($_GET['type']) && $_GET['type'] == 'rent') ? 'active' : ''; ?>">Rental</a>
                </div>
            </div>
        </div>

        <?php if (empty($orders)): ?>
        <div class="no-orders">
            <p>You haven't placed any orders yet.</p>
            <a href="products.php">Browse Products</a>
        </div>
        <?php else: ?>
        <div class="orders-table">
            <table>
                <thead>
                    <tr>
                        <th>
                            <a href="<?php echo getSortLink('order_id', $sortBy, $sortOrder); ?>">
                                Order ID
                                <?php if ($sortBy == 'order_id'): ?>
                                <span class="sort-icon"><?php echo $sortOrder == 'asc' ? '↑' : '↓'; ?></span>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>
                            <a href="<?php echo getSortLink('order_created', $sortBy, $sortOrder); ?>">
                                Date
                                <?php if ($sortBy == 'order_created'): ?>
                                <span class="sort-icon"><?php echo $sortOrder == 'asc' ? '↑' : '↓'; ?></span>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>
                            <a href="<?php echo getSortLink('total_cost', $sortBy, $sortOrder); ?>">
                                Total
                                <?php if ($sortBy == 'total_cost'): ?>
                                <span class="sort-icon"><?php echo $sortOrder == 'asc' ? '↑' : '↓'; ?></span>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>
                            <a href="<?php echo getSortLink('order_status', $sortBy, $sortOrder); ?>">
                                Status
                                <?php if ($sortBy == 'order_status'): ?>
                                <span class="sort-icon"><?php echo $sortOrder == 'asc' ? '↑' : '↓'; ?></span>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>
                            <a href="<?php echo getSortLink('order_type', $sortBy, $sortOrder); ?>">
                                Type
                                <?php if ($sortBy == 'order_type'): ?>
                                <span class="sort-icon"><?php echo $sortOrder == 'asc' ? '↑' : '↓'; ?></span>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <span class="order-id">#<?php echo $order['order_id']; ?></span>
                        </td>
                        <td><?php echo date('M d, Y - h:i A', strtotime($order['order_created'])); ?></td>
                        <td>$<?php echo number_format($order['total_cost'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($order['order_status']); ?>">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="type-badge type-<?php echo strtolower($order['order_type']); ?>">
                                <?php echo ucfirst($order['order_type']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="order_detail.php?order_id=<?php echo $order['order_id']; ?>" class="view-btn">
                                View Details
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page-1; ?><?php echo isset($_GET['sort']) ? '&sort='.$_GET['sort'] : ''; ?><?php echo isset($_GET['order']) ? '&order='.$_GET['order'] : ''; ?><?php echo isset($_GET['status']) ? '&status='.$_GET['status'] : ''; ?><?php echo isset($_GET['type']) ? '&type='.$_GET['type'] : ''; ?>" class="page-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide-chevron-left"><path d="m15 18-6-6 6-6"/></svg>
                Previous
            </a>
            <?php endif; ?>
            
            <div class="page-numbers">
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="?page=<?php echo $i; ?><?php echo isset($_GET['sort']) ? '&sort='.$_GET['sort'] : ''; ?><?php echo isset($_GET['order']) ? '&order='.$_GET['order'] : ''; ?><?php echo isset($_GET['status']) ? '&status='.$_GET['status'] : ''; ?><?php echo isset($_GET['type']) ? '&type='.$_GET['type'] : ''; ?>" class="page-number <?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
            </div>
            
            <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page+1; ?><?php echo isset($_GET['sort']) ? '&sort='.$_GET['sort'] : ''; ?><?php echo isset($_GET['order']) ? '&order='.$_GET['order'] : ''; ?><?php echo isset($_GET['status']) ? '&status='.$_GET['status'] : ''; ?><?php echo isset($_GET['type']) ? '&type='.$_GET['type'] : ''; ?>" class="page-link">
                Next
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide-chevron-right"><path d="m9 18 6-6-6-6"/></svg>
            </a>
            <?php endif; ?>
            
            <span class="page-info">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
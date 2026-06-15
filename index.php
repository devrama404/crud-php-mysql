<?php
require_once 'config/database.php';

// Get database connection
$database = new Database();
$conn = $database->getConnection();

// Get categories with product counts and total inventory value
$categories_query = "
    SELECT 
        c.id,
        c.name,
        c.icon,
        c.color,
        COUNT(p.id) as product_count,
        COALESCE(SUM(p.price * p.stock), 0) as total_value
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    GROUP BY c.id
    ORDER BY c.name
";
$categories_result = $conn->query($categories_query);

// Get overall statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_products,
        SUM(stock) as total_stock,
        SUM(price * stock) as total_inventory_value
    FROM products
";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Multi-Product Management System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏪 Sistem Manajemen Produk V1</h1>
            <p>Multi-Product Management System</p>
        </div>

        <nav class="nav">
            <a href="index.php" class="active">🏠 Dashboard</a>
            <a href="crud/index.php">📦 Semua Produk</a>
            <a href="crud/create.php">➕ Tambah Produk</a>
        </nav>

        <div class="content">
            <h2 style="margin-bottom: 24px; font-size: 1.8em;">Dashboard Overview</h2>
            
            <div class="dashboard">
                <div class="card" style="--card-index: 0; --card-gradient: linear-gradient(135deg, #667eea, #764ba2);">
                    <h3>Total Produk</h3>
                    <div class="number"><?php echo number_format($stats['total_products'] ?? 0); ?></div>
                    <p>Semua kategori</p>
                </div>

                <div class="card" style="--card-index: 1; --card-gradient: linear-gradient(135deg, #10b981, #059669);">
                    <h3>Total Stok</h3>
                    <div class="number"><?php echo number_format($stats['total_stock'] ?? 0); ?></div>
                    <p>Unit tersedia</p>
                </div>

                <div class="card" style="--card-index: 2; --card-gradient: linear-gradient(135deg, #f59e0b, #d97706);">
                    <h3>Nilai Inventory</h3>
                    <div class="number" style="font-size: 2em;">Rp <?php echo number_format($stats['total_inventory_value'] ?? 0, 0, ',', '.'); ?></div>
                    <p>Total nilai semua produk</p>
                </div>
            </div>

            <h2 style="margin: 40px 0 24px 0; font-size: 1.8em;">Produk Per Kategori</h2>
            
            <div class="dashboard">
                <?php 
                $card_index = 0;
                while($cat = $categories_result->fetch_assoc()): 
                ?>
                    <div class="card" style="--card-index: <?php echo $card_index++; ?>; --card-gradient: linear-gradient(135deg, <?php echo $cat['color']; ?>, <?php echo $cat['color']; ?>dd);">
                        <h3><?php echo trim(htmlspecialchars($cat['icon'])); ?> <?php echo htmlspecialchars($cat['name']); ?></h3>
                        <div class="number"><?php echo $cat['product_count']; ?></div>
                        <p>Nilai: Rp <?php echo number_format($cat['total_value'], 0, ',', '.'); ?></p>
                        <a href="crud/index.php?category_id=<?php echo $cat['id']; ?>" class="btn btn-primary btn-small" style="margin-top: 16px;">Lihat Produk</a>
                    </div>
                <?php endwhile; ?>
            </div>

            <div style="margin-top: 40px; padding: 30px; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                <h3 style="margin-bottom: 20px; font-size: 1.5em;">Quick Actions</h3>
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <a href="crud/create.php" class="btn btn-primary">➕ Tambah Produk Baru</a>
                    <a href="crud/index.php" class="btn btn-success">📋 Lihat Semua Produk</a>
                    <a href="crud/index.php?stock=low" class="btn btn-warning">⚠️ Stok Menipis</a>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>&copy; 2026 LAB 5 Quiz Project V2 - Multi-Product Management System</p>
        </div>
    </div>
    
    <script src="assets/script.js"></script>
</body>
</html>
<?php
$database->closeConnection();
?>

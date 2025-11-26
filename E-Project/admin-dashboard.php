<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Fetch dashboard statistics
$today = date('Y-m-d');
$appointments_today = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = '$today'")->fetchColumn();
$total_clients = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn();
$weekly_revenue = $pdo->query("SELECT SUM(s.price) FROM appointments a JOIN services s ON a.service_id = s.id WHERE YEARWEEK(a.appointment_date) = YEARWEEK(NOW()) AND a.status = 'completed'")->fetchColumn();
$low_stock_items = $pdo->query("SELECT COUNT(*) FROM inventory WHERE current_stock <= reorder_level")->fetchColumn();

// Fetch today's appointments
$today_appointments = $pdo->query("
    SELECT a.*, u.name as client_name, s.name as service_name, st.name as stylist_name 
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    JOIN services s ON a.service_id = s.id 
    JOIN staff st ON a.stylist_id = st.id 
    WHERE a.appointment_date = '$today' 
    ORDER BY a.appointment_time
")->fetchAll();

// Fetch low stock items
$low_stock = $pdo->query("SELECT * FROM inventory WHERE current_stock <= reorder_level")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Elegance Salon</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <header>
        <div class="container header-container">
            <div class="logo">
                <i class="fas fa-spa"></i>
                <h1>Elegance Salon</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php" class="nav-link">Home</a></li>
                    <li><a href="services.php" class="nav-link">Services</a></li>
                    <li><a href="appointments.php" class="nav-link">Appointments</a></li>
                    <li><a href="contact.php" class="nav-link">Contact</a></li>
                </ul>
            </nav>
            <div class="auth-buttons">
                <span>Welcome, <?php echo $_SESSION['user_name']; ?> (Admin)</span>
                <a href="admin-dashboard.php" class="btn btn-outline">Admin Dashboard</a>
                <a href="logout.php" class="btn btn-primary">Logout</a>
            </div>
        </div>
    </header>

    <section class="page">
        <div class="dashboard">
            <div class="sidebar">
                <ul class="sidebar-menu">
                    <li><a href="#" class="dashboard-link active" data-dashboard="admin-overview"><i class="fas fa-tachometer-alt"></i> Overview</a></li>
                    <li><a href="#" class="dashboard-link" data-dashboard="admin-appointments"><i class="fas fa-calendar-alt"></i> Appointments</a></li>
                    <li><a href="#" class="dashboard-link" data-dashboard="admin-clients"><i class="fas fa-users"></i> Clients</a></li>
                    <li><a href="#" class="dashboard-link" data-dashboard="admin-staff"><i class="fas fa-user-tie"></i> Staff</a></li>
                    <li><a href="#" class="dashboard-link" data-dashboard="admin-inventory"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li><a href="#" class="dashboard-link" data-dashboard="admin-reports"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
            <div class="dashboard-content">
                <div class="dashboard-header">
                    <h2>Admin Dashboard</h2>
                    <p>Welcome back, <?php echo $_SESSION['user_name']; ?>!</p>
                </div>
                
                <div id="admin-overview" class="dashboard-page active">
                    <h3>Business Overview</h3>
                    <div class="stats">
                        <div class="stat-card">
                            <i class="fas fa-calendar-check"></i>
                            <h3><?php echo $appointments_today; ?></h3>
                            <p>Appointments Today</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-users"></i>
                            <h3><?php echo $total_clients; ?></h3>
                            <p>Total Clients</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-dollar-sign"></i>
                            <h3>$<?php echo number_format($weekly_revenue ?: 0, 2); ?></h3>
                            <p>Revenue This Week</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h3><?php echo $low_stock_items; ?></h3>
                            <p>Low Stock Items</p>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <h3>Today's Appointments</h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Client</th>
                                        <th>Service</th>
                                        <th>Stylist</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($today_appointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></td>
                                        <td><?php echo $appointment['client_name']; ?></td>
                                        <td><?php echo $appointment['service_name']; ?></td>
                                        <td><?php echo $appointment['stylist_name']; ?></td>
                                        <td><span class="btn btn-<?php echo $appointment['status'] === 'completed' ? 'success' : 'warning'; ?> btn-sm"><?php echo ucfirst($appointment['status']); ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="form-group">
                            <h3>Low Stock Alert</h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Current Stock</th>
                                        <th>Reorder Level</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($low_stock as $item): ?>
                                    <tr>
                                        <td><?php echo $item['product_name']; ?></td>
                                        <td><?php echo $item['current_stock']; ?></td>
                                        <td><?php echo $item['reorder_level']; ?></td>
                                        <td><button class="btn btn-primary btn-sm">Reorder</button></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Other dashboard sections would be implemented similarly -->
            </div>
        </div>
    </section>

    <footer>
        <!-- Same footer as index.php -->
    </footer>

    <script>
        // Dashboard navigation
        document.querySelectorAll('.dashboard-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const dashboardId = this.getAttribute('data-dashboard');
                
                document.querySelectorAll('.dashboard-link').forEach(item => {
                    item.classList.remove('active');
                });
                
                this.classList.add('active');
                
                document.querySelectorAll('.dashboard-page').forEach(page => {
                    page.classList.remove('active');
                });
                
                document.getElementById(dashboardId).classList.add('active');
            });
        });
    </script>
</body>
</html>
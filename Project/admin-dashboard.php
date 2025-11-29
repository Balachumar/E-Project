<?php
require_once 'config.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: index.php');
    exit();
}

if (isset($_POST['update_appointment_status'])) {
    $appointment_id = sanitize_input($_POST['appointment_id']);
    $status = sanitize_input($_POST['status']);
    $sql = "UPDATE appointments SET status = '$status' WHERE id = $appointment_id";
    $conn->query($sql);
}

if (isset($_POST['reorder_item'])) {
    $item_id = sanitize_input($_POST['item_id']);
    $quantity = sanitize_input($_POST['quantity']);
    $sql = "UPDATE inventory SET current_stock = current_stock + $quantity WHERE id = $item_id";
    $conn->query($sql);
}

$today = date('Y-m-d');
$week_start = date('Y-m-d', strtotime('monday this week'));

$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM appointments WHERE appointment_date = '$today' AND status != 'cancelled') as today_appointments,
    (SELECT COUNT(*) FROM users WHERE role = 'user') as total_clients,
    (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE DATE(transaction_date) >= '$week_start') as week_revenue,
    (SELECT COUNT(*) FROM inventory WHERE current_stock < reorder_level) as low_stock";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

$dashboard_page = isset($_GET['view']) ? $_GET['view'] : 'overview';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Elegance Salon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
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
                    <li><a href="index.php?page=home" class="nav-link">Home</a></li>
                    <li><a href="index.php?page=services" class="nav-link">Services</a></li>
                    <li><a href="index.php?page=appointments" class="nav-link">Appointments</a></li>
                    <li><a href="index.php?page=contact" class="nav-link">Contact</a></li>
                </ul>
            </nav>
            <div class="auth-buttons">
                <span style="color: white; margin-right: 15px;">Admin: <?php echo $_SESSION['user_name']; ?></span>
                <a href="index.php?logout=1" class="btn btn-primary">Logout</a>
            </div>
        </div>
    </header>

    <section class="page">
        <div class="dashboard">
            <div class="sidebar">
                <ul class="sidebar-menu">
                    <li><a href="?view=overview" class="dashboard-link <?php echo $dashboard_page == 'overview' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i> Overview</a></li>
                    <li><a href="?view=appointments" class="dashboard-link <?php echo $dashboard_page == 'appointments' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i> Appointments</a></li>
                    <li><a href="?view=clients" class="dashboard-link <?php echo $dashboard_page == 'clients' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Clients</a></li>
                    <li><a href="?view=staff" class="dashboard-link <?php echo $dashboard_page == 'staff' ? 'active' : ''; ?>">
                        <i class="fas fa-user-tie"></i> Staff</a></li>
                    <li><a href="?view=inventory" class="dashboard-link <?php echo $dashboard_page == 'inventory' ? 'active' : ''; ?>">
                        <i class="fas fa-boxes"></i> Inventory</a></li>
                    <li><a href="?view=reports" class="dashboard-link <?php echo $dashboard_page == 'reports' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="index.php?logout=1" class="logout-link">
                        <i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
            <div class="dashboard-content">
                <div class="dashboard-header">
                    <h2>Admin Dashboard</h2>
                    <p>Welcome back, Admin!</p>
                </div>
                
                <?php if ($dashboard_page == 'overview'): ?>
                <div id="admin-overview" class="dashboard-page active">
                    <h3>Business Overview</h3>
                    <div class="stats">
                        <div class="stat-card">
                            <i class="fas fa-calendar-check"></i>
                            <h3><?php echo $stats['today_appointments']; ?></h3>
                            <p>Appointments Today</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-users"></i>
                            <h3><?php echo $stats['total_clients']; ?></h3>
                            <p>Total Clients</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-dollar-sign"></i>
                            <h3>$<?php echo number_format($stats['week_revenue'], 2); ?></h3>
                            <p>Revenue This Week</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h3><?php echo $stats['low_stock']; ?></h3>
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
                                    <?php
                                    $today_appointments_sql = "SELECT a.*, 
                                        CONCAT(u.first_name, ' ', u.last_name) as client_name,
                                        s.name as service_name,
                                        CONCAT(su.first_name, ' ', su.last_name) as stylist_name
                                        FROM appointments a
                                        JOIN users u ON a.user_id = u.id
                                        JOIN services s ON a.service_id = s.id
                                        LEFT JOIN staff st ON a.staff_id = st.id
                                        LEFT JOIN users su ON st.user_id = su.id
                                        WHERE a.appointment_date = '$today' AND a.status != 'cancelled'
                                        ORDER BY a.appointment_time";
                                    $today_appointments = $conn->query($today_appointments_sql);
                                    
                                    if ($today_appointments->num_rows > 0):
                                        while ($app = $today_appointments->fetch_assoc()):
                                            $status_class = '';
                                            switch($app['status']) {
                                                case 'completed': $status_class = 'btn-success'; break;
                                                case 'confirmed': $status_class = 'btn-warning'; break;
                                                default: $status_class = 'btn-warning';
                                            }
                                    ?>
                                    <tr>
                                        <td><?php echo date('g:i A', strtotime($app['appointment_time'])); ?></td>
                                        <td><?php echo htmlspecialchars($app['client_name']); ?></td>
                                        <td><?php echo htmlspecialchars($app['service_name']); ?></td>
                                        <td><?php echo $app['stylist_name'] ? htmlspecialchars($app['stylist_name']) : 'Not Assigned'; ?></td>
                                        <td><span class="btn <?php echo $status_class; ?> btn-sm"><?php echo ucfirst($app['status']); ?></span></td>
                                    </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center;">No appointments today</td>
                                    </tr>
                                    <?php endif; ?>
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
                                    <?php
                                    $low_stock_sql = "SELECT * FROM inventory WHERE current_stock < reorder_level LIMIT 5";
                                    $low_stock = $conn->query($low_stock_sql);
                                    
                                    if ($low_stock->num_rows > 0):
                                        while ($item = $low_stock->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                        <td><?php echo $item['current_stock']; ?></td>
                                        <td><?php echo $item['reorder_level']; ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                <input type="hidden" name="quantity" value="<?php echo $item['reorder_level'] * 2; ?>">
                                                <button type="submit" name="reorder_item" class="btn btn-primary btn-sm">Reorder</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center;">All items are well stocked</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <?php elseif ($dashboard_page == 'appointments'): ?>
                <div id="admin-appointments" class="dashboard-page">
                    <div class="dashboard-header">
                        <h3>Appointment Management</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Stylist</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $all_appointments_sql = "SELECT a.*, 
                                CONCAT(u.first_name, ' ', u.last_name) as client_name,
                                s.name as service_name,
                                CONCAT(su.first_name, ' ', su.last_name) as stylist_name
                                FROM appointments a
                                JOIN users u ON a.user_id = u.id
                                JOIN services s ON a.service_id = s.id
                                LEFT JOIN staff st ON a.staff_id = st.id
                                LEFT JOIN users su ON st.user_id = su.id
                                WHERE a.appointment_date >= CURDATE()
                                ORDER BY a.appointment_date, a.appointment_time";
                            $all_appointments = $conn->query($all_appointments_sql);
                            
                            while ($app = $all_appointments->fetch_assoc()):
                                $status_class = '';
                                switch($app['status']) {
                                    case 'completed': $status_class = 'btn-success'; break;
                                    case 'confirmed': $status_class = 'btn-warning'; break;
                                    case 'cancelled': $status_class = 'btn-danger'; break;
                                    default: $status_class = 'btn-warning';
                                }
                            ?>
                            <tr>
                                <td><?php echo date('F j, Y', strtotime($app['appointment_date'])); ?></td>
                                <td><?php echo date('g:i A', strtotime($app['appointment_time'])); ?></td>
                                <td><?php echo htmlspecialchars($app['client_name']); ?></td>
                                <td><?php echo htmlspecialchars($app['service_name']); ?></td>
                                <td><?php echo $app['stylist_name'] ? htmlspecialchars($app['stylist_name']) : 'Not Assigned'; ?></td>
                                <td><span class="btn <?php echo $status_class; ?> btn-sm"><?php echo ucfirst($app['status']); ?></span></td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="appointment_id" value="<?php echo $app['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" style="padding: 5px; border-radius: 4px;">
                                                <option value="">Change Status</option>
                                                <option value="confirmed">Confirmed</option>
                                                <option value="completed">Completed</option>
                                                <option value="cancelled">Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_appointment_status" value="1">
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php elseif ($dashboard_page == 'clients'): ?>
                <div id="admin-clients" class="dashboard-page">
                    <div class="dashboard-header">
                        <h3>Client Management</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Last Visit</th>
                                <th>Total Visits</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $clients_sql = "SELECT u.*, 
                                MAX(a.appointment_date) as last_visit,
                                COUNT(a.id) as total_visits
                                FROM users u
                                LEFT JOIN appointments a ON u.id = a.user_id AND a.status = 'completed'
                                WHERE u.role = 'user'
                                GROUP BY u.id
                                ORDER BY u.first_name, u.last_name";
                            $clients = $conn->query($clients_sql);
                            
                            while ($client = $clients->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($client['email']); ?></td>
                                <td><?php echo htmlspecialchars($client['phone']); ?></td>
                                <td><?php echo $client['last_visit'] ? date('F j, Y', strtotime($client['last_visit'])) : 'Never'; ?></td>
                                <td><?php echo $client['total_visits']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php elseif ($dashboard_page == 'inventory'): ?>
                <div id="admin-inventory" class="dashboard-page">
                    <div class="dashboard-header">
                        <h3>Inventory Management</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Reorder Level</th>
                                <th>Supplier</th>
                                <th>Cost</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $inventory_sql = "SELECT * FROM inventory ORDER BY product_name";
                            $inventory = $conn->query($inventory_sql);
                            
                            while ($item = $inventory->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                <td><?php echo $item['current_stock']; ?></td>
                                <td><?php echo $item['reorder_level']; ?></td>
                                <td><?php echo htmlspecialchars($item['supplier']); ?></td>
                                <td>$<?php echo number_format($item['cost'], 2); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="quantity" value="<?php echo $item['reorder_level'] * 2; ?>">
                                            <button type="submit" name="reorder_item" class="btn btn-primary btn-sm">Reorder</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php elseif ($dashboard_page == 'reports'): ?>
                <div id="admin-reports" class="dashboard-page">
                    <h3>Reports & Analytics</h3>
                    
                    <div class="mt-20">
                        <h4>Sales Summary (This Month)</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>Service Category</th>
                                    <th>Number of Services</th>
                                    <th>Total Revenue</th>
                                    <th>% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $month_start = date('Y-m-01');
                                $reports_sql = "SELECT s.category, 
                                    COUNT(a.id) as service_count,
                                    COALESCE(SUM(p.amount), 0) as total_revenue
                                    FROM appointments a
                                    JOIN services s ON a.service_id = s.id
                                    LEFT JOIN payments p ON a.id = p.appointment_id
                                    WHERE a.appointment_date >= '$month_start' AND a.status = 'completed'
                                    GROUP BY s.category";
                                $reports = $conn->query($reports_sql);
                                
                                $total_revenue = 0;
                                $total_services = 0;
                                $data = [];
                                
                                while ($row = $reports->fetch_assoc()) {
                                    $data[] = $row;
                                    $total_revenue += $row['total_revenue'];
                                    $total_services += $row['service_count'];
                                }
                                
                                foreach ($data as $row):
                                    $percentage = $total_revenue > 0 ? ($row['total_revenue'] / $total_revenue * 100) : 0;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td><?php echo $row['service_count']; ?></td>
                                    <td>$<?php echo number_format($row['total_revenue'], 2); ?></td>
                                    <td><?php echo number_format($percentage, 1); ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td><strong>Total</strong></td>
                                    <td><strong><?php echo $total_services; ?></strong></td>
                                    <td><strong>$<?php echo number_format($total_revenue, 2); ?></strong></td>
                                    <td><strong>100%</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer>
        <div class="container footer-content">
            <div class="footer-section">
                <h3>Elegance Salon</h3>
                <p>Providing premium beauty and wellness services with a touch of luxury and elegance.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="index.php?page=home">Home</a>
                <a href="index.php?page=services">Services</a>
                <a href="index.php?page=appointments">Appointments</a>
                <a href="index.php?page=contact">Contact</a>
            </div>
            <div class="footer-section">
                <h3>Contact Info</h3>
                <p><i class="fas fa-map-marker-alt"></i> 123 Beauty Street, Glamour City, GC 12345</p>
                <p><i class="fas fa-phone"></i> (555) 123-4567</p>
                <p><i class="fas fa-envelope"></i> info@elegancesalon.com</p>
            </div>
            <div class="footer-section">
                <h3>Business Hours</h3>
                <p>Monday - Friday: 9:00 AM - 7:00 PM</p>
                <p>Saturday: 9:00 AM - 5:00 PM</p>
                <p>Sunday: 10:00 AM - 4:00 PM</p>
            </div>
        </div>
        <div class="container footer-bottom">
            <p>&copy; 2025 Elegance Salon. All rights reserved.</p>
        </div>
    </footer>
    <?php
require_once 'config.php';

    if (isset($_POST['action']) && $_POST['action'] == 'get_available_slots') {
        $date = sanitize_input($_POST['date']);
        $staff_id = !empty($_POST['staff_id']) ? sanitize_input($_POST['staff_id']) : NULL;
        
        $all_slots = [
            '09:00:00', '10:00:00', '11:00:00', '12:00:00',
            '13:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00'
        ];
        
        $booked_sql = "SELECT appointment_time FROM appointments 
                       WHERE appointment_date = '$date' 
                       AND status != 'cancelled'";
        
        if ($staff_id) {
            $booked_sql .= " AND staff_id = $staff_id";
        }
        
        $booked_result = $conn->query($booked_sql);
        $booked_times = [];
        
        while ($row = $booked_result->fetch_assoc()) {
            $booked_times[] = $row['appointment_time'];
        }
        
        $available_slots = [];
        foreach ($all_slots as $slot) {
            if (!in_array($slot, $booked_times)) {
                $available_slots[] = [
                    'value' => $slot,
                    'label' => date('g:i A', strtotime($slot))
                ];
            }
        }
        
        echo json_encode([
            'success' => true,
            'slots' => $available_slots
        ]);
        exit();
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'delete_client' && is_admin()) {
        $client_id = sanitize_input($_POST['client_id']);
        
        $sql = "DELETE FROM users WHERE id = $client_id AND role = 'user'";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Client deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete client']);
        }
        exit();
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'add_staff' && is_admin()) {
        $first_name = sanitize_input($_POST['first_name']);
        $last_name = sanitize_input($_POST['last_name']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $position = sanitize_input($_POST['position']);
        $schedule = sanitize_input($_POST['schedule']);
        $password = password_hash('staff123', PASSWORD_DEFAULT);
        
        $check_sql = "SELECT id FROM users WHERE email = '$email'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit();
        }
        
        $user_sql = "INSERT INTO users (first_name, last_name, email, phone, password, role) 
                     VALUES ('$first_name', '$last_name', '$email', '$phone', '$password', 'staff')";
        
        if ($conn->query($user_sql)) {
            $user_id = $conn->insert_id;
            
            $staff_sql = "INSERT INTO staff (user_id, position, schedule) 
                         VALUES ($user_id, '$position', '$schedule')";
            
            if ($conn->query($staff_sql)) {
                echo json_encode(['success' => true, 'message' => 'Staff member added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add staff record']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create user account']);
        }
        exit();
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'add_inventory' && is_admin()) {
        $product_name = sanitize_input($_POST['product_name']);
        $category = sanitize_input($_POST['category']);
        $current_stock = sanitize_input($_POST['current_stock']);
        $reorder_level = sanitize_input($_POST['reorder_level']);
        $supplier = sanitize_input($_POST['supplier']);
        $cost = sanitize_input($_POST['cost']);
        
        $sql = "INSERT INTO inventory (product_name, category, current_stock, reorder_level, supplier, cost) 
                VALUES ('$product_name', '$category', $current_stock, $reorder_level, '$supplier', $cost)";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Inventory item added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add inventory item']);
        }
        exit();
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'update_stock' && is_admin()) {
        $item_id = sanitize_input($_POST['item_id']);
        $quantity = sanitize_input($_POST['quantity']);
        $operation = sanitize_input($_POST['operation']);
        
        if ($operation == 'add') {
            $sql = "UPDATE inventory SET current_stock = current_stock + $quantity WHERE id = $item_id";
        } else {
            $sql = "UPDATE inventory SET current_stock = current_stock - $quantity WHERE id = $item_id";
        }
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Stock updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update stock']);
        }
        exit();
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'get_appointment') {
        $appointment_id = sanitize_input($_POST['appointment_id']);
        
        $sql = "SELECT a.*, 
                CONCAT(u.first_name, ' ', u.last_name) as client_name,
                u.email, u.phone,
                s.name as service_name, s.price_min, s.price_max,
                CONCAT(su.first_name, ' ', su.last_name) as stylist_name
                FROM appointments a
                JOIN users u ON a.user_id = u.id
                JOIN services s ON a.service_id = s.id
                LEFT JOIN staff st ON a.staff_id = st.id
                LEFT JOIN users su ON st.user_id = su.id
                WHERE a.id = $appointment_id";
        
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $appointment = $result->fetch_assoc();
            echo json_encode(['success' => true, 'appointment' => $appointment]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Appointment not found']);
        }
        exit();
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'mark_message_read' && is_admin()) {
        $message_id = sanitize_input($_POST['message_id']);
        
        $sql = "UPDATE contact_messages SET status = 'read' WHERE id = $message_id";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Message marked as read']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update message']);
        }
        exit();
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'get_stats' && is_admin()) {
        $today = date('Y-m-d');
        $week_start = date('Y-m-d', strtotime('monday this week'));
        $month_start = date('Y-m-01');
        
        $stats = [];
        
        $today_sql = "SELECT COUNT(*) as count FROM appointments 
                      WHERE appointment_date = '$today' AND status != 'cancelled'";
        $result = $conn->query($today_sql);
        $stats['today_appointments'] = $result->fetch_assoc()['count'];
        
        $week_sql = "SELECT COALESCE(SUM(amount), 0) as revenue FROM payments 
                     WHERE DATE(transaction_date) >= '$week_start' AND payment_status = 'paid'";
        $result = $conn->query($week_sql);
        $stats['week_revenue'] = $result->fetch_assoc()['revenue'];
        
        $month_sql = "SELECT COALESCE(SUM(amount), 0) as revenue FROM payments 
                      WHERE DATE(transaction_date) >= '$month_start' AND payment_status = 'paid'";
        $result = $conn->query($month_sql);
        $stats['month_revenue'] = $result->fetch_assoc()['revenue'];
        
        $clients_sql = "SELECT COUNT(*) as count FROM users WHERE role = 'user'";
        $result = $conn->query($clients_sql);
        $stats['total_clients'] = $result->fetch_assoc()['count'];
        
        $stock_sql = "SELECT COUNT(*) as count FROM inventory WHERE current_stock < reorder_level";
        $result = $conn->query($stock_sql);
        $stats['low_stock'] = $result->fetch_assoc()['count'];
        
        $pending_sql = "SELECT COUNT(*) as count FROM appointments 
                        WHERE status = 'pending' AND appointment_date >= '$today'";
        $result = $conn->query($pending_sql);
        $stats['pending_appointments'] = $result->fetch_assoc()['count'];
        
        echo json_encode(['success' => true, 'stats' => $stats]);
        exit();
    }

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
    if (isset($_GET['action']) && $_GET['action'] == 'search_clients') {
        $search = sanitize_input($_GET['search']);
        
        $sql = "SELECT id, CONCAT(first_name, ' ', last_name) as name, email, phone 
                FROM users 
                WHERE role = 'user' 
                AND (first_name LIKE '%$search%' 
                     OR last_name LIKE '%$search%' 
                     OR email LIKE '%$search%'
                     OR phone LIKE '%$search%')
                LIMIT 10";
        
        $result = $conn->query($sql);
        $clients = [];
        
        while ($row = $result->fetch_assoc()) {
            $clients[] = $row;
        }
        
        echo json_encode(['success' => true, 'clients' => $clients]);
        exit();
    }
    
    if (isset($_GET['action']) && $_GET['action'] == 'unread_messages' && is_admin()) {
        $sql = "SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'";
        $result = $conn->query($sql);
        $count = $result->fetch_assoc()['count'];
        
        echo json_encode(['success' => true, 'count' => $count]);
        exit();
    }
}
?>
</body>
</html>
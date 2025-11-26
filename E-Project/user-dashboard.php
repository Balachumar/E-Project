<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $address, $user_id]);
        $_SESSION['user_name'] = $name;
        $success = "Profile updated successfully!";
    } catch (PDOException $e) {
        $error = "Failed to update profile: " . $e->getMessage();
    }
}

// Fetch user data
$user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$user_id]);
$user_data = $user->fetch();

// Fetch user statistics
$upcoming_appointments = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = ? AND status = 'confirmed' AND appointment_date >= CURDATE()");
$upcoming_appointments->execute([$user_id]);
$upcoming_count = $upcoming_appointments->fetchColumn();

$past_appointments = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = ? AND (status = 'completed' OR appointment_date < CURDATE())");
$past_appointments->execute([$user_id]);
$past_count = $past_appointments->fetchColumn();

$total_spent = $pdo->prepare("SELECT SUM(p.amount) FROM payments p JOIN appointments a ON p.appointment_id = a.id WHERE a.user_id = ?");
$total_spent->execute([$user_id]);
$total_amount = $total_spent->fetchColumn();

// Fetch recent appointments
$recent_appointments = $pdo->prepare("
    SELECT a.*, s.name as service_name, st.name as stylist_name 
    FROM appointments a 
    JOIN services s ON a.service_id = s.id 
    JOIN staff st ON a.stylist_id = st.id 
    WHERE a.user_id = ? 
    ORDER BY a.appointment_date DESC, a.appointment_time DESC 
    LIMIT 5
");
$recent_appointments->execute([$user_id]);
$appointments = $recent_appointments->fetchAll();

// Fetch all user appointments
$user_appointments = $pdo->prepare("
    SELECT a.*, s.name as service_name, st.name as stylist_name 
    FROM appointments a 
    JOIN services s ON a.service_id = s.id 
    JOIN staff st ON a.stylist_id = st.id 
    WHERE a.user_id = ? 
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$user_appointments->execute([$user_id]);
$all_appointments = $user_appointments->fetchAll();

// Fetch payment history
$payments = $pdo->prepare("
    SELECT p.*, a.appointment_date, s.name as service_name 
    FROM payments p 
    JOIN appointments a ON p.appointment_id = a.id 
    JOIN services s ON a.service_id = s.id 
    WHERE a.user_id = ? 
    ORDER BY p.payment_date DESC
");
$payments->execute([$user_id]);
$payment_history = $payments->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Elegance Salon</title>
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
                <span>Welcome, <?php echo $_SESSION['user_name']; ?></span>
                <a href="user-dashboard.php" class="btn btn-outline">My Dashboard</a>
                <a href="logout.php" class="btn btn-primary">Logout</a>
            </div>
        </div>
    </header>

    <section class="page">
        <div class="dashboard">
            <div class="sidebar">
                <ul class="sidebar-menu">
                    <li><a href="#" class="dashboard-link active" data-dashboard="user-overview"><i class="fas fa-tachometer-alt"></i> Overview</a></li>
                    <li><a href="#" class="dashboard-link" data-dashboard="user-appointments"><i class="fas fa-calendar-check"></i> My Appointments</a></li>
                    <li><a href="#" class="dashboard-link" data-dashboard="user-profile"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="#" class="dashboard-link" data-dashboard="user-payments"><i class="fas fa-credit-card"></i> Payments</a></li>
                    <li><a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
            <div class="dashboard-content">
                <div class="dashboard-header">
                    <h2>User Dashboard</h2>
                    <p>Welcome back, <?php echo $_SESSION['user_name']; ?>!</p>
                </div>
                
                <?php if ($success): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div id="user-overview" class="dashboard-page active">
                    <h3>Overview</h3>
                    <div class="stats">
                        <div class="stat-card">
                            <i class="fas fa-calendar-check"></i>
                            <h3><?php echo $upcoming_count; ?></h3>
                            <p>Upcoming Appointments</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-history"></i>
                            <h3><?php echo $past_count; ?></h3>
                            <p>Past Appointments</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-star"></i>
                            <h3>4.8</h3>
                            <p>Average Rating</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-dollar-sign"></i>
                            <h3>$<?php echo number_format($total_amount ?: 0, 2); ?></h3>
                            <p>Total Spent</p>
                        </div>
                    </div>
                    
                    <h3>Recent Appointments</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Service</th>
                                <th>Stylist</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></td>
                                <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['stylist_name']); ?></td>
                                <td>
                                    <span class="btn btn-<?php 
                                        echo $appointment['status'] === 'completed' ? 'success' : 
                                             ($appointment['status'] === 'confirmed' ? 'warning' : 'danger'); 
                                    ?> btn-sm">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-primary btn-sm">Details</button>
                                        <?php if ($appointment['status'] === 'confirmed' && strtotime($appointment['appointment_date']) >= strtotime('today')): ?>
                                            <button class="btn btn-danger btn-sm">Cancel</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div id="user-appointments" class="dashboard-page">
                    <h3>My Appointments</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Service</th>
                                <th>Stylist</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_appointments as $appointment): ?>
                            <tr>
                                <td><?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></td>
                                <td><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></td>
                                <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['stylist_name']); ?></td>
                                <td>
                                    <span class="btn btn-<?php 
                                        echo $appointment['status'] === 'completed' ? 'success' : 
                                             ($appointment['status'] === 'confirmed' ? 'warning' : 'danger'); 
                                    ?> btn-sm">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-primary btn-sm">Details</button>
                                        <?php if ($appointment['status'] === 'confirmed' && strtotime($appointment['appointment_date']) >= strtotime('today')): ?>
                                            <button class="btn btn-danger btn-sm" onclick="cancelAppointment(<?php echo $appointment['id']; ?>)">Cancel</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div id="user-profile" class="dashboard-page">
                    <h3>My Profile</h3>
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="user-name">Full Name</label>
                                <input type="text" id="user-name" name="name" class="form-control" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="user-phone">Phone</label>
                                <input type="text" id="user-phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user_data['phone']); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="user-email">Email</label>
                            <input type="email" id="user-email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="user-address">Address</label>
                            <textarea id="user-address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
                
                <div id="user-payments" class="dashboard-page">
                    <h3>Payment History</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Service</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Invoice</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payment_history as $payment): ?>
                            <tr>
                                <td><?php echo date('F j, Y', strtotime($payment['payment_date'])); ?></td>
                                <td><?php echo htmlspecialchars($payment['service_name']); ?></td>
                                <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                <td><span class="btn btn-success btn-sm"><?php echo ucfirst($payment['status']); ?></span></td>
                                <td><button class="btn btn-primary btn-sm">Download</button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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
                <a href="index.php" class="nav-link">Home</a>
                <a href="services.php" class="nav-link">Services</a>
                <a href="appointments.php" class="nav-link">Appointments</a>
                <a href="contact.php" class="nav-link">Contact</a>
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
            <p>&copy; <?php echo date('Y'); ?> Elegance Salon. All rights reserved.</p>
        </div>
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

        function cancelAppointment(appointmentId) {
            if (confirm('Are you sure you want to cancel this appointment?')) {
                // This would typically make an AJAX call to cancel the appointment
                alert('Appointment cancellation feature would be implemented here.');
            }
        }
    </script>
</body>
</html>
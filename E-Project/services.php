<?php
session_start();
require_once 'config/database.php';

// Fetch all services from database
$services = $pdo->query("SELECT * FROM services")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services - Elegance Salon</title>
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
                    <li><a href="services.php" class="nav-link active">Services</a></li>
                    <li><a href="appointments.php" class="nav-link">Appointments</a></li>
                    <li><a href="contact.php" class="nav-link">Contact</a></li>
                </ul>
            </nav>
            <div class="auth-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span>Welcome, <?php echo $_SESSION['user_name']; ?></span>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="admin-dashboard.php" class="btn btn-outline">Admin Dashboard</a>
                    <?php else: ?>
                        <a href="user-dashboard.php" class="btn btn-outline">My Dashboard</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-primary">Logout</a>
                <?php else: ?>
                    <a href="index.php" class="btn btn-outline">Login</a>
                    <a href="index.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <section class="page">
        <div class="container">
            <h2 class="text-center mb-20">Our Services</h2>
            <div class="services">
                <?php foreach ($services as $service): ?>
                <div class="service-card">
                    <div class="service-img">
                        <?php
                        // Different icons for different services
                        $icons = [
                            'Haircut' => 'fas fa-cut',
                            'Coloring' => 'fas fa-tint',
                            'Manicure' => 'fas fa-hand-sparkles',
                            'Pedicure' => 'fas fa-shoe-prints',
                            'Facial' => 'fas fa-spa',
                            'Makeup' => 'fas fa-palette'
                        ];
                        
                        $icon = 'fas fa-spa'; // default
                        foreach ($icons as $key => $value) {
                            if (stripos($service['name'], $key) !== false) {
                                $icon = $value;
                                break;
                            }
                        }
                        ?>
                        <i class="<?php echo $icon; ?>"></i>
                    </div>
                    <div class="service-content">
                        <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                        <p><?php echo htmlspecialchars($service['description']); ?></p>
                        <p class="mt-20"><strong>Price: $<?php echo number_format($service['price'], 2); ?></strong></p>
                        <p><strong>Duration: <?php echo $service['duration']; ?> minutes</strong></p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="appointments.php" class="btn btn-primary mt-20">Book Now</a>
                        <?php else: ?>
                            <button onclick="openModal('loginModal')" class="btn btn-primary mt-20">Book Now</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
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

    <!-- Login Modal (same as index.php) -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('loginModal')">&times;</span>
            <h2>Login</h2>
            <form method="POST" action="index.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modals = document.getElementsByClassName('modal');
            for (let modal of modals) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>
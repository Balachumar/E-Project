<?php
session_start();
require_once 'config/database.php';

// Handle login/register
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            
            if ($user['role'] === 'admin') {
                header('Location: admin-dashboard.php');
            } else {
                header('Location: user-dashboard.php');
            }
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    }
    
    if (isset($_POST['register'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $phone = $_POST['phone'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, 'client')");
            $stmt->execute([$name, $email, $password, $phone]);
            
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'client';
            
            header('Location: user-dashboard.php');
            exit();
        } catch (PDOException $e) {
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elegance Salon</title>
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
                    <li><a href="index.php" class="nav-link active">Home</a></li>
                    <li><a href="services.php" class="nav-link">Services</a></li>
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
                    <button class="btn btn-outline" onclick="openModal('loginModal')">Login</button>
                    <button class="btn btn-primary" onclick="openModal('registerModal')">Register</button>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('loginModal')">&times;</span>
            <h2>Login</h2>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
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

    <!-- Register Modal -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('registerModal')">&times;</span>
            <h2>Register</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="reg_name">Full Name</label>
                    <input type="text" id="reg_name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="reg_email">Email</label>
                    <input type="email" id="reg_email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="reg_password">Password</label>
                    <input type="password" id="reg_password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="reg_phone">Phone</label>
                    <input type="text" id="reg_phone" name="phone" class="form-control" required>
                </div>
                <button type="submit" name="register" class="btn btn-primary">Register</button>
            </form>
        </div>
    </div>

    <main class="container">
        <section class="page">
            <div class="hero">
                <h2>Welcome to Elegance Salon</h2>
                <p>Experience luxury and style with our premium beauty and wellness services. Our expert team is dedicated to making you look and feel your best.</p>
                <a href="appointments.php" class="btn btn-primary">Book an Appointment</a>
            </div>
            
            <div class="services">
                <div class="service-card">
                    <div class="service-img">
                        <i class="fas fa-cut"></i>
                    </div>
                    <div class="service-content">
                        <h3>Hair Styling</h3>
                        <p>From classic cuts to modern styles, our expert stylists will create the perfect look for you.</p>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-img">
                        <i class="fas fa-hand-sparkles"></i>
                    </div>
                    <div class="service-content">
                        <h3>Manicure & Pedicure</h3>
                        <p>Pamper your hands and feet with our luxurious nail care services.</p>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-img">
                        <i class="fas fa-spa"></i>
                    </div>
                    <div class="service-content">
                        <h3>Facials</h3>
                        <p>Rejuvenate your skin with our specialized facial treatments tailored to your needs.</p>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-img">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div class="service-content">
                        <h3>Makeup</h3>
                        <p>Professional makeup services for any occasion, from everyday looks to special events.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

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
            <p>&copy; 2023 Elegance Salon. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modal when clicking outside
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
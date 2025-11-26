<?php
session_start();
require_once 'config/database.php';

$success = '';
$error = '';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        $success = "Thank you for your message! We'll get back to you soon.";
        
        // Clear form fields
        $_POST = array();
    } catch (PDOException $e) {
        $error = "Failed to send message: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Elegance Salon</title>
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
                    <li><a href="contact.php" class="nav-link active">Contact</a></li>
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
            <h2 class="text-center mb-20">Contact Us</h2>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group">
                    <h3>Get In Touch</h3>
                    <p>We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
                    
                    <div class="mt-20">
                        <p><i class="fas fa-map-marker-alt"></i> 123 Beauty Street, Glamour City, GC 12345</p>
                        <p><i class="fas fa-phone"></i> (555) 123-4567</p>
                        <p><i class="fas fa-envelope"></i> info@elegancesalon.com</p>
                    </div>
                    
                    <div class="mt-20">
                        <h4>Business Hours</h4>
                        <p>Monday - Friday: 9:00 AM - 7:00 PM</p>
                        <p>Saturday: 9:00 AM - 5:00 PM</p>
                        <p>Sunday: 10:00 AM - 4:00 PM</p>
                    </div>
                </div>
                <div class="form-group">
                    <form method="POST" id="contactForm">
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" class="form-control" value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" class="form-control" rows="5" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
                    </form>
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
</body>
</html>
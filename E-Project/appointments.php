<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$success = '';
$error = '';

// Handle appointment booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appointment'])) {
    $service_id = $_POST['service'];
    $stylist_id = $_POST['stylist'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $notes = $_POST['notes'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO appointments (user_id, service_id, stylist_id, appointment_date, appointment_time, notes, status) VALUES (?, ?, ?, ?, ?, ?, 'confirmed')");
        $stmt->execute([$_SESSION['user_id'], $service_id, $stylist_id, $date, $time, $notes]);
        $success = "Appointment booked successfully!";
    } catch (PDOException $e) {
        $error = "Failed to book appointment: " . $e->getMessage();
    }
}

// Fetch services and stylists
$services = $pdo->query("SELECT * FROM services")->fetchAll();
$stylists = $pdo->query("SELECT * FROM staff WHERE role = 'stylist'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Elegance Salon</title>
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
                    <li><a href="appointments.php" class="nav-link active">Appointments</a></li>
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
        <h2 class="text-center mb-20">Book an Appointment</h2>
        
        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="service">Select Service</label>
                    <select id="service" name="service" class="form-control" required>
                        <option value="">Choose a service</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?php echo $service['id']; ?>"><?php echo $service['name']; ?> - $<?php echo $service['price']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="stylist">Preferred Stylist</label>
                    <select id="stylist" name="stylist" class="form-control" required>
                        <option value="">Any available stylist</option>
                        <?php foreach ($stylists as $stylist): ?>
                            <option value="<?php echo $stylist['id']; ?>"><?php echo $stylist['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="date">Preferred Date</label>
                    <input type="date" id="date" name="date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label for="time">Preferred Time</label>
                    <select id="time" name="time" class="form-control" required>
                        <option value="">Select a time</option>
                        <option value="09:00">9:00 AM</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="12:00">12:00 PM</option>
                        <option value="13:00">1:00 PM</option>
                        <option value="14:00">2:00 PM</option>
                        <option value="15:00">3:00 PM</option>
                        <option value="16:00">4:00 PM</option>
                        <option value="17:00">5:00 PM</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="notes">Additional Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="4" placeholder="Any special requests or requirements..."></textarea>
            </div>
            <button type="submit" name="book_appointment" class="btn btn-primary">Book Appointment</button>
        </form>
        
        <div class="mt-20">
            <h3>Appointment Calendar</h3>
            <table class="calendar">
                <thead>
                    <tr>
                        <th>Sun</th>
                        <th>Mon</th>
                        <th>Tue</th>
                        <th>Wed</th>
                        <th>Thu</th>
                        <th>Fri</th>
                        <th>Sat</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td>1</td>
                        <td>2</td>
                        <td>3</td>
                        <td>4</td>
                        <td>5</td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>7</td>
                        <td>8</td>
                        <td class="today">9</td>
                        <td>10</td>
                        <td>11</td>
                        <td>12</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <footer>
        
    </footer>
</body>
</html>
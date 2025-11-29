<link rel="stylesheet" href="style.css">
<section class="page">
    <div class="hero">
        <h2>Welcome to Elegance Salon</h2>
        <p>Experience luxury and style with our premium beauty and wellness services. Our expert team is dedicated to making you look and feel your best.</p>
        <a href="?page=appointments" class="btn btn-primary">Book an Appointment</a>
    </div>
    
    <div class="services">
        <?php
        $sql = "SELECT * FROM services LIMIT 4";
        $result = $conn->query($sql);
        
        $icons = [
            'Hair Services' => 'fa-cut',
            'Nail Services' => 'fa-hand-sparkles',
            'Skin Care' => 'fa-spa',
            'Makeup' => 'fa-palette'
        ];
        
        while ($service = $result->fetch_assoc()):
            $icon = isset($icons[$service['category']]) ? $icons[$service['category']] : 'fa-star';
        ?>
        <div class="service-card">
            <div class="service-img">
                <i class="fas <?php echo $icon; ?>"></i>
            </div>
            <div class="service-content">
                <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                <p><?php echo htmlspecialchars($service['description']); ?></p>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</section>
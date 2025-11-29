CREATE DATABASE elegance_salon;
USE elegance_salon;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    address TEXT,
    role ENUM('user', 'admin', 'staff') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Staff table
CREATE TABLE IF NOT EXISTS staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    position VARCHAR(100),
    schedule VARCHAR(255),
    bio TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Services table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    duration INT, -- in minutes
    price_min DECIMAL(10,2),
    price_max DECIMAL(10,2),
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Appointments table
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    staff_id INT,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE SET NULL
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
);

-- Inventory table
CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    current_stock INT DEFAULT 0,
    reorder_level INT DEFAULT 10,
    supplier VARCHAR(100),
    cost DECIMAL(10,2),
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO services (name, description, duration, price_min, price_max, category) VALUES
('Haircut & Styling', 'Professional haircuts and styling for men and women', 60, 35.00, 75.00, 'Hair Services'),
('Hair Coloring', 'Custom hair coloring services including highlights and balayage', 120, 60.00, 150.00, 'Hair Services'),
('Manicure', 'Classic and gel manicures with a wide range of polish options', 45, 25.00, 45.00, 'Nail Services'),
('Pedicure', 'Relaxing pedicures to keep your feet looking and feeling great', 60, 35.00, 60.00, 'Nail Services'),
('Facial', 'Customized facial treatments for all skin types', 75, 50.00, 100.00, 'Skin Care'),
('Makeup Application', 'Professional makeup for events, photoshoots, or special occasions', 60, 45.00, 90.00, 'Makeup');

INSERT INTO users (first_name, last_name, email, phone, password, role) VALUES
('Admin', 'User', 'admin@elegancesalon.com', '(555) 123-4567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

INSERT INTO inventory (product_name, category, current_stock, reorder_level, supplier, cost) VALUES
('Shampoo', 'Hair Care', 5, 10, 'Beauty Supplies Inc.', 8.50),
('Conditioner', 'Hair Care', 8, 10, 'Beauty Supplies Inc.', 9.25),
('Nail Polish', 'Nail Care', 25, 15, 'Nail Pro Distributors', 4.75),
('Nail Polish Remover', 'Nail Care', 3, 5, 'Nail Pro Distributors', 6.00);
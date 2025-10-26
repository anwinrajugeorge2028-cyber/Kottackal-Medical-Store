<?php
include 'config.php';

// Create users table
$users_table = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('admin','user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $users_table)) {
    die("Error creating users table: " . mysqli_error($conn));
}

// Create medicines table
$medicines_table = "CREATE TABLE IF NOT EXISTS medicines (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT(11) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $medicines_table)) {
    die("Error creating medicines table: " . mysqli_error($conn));
}

// Insert default admin user if not exists
$check_admin = "SELECT id FROM users WHERE username='admin'";
$result = mysqli_query($conn, $check_admin);

if (mysqli_num_rows($result) == 0) {
    $hashed_password = password_hash('password', PASSWORD_DEFAULT);
    $insert_admin = "INSERT INTO users (username, password, user_type) VALUES ('admin', '$hashed_password', 'admin')";
    mysqli_query($conn, $insert_admin);
}

// Insert sample medicines if empty
$check_medicines = "SELECT id FROM medicines";
$result = mysqli_query($conn, $check_medicines);

if (mysqli_num_rows($result) == 0) {
    $sample_medicines = [
        "INSERT INTO medicines (name, price, quantity, description) VALUES ('Paracetamol', 5.00, 100, 'Pain reliever')",
        "INSERT INTO medicines (name, price, quantity, description) VALUES ('Amoxicillin', 15.50, 50, 'Antibiotic')",
        "INSERT INTO medicines (name, price, quantity, description) VALUES ('Vitamin C', 8.75, 200, 'Immune booster')",
        "INSERT INTO medicines (name, price, quantity, description) VALUES ('Aspirin', 12.00, 5, 'Pain reliever - LOW STOCK')"
    ];
    
    foreach ($sample_medicines as $sql) {
        mysqli_query($conn, $sql);
    }
}
?>
<?php
// setup.php
$host = $_SERVER['DB_HOST'] ?? getenv('DB_HOST') ?: "127.0.0.1"; 
$port = $_SERVER['DB_PORT'] ?? getenv('DB_PORT') ?: "3306";
$db_name = $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: "mass_revival";
$username = $_SERVER['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: "root"; 
$password = $_SERVER['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: ""; 

echo "<h2>Laravel Cloud Database Setup</h2>";

try {
    $conn = new PDO("mysql:host=" . $host . ";port=" . $port . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color:green;'>✅ Successfully connected to the Laravel Cloud database!</p>";
    
    // Create the table automatically
    $sql = "CREATE TABLE IF NOT EXISTS registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        surname VARCHAR(100) NOT NULL,
        other_names VARCHAR(150) NOT NULL,
        gender VARCHAR(20) NOT NULL,
        phone VARCHAR(30) NOT NULL,
        email VARCHAR(150) NOT NULL,
        location VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    echo "<p style='color:green;'>✅ 'registrations' table created successfully!</p>";
    echo "<p><b>You are officially ready for launch! You can now delete this setup.php file.</b></p>";
    
} catch(PDOException $e) {
    echo "<p style='color:red;'>❌ Connection failed: " . $e->getMessage() . "</p>";
    echo "<h3>Debug Info (What Laravel Cloud is giving us):</h3>";
    echo "<ul>";
    echo "<li><b>Host:</b> " . ($host === '127.0.0.1' ? "127.0.0.1 (Falling back to local, Laravel Cloud isn't passing the DB_HOST variable yet)" : $host) . "</li>";
    echo "<li><b>Port:</b> " . $port . "</li>";
    echo "<li><b>DB Name:</b> " . $db_name . "</li>";
    echo "<li><b>Username:</b> " . $username . "</li>";
    echo "</ul>";
}
?>

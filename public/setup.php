// ==========================================
// DATABASE CONFIGURATION (PostgreSQL)
// ==========================================
$host = $_SERVER['DB_HOST'] ?? getenv('DB_HOST') ?: "ep-old-mountain-a5t9f9ew.aws-us-east-2.pg.laravel.cloud"; 
$port = $_SERVER['DB_PORT'] ?? getenv('DB_PORT') ?: "5432";
$db_name = $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: "laravel"; // Defaults to laravel
$username = $_SERVER['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: "laravel"; 
$password = $_SERVER['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: "npg_Dp4SxliKvCq5"; 

echo "<h2>Laravel Cloud Database Setup</h2>";

try {
    // THE MAGIC FIX: We changed 'mysql:' to 'pgsql:' right here 👇
    $conn = new PDO("pgsql:host=" . $host . ";port=" . $port . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color:green;'>✅ Successfully connected to the Laravel Cloud PostgreSQL database!</p>";
    
    // Create the table automatically
    $sql = "CREATE TABLE IF NOT EXISTS registrations (
        id SERIAL PRIMARY KEY,
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
    
} catch(PDOException $e) {
    echo "<p style='color:red;'>❌ Connection failed: " . $e->getMessage() . "</p>";
}

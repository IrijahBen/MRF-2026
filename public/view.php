<?php
session_start();

// ==========================================
// 1. CONFIGURATION
// ==========================================
// Change this password to something secure!
$admin_password = "revivaladmin2026"; 

// Use Laravel Cloud environment variables, fallback to local XAMPP
// ==========================================
// 1. CONFIGURATION
// ==========================================
$admin_password = "revivaladmin2026"; 

$host = $_SERVER['DB_HOST'] ?? getenv('DB_HOST') ?: "ep-old-mountain-a5t9f9ew.aws-us-east-2.pg.laravel.cloud"; 
$port = $_SERVER['DB_PORT'] ?? getenv('DB_PORT') ?: "5432";
$db_name = $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: "laravel"; 
$username = $_SERVER['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: "laravel"; 
$password = $_SERVER['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: "npg_Dp4SxliKvCq5"; 

// ... [Keep your Authentication Logic here] ...

// ==========================================
// 3. DATABASE CONNECTION
// ==========================================
try {
    $conn = new PDO("pgsql:host=" . $host . ";port=" . $port . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    die("Database connection failed: " . $exception->getMessage());
}

// ==========================================
// 2. AUTHENTICATION LOGIC
// ==========================================
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: view.php");
    exit();
}

if (isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['logged_in'] = true;
    } else {
        $login_error = "Incorrect password.";
    }
}

// If not logged in, show the login form and stop execution
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login | Mass Revival Fest</title>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
        <style>
            body { background-color: #1a0f0a; color: #fff; font-family: 'Roboto', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .login-box { background-color: #2a1a14; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); text-align: center; width: 100%; max-width: 400px; }
            input[type="password"] { width: 100%; padding: 15px; margin: 20px 0; border-radius: 8px; border: 1px solid #3d261d; background-color: #3d261d; color: white; box-sizing: border-box; }
            button { width: 100%; padding: 15px; background-color: #e63946; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; }
            button:hover { background-color: #c12734; }
            .error { color: #ff6b6b; margin-bottom: 15px; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>Admin Portal</h2>
            <p style="color: #d4bda8; font-size: 14px;">Enter password to view registrations</p>
            <?php if(isset($login_error)) echo "<div class='error'>$login_error</div>"; ?>
            <form method="POST" action="">
                <input type="password" name="password" placeholder="Enter Password" required>
                <button type="submit">Access Dashboard</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// ==========================================
// 3. DATABASE CONNECTION
// ==========================================
try {
    $conn = new PDO("mysql:host=" . $host . ";port=" . $port . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    die("Database connection failed: " . $exception->getMessage());
}

// ==========================================
// 4. DELETE LOGIC
// ==========================================
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $del_stmt = $conn->prepare("DELETE FROM registrations WHERE id = :id");
    $del_stmt->bindParam(':id', $delete_id, PDO::PARAM_INT);
    if ($del_stmt->execute()) {
        // Redirect back to clean the URL after deleting
        header("Location: view.php");
        exit();
    }
}

// ==========================================
// 5. CSV EXPORT LOGIC
// ==========================================
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=mass_revival_registrations_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // Add CSV Headers (Updated for new fields)
    fputcsv($output, array('ID', 'Surname', 'Other Names', 'Gender', 'Phone (WhatsApp)', 'Email', 'Location', 'Registration Date'));
    
    // Fetch Data
    $stmt = $conn->prepare("SELECT id, surname, other_names, gender, phone, email, location, created_at FROM registrations ORDER BY id DESC");
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// ==========================================
// 6. FETCH DATA FOR DASHBOARD
// ==========================================
$stmt = $conn->prepare("SELECT * FROM registrations ORDER BY created_at DESC");
$stmt->execute();
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_registered = count($registrations);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Mass Revival Fest</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #f4f4f9; color: #333; font-family: 'Roboto', sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .header h1 { margin: 0; color: #1a0f0a; font-size: 24px; }
        
        .stats-card { background-color: #e63946; color: white; padding: 15px 25px; border-radius: 8px; display: inline-block; font-weight: bold; margin-bottom: 20px;}
        
        .btn { padding: 10px 15px; border-radius: 6px; text-decoration: none; font-weight: 500; font-size: 14px; transition: 0.2s; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
        .btn-export { background-color: #25D366; color: white; }
        .btn-export:hover { background-color: #1ebc59; }
        .btn-logout { background-color: #333; color: white; margin-left: 10px; }
        .btn-logout:hover { background-color: #555; }
        
        /* New Delete Button Style */
        .btn-delete { background-color: #ff6b6b; color: white; padding: 6px 10px; border-radius: 4px; border: none; cursor: pointer; text-decoration: none; font-size: 13px; }
        .btn-delete:hover { background-color: #ff4757; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f8f9fa; color: #555; font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
        tr:hover { background-color: #fcfcfc; }
        
        .empty-state { text-align: center; padding: 50px; color: #888; }
        
        @media (max-width: 768px) {
            .header { flex-direction: column; align-items: flex-start; gap: 15px; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Mass Revival Fest '26 - Registrations</h1>
        <div>
            <a href="?export=csv" class="btn btn-export"><i class="fas fa-file-csv"></i> Export to CSV</a>
            <a href="?logout=true" class="btn btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="stats-card">
        Total Registered: <?php echo $total_registered; ?>
    </div>

    <?php if ($total_registered > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Surname</th>
                    <th>Other Names</th>
                    <th>Gender</th>
                    <th>WhatsApp</th>
                    <th>Location</th>
                    <th>Date Registered</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registrations as $index => $row): ?>
                    <tr>
                        <td><?php echo $total_registered - $index; ?></td>
                        <td><?php echo htmlspecialchars($row['surname']); ?></td>
                        <td><?php echo htmlspecialchars($row['other_names']); ?></td>
                        <td><?php echo htmlspecialchars($row['gender']); ?></td>
                        <td>
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $row['phone']); ?>" target="_blank" style="color: #25D366; text-decoration: none; font-weight: 500;">
                                <?php echo htmlspecialchars($row['phone']); ?>
                            </a>
                        </td>
                        <td><span style="background:#eee; padding:4px 8px; border-radius:4px; font-size:12px;"><?php echo htmlspecialchars($row['location']); ?></span></td>
                        <td><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
                        <td>
                            <!-- Delete Button -->
                            <a href="?delete_id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to permanently delete this registration?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox" style="font-size: 40px; margin-bottom: 15px; color: #ddd;"></i>
            <h3>No registrations yet.</h3>
            <p>Once people start filling out the form, their details will appear here.</p>
        </div>
    <?php endif; ?>

</div>

</body>
</html>

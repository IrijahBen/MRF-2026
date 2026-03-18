<?php
// register.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// ==========================================
// 1. DATABASE CONFIGURATION
// ==========================================
$host = "localhost";
$db_name = "mass_revival";
$username = "root"; 
$password = ""; 

// ==========================================
// 2. WHATSAPP API CONFIGURATION (e.g., UltraMsg)
// ==========================================
// You will get these when you create a free account on an API provider
$api_instance_id = "YOUR_INSTANCE_ID"; 
$api_token = "YOUR_API_TOKEN";
$channel_link = "https://whatsapp.com/channel/YOUR_LINK";

try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    echo json_encode(["result" => "error", "message" => "Database connection error"]);
    exit();
}

// Function to format the phone number (Assuming Nigerian numbers for the event)
function formatPhoneNumber($phone) {
    // Remove any non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // If it starts with 0, replace it with 234
    if (strpos($phone, '0') === 0) {
        $phone = '234' . substr($phone, 1);
    }
    return $phone;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Sanitize inputs
    $first_name = htmlspecialchars(strip_tags($_POST['first_name'] ?? ''));
    $last_name = htmlspecialchars(strip_tags($_POST['last_name'] ?? ''));
    $raw_phone = htmlspecialchars(strip_tags($_POST['phone'] ?? ''));
    $email = htmlspecialchars(strip_tags($_POST['email'] ?? ''));
    
    $location = htmlspecialchars(strip_tags($_POST['location'] ?? ''));
    if ($location === 'Other') {
        $location = htmlspecialchars(strip_tags($_POST['custom_location'] ?? ''));
    }

    if (empty($first_name) || empty($last_name) || empty($raw_phone) || empty($email) || empty($location)) {
        echo json_encode(["result" => "error", "message" => "All fields are required."]);
        exit();
    }

    // Format phone number for the API
    $formatted_phone = formatPhoneNumber($raw_phone);

    // Insert into database
    $query = "INSERT INTO registrations (first_name, last_name, phone, email, location) VALUES (:first_name, :last_name, :phone, :email, :location)";
    $stmt = $conn->prepare($query);

    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':phone', $formatted_phone); // Store formatted number
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':location', $location);

    if ($stmt->execute()) {
        
        // ==========================================
        // 3. SEND THE AUTOMATED WHATSAPP MESSAGE
        // ==========================================
        $message = "Shalom {$first_name}! 🔥\n\nYour registration for the *Mass Revival Fest 2026* is confirmed.\n\nDate: 20th March 2026\nVenue: Chapel Tarmac, UI.\n\nTo receive updates, prayer points, and instructions before the convergence, please join our official channel right now by clicking this link:\n{$channel_link}\n\nWe look forward to hosting you!";

        // Setup cURL for the WhatsApp API
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.ultramsg.com/{$api_instance_id}/messages/chat",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query(array(
                'token' => $api_token,
                'to' => $formatted_phone,
                'body' => $message
            )),
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        // We return success to the frontend regardless of whether the WA message succeeded, 
        // because the DB insert was successful.
        echo json_encode(["result" => "success", "message" => "Registration saved and WhatsApp message triggered."]);
        
    } else {
        echo json_encode(["result" => "error", "message" => "Could not save registration."]);
    }
} else {
    echo json_encode(["result" => "error", "message" => "Invalid request method."]);
}
?>
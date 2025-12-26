<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");

// 1. Database Connection
$host = "localhost";
$user = "sttnogxp_audiolibrary";      // Check your DB username
$pass = "WVDHf4DjJwr5Ak7DSHch";          // Check your DB password
$dbname = "sttnogxp_audiolibrary";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(["status" => "error", "message" => "DB Connection Failed"]));
}

$action = $_GET['action'] ?? '';

// 2. Router
switch($action) {
    case 'register': handleRegister($conn); break;
    case 'login': handleLogin($conn); break;
    case 'save_face': handleSaveFace($conn); break;
    case 'fetch_faces': handleFetchFaces($conn); break;
    default: echo json_encode(["status" => "error", "message" => "Invalid Action"]);
}

// 3. Functions

// --- Touch Register ---
function handleRegister($conn) {
    $fullname = $_POST['fullname'] ?? '';
    $password = $_POST['password'] ?? '';
    $vision_type = $_POST['vision_type'] ?? 'normal';

    if(empty($fullname) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Required fields missing."]);
        return;
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE fullname = ?");
    $stmt->execute([$fullname]);
    if($stmt->rowCount() > 0){
        echo json_encode(["status" => "error", "message" => "Username taken!"]);
        return;
    }

    $dummy_email = str_replace(' ', '', strtolower($fullname)) . time() . "@local.com";
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, vision_type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$fullname, $dummy_email, $hashed_password, $vision_type]);
        echo json_encode(["status" => "success"]);
    } catch(PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

// --- Touch Login ---
function handleLogin($conn) {
    $fullname = $_POST['fullname'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, fullname, password, vision_type FROM users WHERE fullname = ?");
    $stmt->execute([$fullname]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        echo json_encode([
            "status" => "success",
            "user_id" => $user['id'],
            "fullname" => $user['fullname'],
            "vision_type" => $user['vision_type']
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid Credentials"]);
    }
}

// --- Voice Register (Save Face) ---
function handleSaveFace($conn) {
    ini_set('memory_limit', '256M');
    $data = json_decode(file_get_contents("php://input"), true);
    
    $name = $data['fullname'] ?? '';
    $descriptor = $data['descriptor'] ?? null;
    $image_base64 = $data['image'] ?? null;
    $vision_type = $data['vision_type'] ?? 'normal';

    if(!$name || !$descriptor || !$image_base64) {
        echo json_encode(["status" => "error", "message" => "Data Missing"]);
        return;
    }

    // Save Image
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    $image_parts = explode(";base64,", $image_base64);
    $image_decoded = base64_decode($image_parts[1]);
    $filename = "user_" . time() . ".jpg";
    $file_path = $upload_dir . $filename;
    file_put_contents($file_path, $image_decoded);

    // Update DB (Check if user exists from Touch Register first)
    $descriptor_json = json_encode($descriptor);

    try {
        // Try to update existing user first
        $stmt = $conn->prepare("SELECT id FROM users WHERE fullname = ?");
        $stmt->execute([$name]);
        $existing = $stmt->fetch();

        if($existing) {
            $stmt = $conn->prepare("UPDATE users SET face_descriptor = ?, image_path = ? WHERE id = ?");
            $stmt->execute([$descriptor_json, $file_path, $existing['id']]);
        } else {
            // Create new voice user
            $dummy_email = str_replace(' ', '', strtolower($name)) . time() . "@voice.com";
            $dummy_pass = password_hash("voice123", PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, face_descriptor, image_path, vision_type) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $dummy_email, $dummy_pass, $descriptor_json, $file_path, $vision_type]);
        }
        echo json_encode(["status" => "success"]);
    } catch(PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

// --- Voice Login (Fetch Faces) ---
function handleFetchFaces($conn) {
    $stmt = $conn->prepare("SELECT fullname, face_descriptor, vision_type FROM users WHERE face_descriptor IS NOT NULL");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($users as &$user) { $user['face_descriptor'] = json_decode($user['face_descriptor']); }
    echo json_encode($users);
}
?>
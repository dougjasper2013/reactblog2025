<?php
header("Access-Control-Allow-Origin: *");  // Or specify your frontend domain
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once('../config/config.php');
require_once('../config/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { // handle preflight
    http_response_code(200);
    exit();
}

// Validate required fields from form-data
if (empty($_POST['title']) || empty($_POST['content']) || empty($_POST['author'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Error: Missing or empty required parameter']);
    exit();
}

// Sanitize input
$title   = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
$content = filter_var($_POST['content'], FILTER_SANITIZE_STRING);
$author  = filter_var($_POST['author'], FILTER_SANITIZE_STRING);

// Handle file upload (optional)
$imageName = null;
$uploadDir = __DIR__ . '/uploads/';

if (!empty($_FILES['image']['name'])) {
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = basename($_FILES['image']['name']);
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid file type. Allowed: jpg, jpeg, png, gif']);
        exit();
    }

    // Ensure uploads directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    // Generate unique filename to avoid collisions
    $newFileName = uniqid('img_', true) . '.' . $fileExtension;
    $destPath = $uploadDir . $newFileName;

    if (move_uploaded_file($fileTmpPath, $destPath)) {
        $imageName = $newFileName;
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Error uploading file']);
        exit();
    }
}

// Prepare SQL with imageName column
$stmt = $conn->prepare('INSERT INTO blog_posts (title, content, author, imageName) VALUES(?, ?, ?, ?)');
$stmt->bind_param('ssss', $title, $content, $author, $imageName);

// Execute statement
if ($stmt->execute()) {
    $id = $stmt->insert_id;
    http_response_code(201);
    echo json_encode([
        'message' => 'Post created successfully',
        'id' => $id,
        'imageName' => $imageName
    ]);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Error creating post: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

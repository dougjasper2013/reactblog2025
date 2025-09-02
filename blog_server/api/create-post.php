<?php

header("Access-Control-Allow-Origin: *");  // Or your frontend domain
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once('../config/config.php');
require_once('../config/database.php');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Validate required POST fields
if (!isset($_POST['title'], $_POST['content'], $_POST['author'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Missing required fields']);
    exit();
}

// Sanitize input
$title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
$content = filter_var($_POST['content'], FILTER_SANITIZE_STRING);
$author = filter_var($_POST['author'], FILTER_SANITIZE_STRING);

// Handle image upload if exists
$uploadDir = __DIR__ . "/uploads/";
$imageName = "placeholder_100.jpg"; // default

if (!empty($_FILES['image']['name'])) {
    $originalName = basename($_FILES['image']['name']);
    $targetFilePath = $uploadDir . $originalName;

    // Check if file already exists
    if (file_exists($targetFilePath)) {
        http_response_code(400);
        echo json_encode(['message' => 'File already exists: ' . $originalName]);
        exit();
    }

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
        http_response_code(500);
        echo json_encode([
            'message' => 'Error uploading file',
            'php_error' => $_FILES['image']['error']
        ]);
        exit();
    }

    $imageName = $originalName; // overwrite default with uploaded file name
}

// Insert into database
$stmt = $conn->prepare('INSERT INTO blog_posts (title, content, author, imageName) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $title, $content, $author, $imageName);

if ($stmt->execute()) {
    $id = $stmt->insert_id;
    http_response_code(201);
    echo json_encode(['message' => 'Post created successfully', 'id' => $id, 'imageName' => $imageName]);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Error creating post: ' . $stmt->error]);
}

$stmt->close();
$conn->close();

?>

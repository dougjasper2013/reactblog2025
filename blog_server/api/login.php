<?php
session_start();
header("Content-Type: application/json");
include "connect.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['userName'], $data['password'])) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
}

$userName = mysqli_real_escape_string($con, $data['userName']);
$password = $data['password'];

// Find user
$stmt = $con->prepare("SELECT registrationID, password FROM registrations WHERE userName = ?");
$stmt->bind_param("s", $userName);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $hash);
    $stmt->fetch();

    if (password_verify($password, $hash)) {
        $_SESSION['user'] = [
            "id" => $id,
            "userName" => $userName
        ];
        echo json_encode(["success" => true, "message" => "Login successful"]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid credentials"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "User not found"]);
}

$stmt->close();
$con->close();
?>

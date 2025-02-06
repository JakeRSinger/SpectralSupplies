<?php
session_start();
header('Content-Type: application/json');

include("db.php");

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["Error" => "User not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch user details
    $stmt = $pdo->prepare('SELECT user_id, user_forename, user_surname, user_email, user_addr_1, user_addr_2, user_addr_town, user_postcode, user_discount 
                            FROM ss_user 
                            WHERE user_id = :user_id');
    $stmt->execute([':user_id' => $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        http_response_code(200); // Success
        echo json_encode($result);
    } else {
        http_response_code(404); // Not Found
        echo json_encode(["Error" => "No user found"]);
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["Error" => "Database error: " . $e->getMessage()]);
}

<?php
header("Content-Type: application/json");
session_start();

require_once 'db.php';

// Set CORS Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "User not logged in."]);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];

    // Get active basket ID
    $stmt = $pdo->prepare("SELECT basket_id FROM ss_basket WHERE basket_user_id = :user_id AND basket_status = 1");
    $stmt->execute([':user_id' => $user_id]);
    $basket = $stmt->fetch();

    if (!$basket) {
        echo json_encode(["basket_items" => []]); // Empty basket
        exit;
    }

    $basket_id = $basket['basket_id'];

    // Fetch basket items
    $stmt = $pdo->prepare("SELECT ss_basket_item.*, ss_stock.stock_name, ss_stock.stock_id
                           FROM ss_basket_item 
                           JOIN ss_stock ON ss_basket_item.bi_stock_id = ss_stock.stock_id
                           WHERE bi_basket_id = :basket_id");
    $stmt->execute([':basket_id' => $basket_id]);
    $basket_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["basket_items" => $basket_items]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Failed to fetch basket items.", "error" => $e->getMessage()]);
}

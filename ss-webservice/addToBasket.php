<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set CORS Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Content-Type: application/json");

session_start(); 

require_once 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["message" => "User not logged in."]);
    exit;
}

try {
    // Read POST data
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['stock_id'])) {
        echo json_encode(["message" => "Invalid request."]);
        exit;
    }

    $stock_id = intval($data['stock_id']);
    $user_id = $_SESSION['user_id']; // Get the logged-in user's ID
    $quantity = isset($data['quantity']) ? intval($data['quantity']) : 1; // Default to 1 if no quantity is provided

    // Check for active basket
    $stmt = $pdo->prepare("SELECT basket_id FROM ss_basket WHERE basket_user_id = :user_id AND basket_status = 1;");
    $stmt->execute([':user_id' => $user_id]);
    $basket = $stmt->fetch();

    if (!$basket) {
        // Create new basket
        $stmt = $pdo->prepare("INSERT INTO ss_basket (basket_user_id, basket_status) VALUES (:user_id, 1)");
        $stmt->execute([":user_id"=> $user_id]);
        $basket_id = $pdo->lastInsertId();
    } else {
        $basket_id = $basket["basket_id"];
    }

    // Check if item already exists in basket
    $stmt = $pdo->prepare("SELECT bi_qty FROM ss_basket_item WHERE bi_basket_id = :basket_id AND bi_stock_id = :stock_id");
    $stmt->execute([':basket_id' => $basket_id, ':stock_id' => $stock_id]);
    $item = $stmt->fetch();

    $removed = false;

    if ($item) {
        // Update the quantity
        $newQuantity = $item['bi_qty'] + $quantity;

        if ($newQuantity < 1) {
            $stmt = $pdo->prepare('DELETE FROM ss_basket_item WHERE bi_basket_id = :basket_id AND bi_stock_id = :stock_id');
            $stmt->execute([':basket_id' => $basket_id, ':stock_id' => $stock_id]);
            $removed = true;
        }
        else {
            $stmt = $pdo->prepare("UPDATE ss_basket_item SET bi_qty = :quantity WHERE bi_basket_id = :basket_id AND bi_stock_id = :stock_id");
            $stmt->execute([':quantity' => $newQuantity, ':basket_id' => $basket_id, ':stock_id' => $stock_id]);
        }

    } else {
        // Add new item to basket
        $stmt = $pdo->prepare("INSERT INTO ss_basket_item (bi_basket_id, bi_stock_id, bi_qty, bi_price_per_unit) 
        VALUES (:basket_id, :stock_id, :quantity, 
                (SELECT stock_price FROM ss_stock WHERE stock_id = :stock_id))");
        $stmt->execute([':basket_id' => $basket_id, ':stock_id' => $stock_id, ':quantity' => $quantity]);
    }

    if ($removed) {
        $stmt = $pdo->prepare('SELECT bi_basket_id FROM ss_basket_item WHERE bi_basket_id = :basket_id');
        $stmt->execute([':basket_id'=> $basket_id]);
        $row = $stmt->fetch();
        $removed = false;

        if (!$row) {
            $stmt = $pdo->prepare('DELETE FROM ss_basket WHERE basket_id = :basket_id AND basket_user_id = :user_id');
            $stmt->execute([':basket_id'=> $basket_id, ':user_id'=> $user_id]);
        }
    }

    echo json_encode(["message" => "Item added to basket successfully."]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Failed to add item to basket.", "error" => $e->getMessage()]);
}

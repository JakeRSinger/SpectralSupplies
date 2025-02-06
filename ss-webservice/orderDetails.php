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

$user_id = $_SESSION["user_id"];

try {
    // Fetch the latest order ID for the logged-in user
    $stmt = $pdo->prepare("SELECT order_id 
                           FROM ss_order 
                           WHERE order_user_id = :user_id 
                           ORDER BY order_date DESC LIMIT 1");
    $stmt->execute([":user_id" => $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        http_response_code(404); // No orders found
        echo json_encode(["Error" => "No orders found for this user."]);
        exit();
    }

    $order_id = $order["order_id"];

    // Fetch order details
    $stmt = $pdo->prepare("SELECT 
                                    ol_order_id, 
                                    ol_stock_id, 
                                    stock_name, 
                                    ol_qty, 
                                    ol_price_per_unit, 
                                    (ol_qty * ol_price_per_unit) AS ol_total, 
                                    order_total 
                                FROM ss_orderline 
                                JOIN ss_order ON ss_orderline.ol_order_id = ss_order.order_id
                                JOIN ss_stock ON ss_orderline.ol_stock_id = ss_stock.stock_id
                                WHERE ol_order_id = :order_id");
    $stmt->execute([":order_id" => $order_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($order_items) {
        http_response_code(200); // Success
        echo json_encode(["order_items" => $order_items]);
    } else {
        http_response_code(404); // No details found for the order
        echo json_encode(["Error" => "No order details found"]);
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["Error" => "Database error: " . $e->getMessage()]);
}

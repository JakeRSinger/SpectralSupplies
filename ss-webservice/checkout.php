<?php
header("Content-Type: application/json");
session_start();

require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit;
}

try {
    $pdo->beginTransaction();
    $user_id = $_SESSION["user_id"];

    // Get active basket
    $stmt = $pdo->prepare("SELECT basket_id FROM ss_basket WHERE basket_user_id = :user_id AND basket_status = 1");
    $stmt->execute([':user_id' => $user_id]);
    $basket = $stmt->fetch();

    if (!$basket) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => "No active basket found."]);
        exit;
    }

    $basket_id = $basket["basket_id"];

    // Get user discount
    $stmt = $pdo->prepare("SELECT user_discount FROM ss_user WHERE user_id = :user_id");
    $stmt->execute([":user_id" => $user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => "User not found."]);
        exit;
    }

    $user_discount = $user["user_discount"];

    // Calculate order total
    $stmt = $pdo->prepare(
        "SELECT bi_stock_id, bi_qty, bi_price_per_unit, (bi_qty * bi_price_per_unit) AS item_total
         FROM ss_basket_item
         WHERE bi_basket_id = :basket_id"
    );
    $stmt->execute([':basket_id' => $basket_id]);
    $basket_items = $stmt->fetchAll();

    $order_total = 0;
    foreach ($basket_items as $item) {
        $order_total += $item['item_total'];
    }

    $discounted_total = $order_total * (1 - $user_discount);

    // Create new order
    $stmt = $pdo->prepare(
        "INSERT INTO ss_order (order_user_id, order_total)
         VALUES (:user_id, :order_total)"
    );
    $stmt->execute([':user_id' => $user_id, ':order_total' => $discounted_total]);
    $order_id = $pdo->lastInsertId();

    // Populate orderlines
    $stmt = $pdo->prepare(
        "INSERT INTO ss_orderline (ol_order_id, ol_stock_id, ol_qty, ol_price_per_unit)
         VALUES (:order_id, :stock_id, :qty, :price_per_unit)"
    );
    foreach ($basket_items as $item) {
        $stmt->execute([
            ':order_id' => $order_id,
            ':stock_id' => $item["bi_stock_id"],
            ':qty' => $item["bi_qty"],
            ':price_per_unit' => $item['bi_price_per_unit']
        ]);
    }

    // Update stock quantities
    $checkStmt = $pdo->prepare("SELECT stock_qty FROM ss_stock WHERE stock_id = :stock_id");
    $updateStmt = $pdo->prepare(
        "UPDATE ss_stock SET stock_qty = GREATEST(stock_qty - :qty, 0) WHERE stock_id = :stock_id"
    );
    foreach ($basket_items as $item) {
        $checkStmt->execute([":stock_id" => $item["bi_stock_id"]]);
        $stockQty = $checkStmt->fetchColumn();

        if ($stockQty < $item["bi_qty"]) {
            throw new Exception("Insufficient stock for item ID " . $item["bi_stock_id"]);
        }

        $updateStmt->execute([
            ":qty" => $item["bi_qty"],
            ":stock_id" => $item["bi_stock_id"]
        ]);
    }

    // Mark basket as complete and clear items
    $pdo->prepare("UPDATE ss_basket SET basket_status = 0 WHERE basket_id = :basket_id")
        ->execute([":basket_id" => $basket_id]);

    $pdo->prepare("DELETE FROM ss_basket_item WHERE bi_basket_id = :basket_id")
        ->execute([":basket_id" => $basket_id]);

    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Checkout completed successfully.", "order_id" => $order_id]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Checkout failed.", "error" => $e->getMessage()]);
}

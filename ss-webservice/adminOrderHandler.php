<?php
session_start();
include("db.php");

if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] === 1) {
    $password = $_POST["password"];

    try {
        // Fetch admin password
        $stmt = $pdo->prepare("SELECT user_password FROM ss_user WHERE user_id = :user_id");
        $stmt->execute([':user_id' => 1]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($password, $row['user_password'])) {
            $account = $_POST['account'];
            $order = $_POST['order'];

            // Validate account and order IDs
            if (!filter_var($account, FILTER_VALIDATE_INT) || !filter_var($order, FILTER_VALIDATE_INT)) {
                $_SESSION['error_message'] = "Invalid account or order ID.";
                header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/adminOrder.php");
                exit();
            }

            // Fetch current cancellation status
            $stmt = $pdo->prepare(
                "SELECT order_cancelled 
                 FROM ss_order 
                 WHERE order_user_id = :account AND order_id = :order"
            );
            $stmt->execute([':account' => $account, ':order' => $order]);
            $active = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($active) {
                // Toggle cancellation status
                $toggle = $active['order_cancelled'] == 1 ? 0 : 1;

                // Update `ss_order`
                $stmt = $pdo->prepare(
                    "UPDATE ss_order 
                     SET order_cancelled = :toggle 
                     WHERE order_user_id = :account AND order_id = :order"
                );
                $stmt->execute([':toggle' => $toggle, ':account' => $account, ':order' => $order]);

                // Update `ss_orderline`
                $stmt = $pdo->prepare(
                    "UPDATE ss_orderline 
                     SET ol_order_cancelled = :toggle 
                     WHERE ol_order_id = :order"
                );
                $stmt->execute([':toggle' => $toggle, ':order' => $order]);

                unset($_SESSION['error_message']);
                header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/adminOrder.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Account or order not found.";
                header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/adminOrder.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Incorrect password.";
            header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/adminOrder.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/adminOrder.php");
        exit();
    }
} else {
    $_SESSION['error_message'] = "Only an admin account can access that page.";
    header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/login.php");
    exit();
}

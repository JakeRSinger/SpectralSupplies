<?php
session_start();
include("db.php");

if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] === 1) {
    $password = $_POST["password"];

    try {
        // Fetch admin password
        $stmt = $pdo->prepare("SELECT user_password 
                                    FROM ss_user 
                                    WHERE user_id = :user_id");
        $stmt->execute([':user_id' => 1]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($password, $row['user_password'])) {
            $account = $_POST['account'];

            // Validate account ID
            if (!filter_var($account, FILTER_VALIDATE_INT)) {
                $_SESSION['error_message'] = "Invalid account ID.";
                header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/adminAcc.php");
                exit();
            }

            // Fetch current activation status
            $stmt = $pdo->prepare("SELECT user_active 
                                        FROM ss_user 
                                        WHERE user_id = :account");
            $stmt->execute([':account' => $account]);
            $active = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($active) {
                // Toggle activation status
                $toggle = $active['user_active'] == 1 ? 0 : 1;

                $stmt = $pdo->prepare("UPDATE ss_user 
                                            SET user_active = :toggle 
                                            WHERE user_id = :account");
                $stmt->execute([':toggle' => $toggle,
                                        ':account' => $account]);

                unset($_SESSION['error_message']);
                header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/adminAcc.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Account not found.";
                header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/adminAcc.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Incorrect password.";
            header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/adminAcc.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/adminAcc.php");
        exit();
    }
} else {
    $_SESSION['error_message'] = "Only an admin account can access that page.";
    header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/login.php");
    exit();
}

<?php
session_start();
include("db.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT user_id, user_password, user_active FROM ss_user WHERE user_email = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        // Check if a user with this email exists
        if ($stmt->rowCount() === 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row['user_active'] === 0) {
                $_SESSION['error_message'] = "Your account has been deactivated by the admin team.";
                header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/login.php");
                exit();
            }

            // Verify the entered password against the hashed password in the database
            if (password_verify($password, $row['user_password'])) {
                $_SESSION['user_email'] = $username;
                $_SESSION['user_id'] = $row['user_id'];
                unset($_SESSION['error_message']);

                if ($row["user_id"] === 1) {
                    header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/adminAcc.php");
                    exit();
                }
                else {
                    // Redirect after setting session variables
                    header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/index.html");
                    exit();
                }

            } else {
                $_SESSION['error_message'] = "Invalid password.";
                header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/login.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "No account found with that email.";
            header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/login.php");
            exit();
        }
    } catch (PDOException $e) {
        // Catch any errors and display the error message
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/login.php");
        exit();
    }
}
?>

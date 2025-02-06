<?php
session_start();
include 'db.php'; // Include database connection

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $user_id = $_SESSION['user_id'];
    $forename = trim($_POST['new_user_forename']);
    $surname = trim($_POST['new_user_surname']);
    $email = trim($_POST['new_user_email']);
    $password = password_hash($_POST['new_user_password'], PASSWORD_DEFAULT);
    $addr1 = trim($_POST['new_user_addr_1']);
    $addr2 = trim($_POST['new_user_addr_2']);
    $town = trim($_POST['new_user_addr_town']);
    $postcode = trim($_POST['new_user_postcode']);
    $membership = $_POST['new_membership'];
    $old_password = $_POST['user_password'];

    // Assign discount based on membership level
    switch ($membership) {
        case 'gold':
            $discount = 0.20;
            break;
        case 'silver':
            $discount = 0.10;
            break;
        case 'bronze':
            $discount = 0.00;
            break;
        default:
            $discount = 0.00;
            break;
    }

    try {
        // Verify old password
        $stmt = $pdo->prepare('SELECT user_password FROM ss_user WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($old_password, $row['user_password'])) {
            // Prepare the SQL statement for updating user details
            $stmt = $pdo->prepare(
                "UPDATE ss_user 
                SET 
                    user_forename = :forename, 
                    user_surname = :surname, 
                    user_email = :email, 
                    user_password = :password, 
                    user_addr_1 = :addr1, 
                    user_addr_2 = :addr2, 
                    user_addr_town = :town, 
                    user_postcode = :postcode, 
                    user_discount = :discount
                WHERE 
                    user_id = :user_id"
            );

            // Bind parameters
            $stmt->bindValue(':forename', $forename, PDO::PARAM_STR);
            $stmt->bindValue(':surname', $surname, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':password', $password, PDO::PARAM_STR);
            $stmt->bindValue(':addr1', $addr1, PDO::PARAM_STR);
            $stmt->bindValue(':addr2', $addr2, PDO::PARAM_STR);
            $stmt->bindValue(':town', $town, PDO::PARAM_STR);
            $stmt->bindValue(':postcode', $postcode, PDO::PARAM_STR);
            $stmt->bindValue(':discount', $discount, PDO::PARAM_STR);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

            // Execute the update query
            $stmt->execute();

            // Reload the account page on success
            header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/account.html");
            exit;
        } else {
            echo "Error: Incorrect current password.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    } finally {
        // Close the statement
        $stmt = null;
    }
} else {
    echo "Invalid request method.";
}

<?php
include 'db.php'; // Include database connection

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $forename = $_POST['user_forename'];
    $surname = $_POST['user_surname'];
    $email = $_POST['user_email'];
    $password = password_hash($_POST['user_password'], PASSWORD_DEFAULT);
    $addr1 = $_POST['user_addr_1'];
    $addr2 = $_POST['user_addr_2'];
    $town = $_POST['user_addr_town'];
    $postcode = $_POST['user_postcode'];
    $membership = $_POST['membership'];

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

    // Prepare the SQL statement
    $stmt = $pdo->prepare("INSERT INTO ss_user (user_forename, user_surname, user_email, user_password, user_addr_1, user_addr_2, user_addr_town, user_postcode, user_discount) 
                            VALUES (:forename, :surname, :email, :password, :addr1, :addr2, :town, :postcode, :discount)");

    // Bind parameters using bindValue (PDO)
    $stmt->bindValue(':forename', $forename, PDO::PARAM_STR);
    $stmt->bindValue(':surname', $surname, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':password', $password, PDO::PARAM_STR);
    $stmt->bindValue(':addr1', $addr1, PDO::PARAM_STR);
    $stmt->bindValue(':addr2', $addr2, PDO::PARAM_STR);
    $stmt->bindValue(':town', $town, PDO::PARAM_STR);
    $stmt->bindValue(':postcode', $postcode, PDO::PARAM_STR);
    $stmt->bindValue(':discount', $discount, PDO::PARAM_STR);

    // Execute the query
    try {
        $stmt->execute();
        header("Location: https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/login.php");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    // Close the statement
    $stmt = null;
}

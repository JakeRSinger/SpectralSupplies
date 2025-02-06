<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="en" class="login">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin - Spectral Supply Co.</title>
        <link rel="stylesheet" href="styles/styles.css">
        <link rel="icon" type="image/x-icon" href="img/favicon.png">
        <script type="text/javascript" src="scripts/hammer.js"></script>
        <script type="text/javascript" src="scripts/modeAndNav.js"></script>
    </head>
    <body class="login">
        <div class="login-container">
            <h2 class="login-heading">Change Account Status</h2>
            <div class="form-container">
                <form action="https://comp-server.uhi.ac.uk/~21011375/ss-webservice/adminAccountHandler.php" method="POST">
                    <label for="account">User ID:</label>
                    <input type="text" id="account" name="account" required>

                    <label for="password">Enter Admin Password:</label>
                    <input type="password" id="password" name="password" required>

                    <button type="submit">Submit</button>
                </form>

                <br>

                <?php
                    // Display the error message if it exists in the session
                    if (isset($_SESSION['error_message'])) {
                        echo "<p style='color: red;'>" . htmlspecialchars($_SESSION['error_message']) . "</p>";
                        unset($_SESSION['error_message']); // Clear the error message after displaying it
                    }
                ?>
            </div>
            <p>Change order status instead? <a href="adminOrder.php">Change it here</a>.</p>
        </div>
    </body>
</html>
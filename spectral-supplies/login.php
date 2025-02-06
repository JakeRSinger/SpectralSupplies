<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="en" class="login">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/x-icon" href="img/favicon.png">
        <title>Login - Spectral Supply Co.</title>
        <link rel="stylesheet" href="styles/styles.css">
        <script type="text/javascript" src="scripts/hammer.js"></script>
        <script type="text/javascript" src="scripts/modeAndNav.js"></script>
    </head>
    <body class="login">
        <div class="login-container">
            <h2 class="login-heading">Login to Spectral Supply Co.</h2>
            <div class="form-container">
                <form action="https://comp-server.uhi.ac.uk/~21011375/ss-webservice/loginHandler.php" method="POST">
                    <label for="username">Email:</label>
                    <input type="email" id="username" name="username" required>

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>

                    <button type="submit">Login</button>
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
            <p>Don't have an account? <a href="register.html">Register here</a>.</p>
        </div>
    </body>
</html>

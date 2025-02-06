<?php
// Include DB connection
require 'db.php';

// Set CORS Policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Content-Type: application/json");

try {
    // Check for GET URL params (limit and offset)
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 8;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

    // SQL query to get products with prepared statement
    $sql = "SELECT * FROM ss_stock LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    // Execute the query
    $stmt->execute();

    // Fetch data as an associative array
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
        // Return data & set response code
        http_response_code(200);
        echo json_encode($result);
    } else {
        // Handle case where no data was found
        http_response_code(404);
        echo json_encode(["Error" => "No products found"]);
    }
} catch (PDOException $e) {
    // Handle database connection errors
    http_response_code(500);
    echo json_encode(["Error" => "Database error: " . $e->getMessage()]);
}

?>

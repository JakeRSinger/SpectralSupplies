<?php

    $host = "comp-server.uhi.ac.uk";
    $dbName = "IN21011375";
    $username = "IN21011375";
    $password = '21011375';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbName", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch (PDOException $e) {
        echo "Connection Failed: ". $e->getMessage();
    }
?>
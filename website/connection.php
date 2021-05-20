
<?php
    $dbServername = "hostname";   
    $dbUsername = "username";
    $dbPassword = "password";
    $dbName = "database";

    // Create connection
    $conn = new mysqli($dbServername, $dbUsername, $dbPassword, $dbName);
    mysqli_set_charset($conn, "utf8");

    // Check connection
    if ($conn->connect_error)
    {
        die("Connection failed: " . $conn->connect_error);
    }
?>

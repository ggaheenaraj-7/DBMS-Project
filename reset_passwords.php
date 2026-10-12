<?php

$host = "localhost";
$dbname = "smart_logistics";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$adminPassword = password_hash("admin123", PASSWORD_DEFAULT);
$customerPassword = password_hash("customer123", PASSWORD_DEFAULT);

$conn->query("
    UPDATE users
    SET password_hash = '$adminPassword'
    WHERE email = 'admin@demo.com'
");

$conn->query("
    UPDATE users
    SET password_hash = '$customerPassword'
    WHERE email = 'customer@demo.com'
");

echo "<h2>Passwords reset successfully!</h2>";
echo "<p>Admin: admin@demo.com / admin123</p>";
echo "<p>Customer: customer@demo.com / customer123</p>";

$conn->close();
?>
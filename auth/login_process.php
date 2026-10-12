<?php

session_start();

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: login.php");
    exit();

}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$role = $_POST["role"] ?? "";

if ($email === "" || $password === "" || $role === "") {

    header(
        "Location: login.php?error=" .
        urlencode("Please fill all fields.")
    );

    exit();
}

if ($role !== "admin" && $role !== "customer") {

    header(
        "Location: login.php?error=" .
        urlencode("Invalid role selected.")
    );

    exit();
}


$stmt = $conn->prepare(
    "SELECT id, name, email, password_hash, role
     FROM users
     WHERE email = ?
     AND role = ?
     LIMIT 1"
);

$stmt->bind_param("ss", $email, $role);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {

    header(
        "Location: login.php?error=" .
        urlencode("User not found or incorrect role.")
    );

    exit();
}


$user = $result->fetch_assoc();


if (!password_verify($password, $user["password_hash"])) {

    header(
        "Location: login.php?error=" .
        urlencode("Incorrect password.")
    );

    exit();
}


session_regenerate_id(true);

$_SESSION["user_id"] = $user["id"];
$_SESSION["name"] = $user["name"];
$_SESSION["email"] = $user["email"];
$_SESSION["role"] = $user["role"];


$stmt->close();
$conn->close();


if ($user["role"] === "admin") {

    header("Location: ../admin/dashboard.php");
    exit();

}


header("Location: ../customer/dashboard.php");
exit();

?>
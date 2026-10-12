<?php

session_start();

require_once "../config/database.php";

header("Content-Type: application/json");


if (!isset($_SESSION["user_id"])) {

    echo json_encode([
        "success" => false,
        "message" => "Not logged in."
    ]);

    exit();

}


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);

    exit();

}


$shipment_id = intval($_POST["shipment_id"] ?? 0);

$latitude = floatval($_POST["latitude"] ?? 0);

$longitude = floatval($_POST["longitude"] ?? 0);

$accuracy = floatval($_POST["accuracy"] ?? 0);


if ($shipment_id <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid shipment."
    ]);

    exit();

}


if (
    $latitude < -90 ||
    $latitude > 90 ||
    $longitude < -180 ||
    $longitude > 180
) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid GPS coordinates."
    ]);

    exit();

}


/*
 Check shipment ownership.

 Customer can update only their own shipment.
 Admin can update any shipment.
*/

if ($_SESSION["role"] === "customer") {

    $check = $conn->prepare(
        "SELECT id
         FROM shipments
         WHERE id = ?
         AND customer_id = ?
         LIMIT 1"
    );

    $check->bind_param(
        "ii",
        $shipment_id,
        $_SESSION["user_id"]
    );

} else {

    $check = $conn->prepare(
        "SELECT id
         FROM shipments
         WHERE id = ?
         LIMIT 1"
    );

    $check->bind_param(
        "i",
        $shipment_id
    );

}


$check->execute();

$result = $check->get_result();


if ($result->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" => "Shipment not found."
    ]);

    exit();

}

$check->close();


$stmt = $conn->prepare(
    "INSERT INTO shipment_locations
    (
        shipment_id,
        latitude,
        longitude,
        accuracy
    )
    VALUES (?, ?, ?, ?)"
);


$stmt->bind_param(
    "iddd",
    $shipment_id,
    $latitude,
    $longitude,
    $accuracy
);


if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Location updated.",
        "latitude" => $latitude,
        "longitude" => $longitude
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Database error."
    ]);

}


$stmt->close();

$conn->close();

?>
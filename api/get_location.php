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


$shipment_id = intval($_GET["shipment_id"] ?? 0);


if ($shipment_id <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid shipment."
    ]);

    exit();

}


/*
 Make sure the customer can only see
 their own shipment.
*/

if ($_SESSION["role"] === "customer") {

    $stmt = $conn->prepare(
        "SELECT id
         FROM shipments
         WHERE id = ?
         AND customer_id = ?
         LIMIT 1"
    );

    $stmt->bind_param(
        "ii",
        $shipment_id,
        $_SESSION["user_id"]
    );

} else {

    $stmt = $conn->prepare(
        "SELECT id
         FROM shipments
         WHERE id = ?
         LIMIT 1"
    );

    $stmt->bind_param(
        "i",
        $shipment_id
    );

}


$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" => "Shipment not found."
    ]);

    exit();

}

$stmt->close();


$locationStmt = $conn->prepare(
    "SELECT
        latitude,
        longitude,
        accuracy,
        recorded_at
     FROM shipment_locations
     WHERE shipment_id = ?
     ORDER BY recorded_at DESC
     LIMIT 1"
);


$locationStmt->bind_param(
    "i",
    $shipment_id
);


$locationStmt->execute();

$locationResult = $locationStmt->get_result();


if ($locationResult->num_rows === 0) {

    echo json_encode([
        "success" => true,
        "location_available" => false
    ]);

    exit();

}


$location = $locationResult->fetch_assoc();


echo json_encode([
    "success" => true,
    "location_available" => true,
    "latitude" => (float)$location["latitude"],
    "longitude" => (float)$location["longitude"],
    "accuracy" => (float)$location["accuracy"],
    "recorded_at" => $location["recorded_at"]
]);


$locationStmt->close();

$conn->close();

?>
<?php

session_start();

require_once "../config/database.php";

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "customer"
) {
    header("Location: ../auth/login.php");
    exit();
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $sender_name = trim($_POST["sender_name"] ?? "");
    $receiver_name = trim($_POST["receiver_name"] ?? "");
    $origin = trim($_POST["origin"] ?? "");
    $destination = trim($_POST["destination"] ?? "");
    $package_type = trim($_POST["package_type"] ?? "");
    $weight = floatval($_POST["weight"] ?? 0);
    $priority = trim($_POST["priority"] ?? "Normal");

    if (
        $sender_name === "" ||
        $receiver_name === "" ||
        $origin === "" ||
        $destination === "" ||
        $package_type === "" ||
        $weight <= 0
    ) {

        $error = "Please fill all required fields.";

    } else {

        // Generate unique tracking ID
        do {

            $tracking_id =
                "SL" .
                date("Ymd") .
                strtoupper(
                    substr(
                        str_shuffle(
                            "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
                        ),
                        0,
                        6
                    )
                );

            $check = $conn->prepare(
                "SELECT id
                 FROM shipments
                 WHERE tracking_id = ?
                 LIMIT 1"
            );

            $check->bind_param(
                "s",
                $tracking_id
            );

            $check->execute();

            $check_result =
                $check->get_result();

            $exists =
                $check_result->num_rows > 0;

            $check->close();

        } while ($exists);


        $status = "Booked";


        $stmt = $conn->prepare(
            "INSERT INTO shipments
            (
                customer_id,
                tracking_id,
                sender_name,
                receiver_name,
                origin,
                destination,
                package_type,
                weight,
                priority,
                status
            )
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );


        $stmt->bind_param(
            "issssssdds",
            $_SESSION["user_id"],
            $tracking_id,
            $sender_name,
            $receiver_name,
            $origin,
            $destination,
            $package_type,
            $weight,
            $priority,
            $status
        );


        if ($stmt->execute()) {

            $shipment_id =
                $stmt->insert_id;

            $stmt->close();


            // Add first shipment event

            $event_status = "Booked";

            $location = $origin;

            $event_message =
                "Shipment successfully booked.";

            $event = $conn->prepare(
                "INSERT INTO shipment_events
                (
                    shipment_id,
                    status,
                    location,
                    message
                )
                VALUES (?, ?, ?, ?)"
            );


            $event->bind_param(
                "isss",
                $shipment_id,
                $event_status,
                $location,
                $event_message
            );


            $event->execute();

            $event->close();


            $message =
                "Shipment created successfully! Tracking ID: " .
                $tracking_id;

        } else {

            $error =
                "Unable to create shipment: " .
                $conn->error;

            $stmt->close();

        }

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>

<title>Book Shipment - Smart Logistics</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
}

body {

    min-height: 100vh;

    background:
    linear-gradient(
        135deg,
        #071e3d,
        #126e82,
        #1f8a70
    );

}

nav {

    background: white;

    padding: 18px 5%;

    display: flex;

    justify-content: space-between;

    align-items: center;

}

.logo {

    font-size: 21px;

    font-weight: bold;

    color: #073b4c;

}

.nav-links a {

    text-decoration: none;

    margin-left: 15px;

    color: #073b4c;

    font-weight: bold;

}

.logout {

    background: #e63946;

    color: white !important;

    padding: 10px 16px;

    border-radius: 8px;

}

.container {

    width: 92%;

    max-width: 900px;

    margin: 40px auto;

}

.title {

    color: white;

    margin-bottom: 25px;

}

.title h1 {

    font-size: 34px;

    margin-bottom: 8px;

}

.form-box {

    background: white;

    padding: 35px;

    border-radius: 20px;

    box-shadow: 0 20px 50px rgba(0,0,0,.25);

}

.form-box h2 {

    color: #073b4c;

    margin-bottom: 25px;

}

.form-grid {

    display: grid;

    grid-template-columns:
    repeat(2, 1fr);

    gap: 20px;

}

.full {

    grid-column: 1 / -1;

}

label {

    display: block;

    font-weight: bold;

    color: #333;

    margin-bottom: 8px;

}

input,
select {

    width: 100%;

    padding: 13px;

    border: 2px solid #ddd;

    border-radius: 9px;

    font-size: 15px;

}

input:focus,
select:focus {

    border-color: #126e82;

    outline: none;

}

.submit-btn {

    width: 100%;

    margin-top: 25px;

    padding: 15px;

    border: none;

    border-radius: 10px;

    background:
    linear-gradient(
        135deg,
        #126e82,
        #1f8a70
    );

    color: white;

    font-size: 17px;

    font-weight: bold;

    cursor: pointer;

}

.submit-btn:hover {

    transform: translateY(-2px);

}

.success {

    background: #dcfce7;

    color: #166534;

    padding: 15px;

    border-radius: 10px;

    margin-bottom: 20px;

}

.error {

    background: #fee2e2;

    color: #b91c1c;

    padding: 15px;

    border-radius: 10px;

    margin-bottom: 20px;

}

.success a {

    display: inline-block;

    margin-top: 10px;

    color: #166534;

    font-weight: bold;

}

.back {

    margin-top: 20px;

}

.back a {

    color: #126e82;

    text-decoration: none;

    font-weight: bold;

}

@media(max-width:700px) {

    .form-grid {

        grid-template-columns: 1fr;

    }

    .full {

        grid-column: auto;

    }

}

</style>

</head>

<body>


<nav>

<div class="logo">

🚚 Smart Logistics | Customer

</div>


<div class="nav-links">

<a href="dashboard.php">
Dashboard
</a>

<a
href="../auth/logout.php"
class="logout"
>
Logout
</a>

</div>

</nav>


<div class="container">


<div class="title">

<h1>📦 Book New Shipment</h1>

<p>
Create a new delivery and receive your tracking ID.
</p>

</div>


<div class="form-box">


<?php if ($message !== ""): ?>

<div class="success">

<?= htmlspecialchars($message) ?>

<br>

<a href="track.php?tracking_id=<?= urlencode($tracking_id) ?>">
📍 Track This Shipment
</a>

</div>

<?php endif; ?>


<?php if ($error !== ""): ?>

<div class="error">

<?= htmlspecialchars($error) ?>

</div>

<?php endif; ?>


<h2>Shipment Details</h2>


<form
method="POST"
>


<div class="form-grid">


<div>

<label>
Sender Name *
</label>

<input
type="text"
name="sender_name"
placeholder="Enter sender name"
required
>

</div>


<div>

<label>
Receiver Name *
</label>

<input
type="text"
name="receiver_name"
placeholder="Enter receiver name"
required
>

</div>


<div>

<label>
Origin *
</label>

<input
type="text"
name="origin"
placeholder="Pickup location"
required
>

</div>


<div>

<label>
Destination *
</label>

<input
type="text"
name="destination"
placeholder="Delivery location"
required
>

</div>


<div>

<label>
Package Type *
</label>

<select
name="package_type"
required
>

<option value="">
Select Package
</option>

<option value="Document">
Document
</option>

<option value="Electronics">
Electronics
</option>

<option value="Clothing">
Clothing
</option>

<option value="Food">
Food
</option>

<option value="Fragile">
Fragile
</option>

<option value="Other">
Other
</option>

</select>

</div>


<div>

<label>
Weight (kg) *
</label>

<input
type="number"
name="weight"
step="0.01"
min="0.01"
placeholder="Example: 2.5"
required
>

</div>


<div class="full">

<label>
Priority
</label>

<select name="priority">

<option value="Normal">
Normal
</option>

<option value="Express">
Express
</option>

<option value="Urgent">
Urgent
</option>

</select>

</div>


</div>


<button
type="submit"
class="submit-btn"
>
🚚 BOOK SHIPMENT
</button>


</form>


<div class="back">

<a href="dashboard.php">
← Back to Dashboard
</a>

</div>


</div>

</div>

</body>

</html>

<?php

$conn->close();

?>
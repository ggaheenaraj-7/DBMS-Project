<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {

    header("Location: ../auth/login.php");
    exit();

}

$customer_id = $_SESSION["user_id"];

$message = "";
$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $sender_name = trim($_POST["sender_name"] ?? "");
    $receiver_name = trim($_POST["receiver_name"] ?? "");
    $origin = trim($_POST["origin"] ?? "");
    $destination = trim($_POST["destination"] ?? "");
    $package_type = trim($_POST["package_type"] ?? "");
    $weight = floatval($_POST["weight"] ?? 0);
    $priority = $_POST["priority"] ?? "Normal";


    if (
        $sender_name === "" ||
        $receiver_name === "" ||
        $origin === "" ||
        $destination === "" ||
        $package_type === "" ||
        $weight <= 0
    ) {

        $error = "Please fill all required fields correctly.";

    } else {

        do {

            $tracking_id =
                "SL" .
                date("Ymd") .
                strtoupper(
                    substr(
                        bin2hex(random_bytes(5)),
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

            $check->bind_param("s", $tracking_id);

            $check->execute();

            $checkResult = $check->get_result();

            $exists = $checkResult->num_rows > 0;

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
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );


        $stmt->bind_param(
            "issssssdis",
            $customer_id,
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

            $shipment_id = $stmt->insert_id;

            $eventMessage =
                "Shipment successfully booked.";

            $eventStmt = $conn->prepare(
                "INSERT INTO shipment_events
                (
                    shipment_id,
                    status,
                    location,
                    message
                )
                VALUES (?, ?, ?, ?)"
            );

            $eventStmt->bind_param(
                "isss",
                $shipment_id,
                $status,
                $origin,
                $eventMessage
            );

            $eventStmt->execute();

            $eventStmt->close();

            $message =
                "Shipment booked successfully! Tracking ID: " .
                $tracking_id;

        } else {

            $error = "Unable to create shipment.";

        }

        $stmt->close();

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Book Shipment</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: Arial, sans-serif;

    background: #07111f;

    color: white;
}

nav {

    padding: 20px 6%;

    display: flex;
    justify-content: space-between;

    border-bottom: 1px solid #1e293b;

    background: #020617;
}

.logo {
    font-size: 24px;
    font-weight: bold;
}

.logo span {
    color: #38bdf8;
}

nav a {
    color: white;
    text-decoration: none;
    margin-left: 20px;
}

.container {

    max-width: 850px;

    width: 92%;

    margin: 50px auto;

}

.box {

    background: #0f172a;

    padding: 40px;

    border-radius: 20px;

    border: 1px solid #1e293b;

    box-shadow: 0 20px 50px rgba(0,0,0,.3);
}

h1 {
    margin-top: 0;
}

.subtitle {
    color: #94a3b8;
    margin-bottom: 30px;
}

.grid {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 20px;
}

.field label {

    display: block;

    margin-bottom: 8px;

    color: #cbd5e1;
}

.field input,
.field select {

    width: 100%;

    padding: 13px;

    background: #07111f;

    border: 1px solid #334155;

    border-radius: 9px;

    color: white;
}

.full {
    grid-column: 1 / -1;
}

button {

    width: 100%;

    padding: 15px;

    margin-top: 25px;

    border: none;

    border-radius: 10px;

    background: linear-gradient(135deg,#06b6d4,#2563eb);

    color: white;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;
}

.success {

    background: rgba(34,197,94,.15);

    border: 1px solid #22c55e;

    padding: 15px;

    border-radius: 10px;

    margin-bottom: 25px;

    color: #86efac;
}

.error {

    background: rgba(239,68,68,.15);

    border: 1px solid #ef4444;

    padding: 15px;

    border-radius: 10px;

    margin-bottom: 25px;

    color: #fca5a5;
}

@media(max-width:650px) {

    .grid {
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
        Smart<span>Logistics</span>
    </div>

    <div>

        <a href="dashboard.php">Dashboard</a>

        <a href="my_shipments.php">My Shipments</a>

        <a href="../auth/logout.php">Logout</a>

    </div>

</nav>


<div class="container">

<div class="box">

<h1>📦 Book New Shipment</h1>

<p class="subtitle">
Enter the shipment details below.
</p>


<?php if ($message !== ""): ?>

<div class="success">

    <?php echo htmlspecialchars($message); ?>

    <br><br>

    <a
        href="track.php?tracking_id=<?php echo urlencode($tracking_id); ?>"
        style="color:#38bdf8;"
    >
        Track this shipment →
    </a>

</div>

<?php endif; ?>


<?php if ($error !== ""): ?>

<div class="error">

    <?php echo htmlspecialchars($error); ?>

</div>

<?php endif; ?>


<form method="POST">

<div class="grid">

<div class="field">

<label>Sender Name</label>

<input
    type="text"
    name="sender_name"
    required
>

</div>


<div class="field">

<label>Receiver Name</label>

<input
    type="text"
    name="receiver_name"
    required
>

</div>


<div class="field">

<label>Origin</label>

<input
    type="text"
    name="origin"
    placeholder="Hyderabad"
    required
>

</div>


<div class="field">

<label>Destination</label>

<input
    type="text"
    name="destination"
    placeholder="Bangalore"
    required
>

</div>


<div class="field">

<label>Package Type</label>

<input
    type="text"
    name="package_type"
    placeholder="Electronics"
    required
>

</div>


<div class="field">

<label>Weight (kg)</label>

<input
    type="number"
    name="weight"
    step="0.01"
    min="0.01"
    required
>

</div>


<div class="field full">

<label>Priority</label>

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


<button type="submit">
🚚 Book Shipment
</button>

</form>

</div>

</div>

</body>
</html>
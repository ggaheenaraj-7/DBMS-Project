<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {

    header("Location: ../auth/login.php");
    exit();

}


$allowedStatuses = [
    "Booked",
    "Picked Up",
    "In Transit",
    "Out for Delivery",
    "Delivered",
    "Cancelled"
];


$message = "";
$error = "";


/* UPDATE SHIPMENT STATUS */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $shipment_id = intval($_POST["shipment_id"] ?? 0);

    $new_status = trim($_POST["status"] ?? "");

    $location = trim($_POST["location"] ?? "");

    $event_message = trim($_POST["message"] ?? "");


    if (
        $shipment_id <= 0 ||
        !in_array($new_status, $allowedStatuses, true)
    ) {

        $error = "Invalid shipment status.";

    } else {

        $stmt = $conn->prepare(
            "UPDATE shipments
             SET status = ?,
                 delivered_at =
                 CASE
                    WHEN ? = 'Delivered'
                    THEN NOW()
                    ELSE delivered_at
                 END
             WHERE id = ?"
        );

        $stmt->bind_param(
            "ssi",
            $new_status,
            $new_status,
            $shipment_id
        );


        if ($stmt->execute()) {

            $stmt->close();


            if ($location === "") {
                $location = "Logistics Network";
            }

            if ($event_message === "") {

                $event_message =
                    "Shipment status updated to " .
                    $new_status . ".";

            }


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
                $new_status,
                $location,
                $event_message
            );

            $eventStmt->execute();

            $eventStmt->close();


            $message =
                "Shipment status updated successfully.";

        } else {

            $error = "Unable to update shipment.";

            $stmt->close();

        }

    }

}


/* SEARCH / FILTER */

$search = trim($_GET["search"] ?? "");

$statusFilter = trim($_GET["status"] ?? "");


$sql = "
SELECT
    id,
    tracking_id,
    customer_id,
    sender_name,
    receiver_name,
    origin,
    destination,
    package_type,
    weight,
    priority,
    status,
    eta_hours,
    carbon_kg,
    booked_at,
    delivered_at
FROM shipments
WHERE 1=1
";


$params = [];

$types = "";


if ($search !== "") {

    $sql .= "
    AND (
        tracking_id LIKE ?
        OR sender_name LIKE ?
        OR receiver_name LIKE ?
        OR origin LIKE ?
        OR destination LIKE ?
    )
    ";

    $like = "%" . $search . "%";

    $params = [
        $like,
        $like,
        $like,
        $like,
        $like
    ];

    $types = "sssss";

}


if ($statusFilter !== "") {

    $sql .= " AND status = ? ";

    $params[] = $statusFilter;

    $types .= "s";

}


$sql .= " ORDER BY booked_at DESC";


$stmt = $conn->prepare($sql);


if (count($params) > 0) {

    $stmt->bind_param(
        $types,
        ...$params
    );

}


$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Shipments</title>

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

    background: #020617;

    border-bottom: 1px solid #1e293b;

    display: flex;

    justify-content: space-between;
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

    width: 95%;

    max-width: 1500px;

    margin: 40px auto;
}

.filters {

    display: flex;

    gap: 10px;

    margin: 25px 0;

    flex-wrap: wrap;
}

.filters input,
.filters select {

    padding: 13px;

    background: #0f172a;

    border: 1px solid #334155;

    border-radius: 9px;

    color: white;
}

.filters input {
    min-width: 300px;
}

button {

    padding: 13px 20px;

    background: #0284c7;

    border: none;

    color: white;

    border-radius: 9px;

    cursor: pointer;
}

.success {

    padding: 15px;

    background: rgba(34,197,94,.15);

    border: 1px solid #22c55e;

    color: #86efac;

    border-radius: 10px;
}

.error {

    padding: 15px;

    background: rgba(239,68,68,.15);

    border: 1px solid #ef4444;

    color: #fca5a5;

    border-radius: 10px;
}

.table-box {

    overflow-x: auto;

    background: #0f172a;

    border-radius: 18px;

    border: 1px solid #1e293b;
}

table {

    width: 100%;

    min-width: 1400px;

    border-collapse: collapse;
}

th,
td {

    padding: 14px;

    text-align: left;

    border-bottom: 1px solid #1e293b;
}

th {
    color: #38bdf8;
}

td {
    color: #cbd5e1;
}

.status {

    padding: 6px 10px;

    border-radius: 20px;

    background: #164e63;

    color: #67e8f9;

    font-size: 12px;
}

.update-form {

    display: flex;

    flex-direction: column;

    gap: 7px;

    min-width: 190px;
}

.update-form select,
.update-form input {

    padding: 8px;

    background: #07111f;

    border: 1px solid #334155;

    color: white;

    border-radius: 7px;
}

.update-form button {

    padding: 8px;
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

<a href="../index.php">Home</a>

<a href="../auth/logout.php">Logout</a>

</div>

</nav>


<div class="container">

<h1>📦 Shipment Management</h1>

<p style="color:#94a3b8;">
Search, monitor and update shipment status.
</p>


<?php if ($message !== ""): ?>

<div class="success">

<?php echo htmlspecialchars($message); ?>

</div>

<br>

<?php endif; ?>


<?php if ($error !== ""): ?>

<div class="error">

<?php echo htmlspecialchars($error); ?>

</div>

<br>

<?php endif; ?>


<form class="filters" method="GET">

<input
    type="text"
    name="search"
    placeholder="Search tracking ID, sender, receiver..."
    value="<?php echo htmlspecialchars($search); ?>"
>


<select name="status">

<option value="">All Statuses</option>

<?php foreach ($allowedStatuses as $status): ?>

<option
    value="<?php echo htmlspecialchars($status); ?>"
    <?php echo $statusFilter === $status ? "selected" : ""; ?>
>

<?php echo htmlspecialchars($status); ?>

</option>

<?php endforeach; ?>

</select>


<button type="submit">
Search
</button>


<a
    href="shipments.php"
    style="
    padding:13px 20px;
    background:#334155;
    color:white;
    text-decoration:none;
    border-radius:9px;
    "
>
Reset
</a>

</form>


<div class="table-box">

<table>

<thead>

<tr>

<th>Tracking ID</th>

<th>Sender</th>

<th>Receiver</th>

<th>Route</th>

<th>Package</th>

<th>Weight</th>

<th>Priority</th>

<th>Status</th>

<th>ETA</th>

<th>Booked</th>

<th>Update</th>

</tr>

</thead>

<tbody>


<?php if ($result->num_rows === 0): ?>

<tr>

<td colspan="11" style="text-align:center;padding:50px;">

No shipments found.

</td>

</tr>

<?php endif; ?>


<?php while ($row = $result->fetch_assoc()): ?>

<tr>

<td>

<strong>
<?php echo htmlspecialchars($row["tracking_id"]); ?>
</strong>

</td>


<td>

<?php echo htmlspecialchars($row["sender_name"] ?? "—"); ?>

</td>


<td>

<?php echo htmlspecialchars($row["receiver_name"] ?? "—"); ?>

</td>


<td>

<?php echo htmlspecialchars($row["origin"] ?? "—"); ?>

<br>

↓

<?php echo htmlspecialchars($row["destination"] ?? "—"); ?>

</td>


<td>

<?php echo htmlspecialchars($row["package_type"] ?? "—"); ?>

</td>


<td>

<?php echo htmlspecialchars($row["weight"] ?? "—"); ?>

kg

</td>


<td>

<?php echo htmlspecialchars($row["priority"] ?? "Normal"); ?>

</td>


<td>

<span class="status">

<?php echo htmlspecialchars($row["status"]); ?>

</span>

</td>


<td>

<?php

if ($row["eta_hours"] !== null) {

    echo htmlspecialchars(
        $row["eta_hours"]
    ) . " hrs";

} else {

    echo "—";

}

?>

</td>


<td>

<?php echo htmlspecialchars($row["booked_at"]); ?>

</td>


<td>

<form
    class="update-form"
    method="POST"
>

<input
    type="hidden"
    name="shipment_id"
    value="<?php echo $row["id"]; ?>"
>


<select name="status">

<?php foreach ($allowedStatuses as $status): ?>

<option
    value="<?php echo htmlspecialchars($status); ?>"
    <?php echo $row["status"] === $status ? "selected" : ""; ?>
>

<?php echo htmlspecialchars($status); ?>

</option>

<?php endforeach; ?>

</select>


<input
    type="text"
    name="location"
    placeholder="Location"
/>


<input
    type="text"
    name="message"
    placeholder="Update message"
/>


<button type="submit">
Update
</button>

</form>

</td>

</tr>

<?php endwhile; ?>


</tbody>

</table>

</div>

</div>

</body>

</html>
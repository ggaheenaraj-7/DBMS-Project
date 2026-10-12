<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {

    header("Location: ../auth/login.php");
    exit();

}

$customer_id = $_SESSION["user_id"];


$stmt = $conn->prepare(
    "SELECT
        id,
        tracking_id,
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
     WHERE customer_id = ?
     ORDER BY booked_at DESC"
);

$stmt->bind_param("i", $customer_id);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Shipments</title>

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

    width: 94%;
    max-width: 1300px;

    margin: 40px auto;
}

.table-box {

    overflow-x: auto;

    background: #0f172a;

    border-radius: 18px;

    border: 1px solid #1e293b;
}

table {

    width: 100%;

    border-collapse: collapse;

    min-width: 1000px;
}

th,
td {

    padding: 16px;

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

    font-size: 13px;
}

.track {

    color: #38bdf8;

    text-decoration: none;
}

.empty {

    padding: 50px;

    text-align: center;

    color: #94a3b8;
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

        <a href="new_shipment.php">Book Shipment</a>

        <a href="track.php">Track</a>

        <a href="../auth/logout.php">Logout</a>

    </div>

</nav>


<div class="container">

<h1>📋 My Shipments</h1>

<p style="color:#94a3b8;">
All shipments booked from your account.
</p>

<br>


<div class="table-box">

<?php if ($result->num_rows === 0): ?>

    <div class="empty">

        <h2>No shipments yet</h2>

        <p>Book your first shipment to see it here.</p>

        <a
            href="new_shipment.php"
            style="color:#38bdf8;"
        >
            Book Shipment
        </a>

    </div>

<?php else: ?>

<table>

<thead>

<tr>

<th>Tracking ID</th>
<th>Sender</th>
<th>Receiver</th>
<th>Origin</th>
<th>Destination</th>
<th>Package</th>
<th>Weight</th>
<th>Priority</th>
<th>Status</th>
<th>ETA</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php while ($row = $result->fetch_assoc()): ?>

<tr>

<td>
<strong>
<?php echo htmlspecialchars($row["tracking_id"]); ?>
</strong>
</td>

<td>
<?php echo htmlspecialchars($row["sender_name"]); ?>
</td>

<td>
<?php echo htmlspecialchars($row["receiver_name"]); ?>
</td>

<td>
<?php echo htmlspecialchars($row["origin"]); ?>
</td>

<td>
<?php echo htmlspecialchars($row["destination"]); ?>
</td>

<td>
<?php echo htmlspecialchars($row["package_type"]); ?>
</td>

<td>
<?php echo htmlspecialchars($row["weight"]); ?> kg
</td>

<td>
<?php echo htmlspecialchars($row["priority"]); ?>
</td>

<td>

<span class="status">

<?php echo htmlspecialchars($row["status"]); ?>

</span>

</td>

<td>

<?php

if ($row["eta_hours"] !== null) {
    echo htmlspecialchars($row["eta_hours"]) . " hrs";
} else {
    echo "—";
}

?>

</td>

<td>

<a
    class="track"
    href="track.php?tracking_id=<?php echo urlencode($row["tracking_id"]); ?>"
>
Track
</a>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

<?php endif; ?>

</div>

</div>

</body>

</html>
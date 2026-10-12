<?php

session_start();

require_once "../config/database.php";

// ------------------------------------
// CHECK CUSTOMER LOGIN
// ------------------------------------

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "customer"
) {
    header("Location: ../auth/login.php");
    exit();
}

$customer_id = $_SESSION["user_id"];


// ------------------------------------
// GET CUSTOMER SHIPMENTS
// ------------------------------------

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
        booked_at
     FROM shipments
     WHERE customer_id = ?
     ORDER BY id DESC"
);

$stmt->bind_param("i", $customer_id);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>My Shipments | Smart Logistics</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
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

    color: #333;

}


/* NAVBAR */

nav {

    background: rgba(255,255,255,0.97);

    padding: 18px 5%;

    display: flex;

    justify-content: space-between;

    align-items: center;

    box-shadow:
    0 4px 20px rgba(0,0,0,0.15);

    position: sticky;

    top: 0;

    z-index: 100;

}

.logo {

    font-size: 22px;

    font-weight: bold;

    color: #073b4c;

}

.nav-links {

    display: flex;

    gap: 10px;

    align-items: center;

}

.nav-links a {

    text-decoration: none;

    color: #073b4c;

    font-weight: bold;

    padding: 10px 14px;

    border-radius: 8px;

    transition: 0.3s;

}

.nav-links a:hover {

    background: #e8f5f7;

}

.logout {

    background: #e63946 !important;

    color: white !important;

}


/* MAIN */

.container {

    width: 94%;

    max-width: 1250px;

    margin: 40px auto;

}


/* TITLE */

.title {

    color: white;

    margin-bottom: 25px;

    animation: fadeDown 0.7s ease;

}

.title h1 {

    font-size: 36px;

    margin-bottom: 8px;

}

.title p {

    opacity: 0.9;

    font-size: 16px;

}


/* ANIMATIONS */

@keyframes fadeDown {

    from {

        opacity: 0;

        transform: translateY(-25px);

    }

    to {

        opacity: 1;

        transform: translateY(0);

    }

}

@keyframes fadeUp {

    from {

        opacity: 0;

        transform: translateY(25px);

    }

    to {

        opacity: 1;

        transform: translateY(0);

    }

}


/* TABLE BOX */

.table-box {

    background: white;

    border-radius: 20px;

    padding: 25px;

    box-shadow:
    0 20px 45px rgba(0,0,0,0.2);

    animation: fadeUp 0.8s ease;

    overflow-x: auto;

}


/* TABLE */

table {

    width: 100%;

    border-collapse: collapse;

    min-width: 1000px;

}

thead {

    background:
    linear-gradient(
        135deg,
        #073b4c,
        #126e82
    );

    color: white;

}

th {

    padding: 15px;

    text-align: left;

    font-size: 14px;

}

td {

    padding: 15px;

    border-bottom: 1px solid #eee;

    font-size: 14px;

}

tbody tr {

    transition: 0.25s;

}

tbody tr:hover {

    background: #f3fafb;

}


/* TRACKING ID */

.tracking-id {

    font-weight: bold;

    color: #126e82;

}


/* STATUS */

.status {

    display: inline-block;

    padding: 7px 12px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;

}


/* BOOKED */

.status-booked {

    background: #e0f2fe;

    color: #0369a1;

}


/* PICKED UP */

.status-picked {

    background: #fef3c7;

    color: #92400e;

}


/* IN TRANSIT */

.status-transit {

    background: #ede9fe;

    color: #6d28d9;

}


/* OUT FOR DELIVERY */

.status-out {

    background: #cffafe;

    color: #155e75;

}


/* DELIVERED */

.status-delivered {

    background: #dcfce7;

    color: #166534;

}


/* CANCELLED */

.status-cancelled {

    background: #fee2e2;

    color: #b91c1c;

}


/* TRACK BUTTON */

.track-btn {

    display: inline-block;

    padding: 9px 14px;

    background:
    linear-gradient(
        135deg,
        #126e82,
        #1f8a70
    );

    color: white;

    text-decoration: none;

    border-radius: 8px;

    font-weight: bold;

    font-size: 13px;

    transition: 0.3s;

}

.track-btn:hover {

    transform: translateY(-2px);

    box-shadow:
    0 5px 12px rgba(0,0,0,0.2);

}


/* EMPTY */

.empty {

    text-align: center;

    padding: 60px 20px;

}

.empty-icon {

    font-size: 60px;

    margin-bottom: 15px;

}

.empty h2 {

    color: #073b4c;

    margin-bottom: 10px;

}

.empty p {

    color: #777;

    margin-bottom: 20px;

}

.book-btn {

    display: inline-block;

    padding: 12px 20px;

    background:
    linear-gradient(
        135deg,
        #126e82,
        #1f8a70
    );

    color: white;

    text-decoration: none;

    border-radius: 9px;

    font-weight: bold;

}


/* FOOTER */

.footer {

    color: white;

    text-align: center;

    margin-top: 40px;

    padding: 20px;

    opacity: 0.8;

}


/* MOBILE */

@media(max-width:700px) {

    .logo {

        font-size: 17px;

    }

    .nav-links a {

        padding: 7px;

        font-size: 12px;

    }

    .title h1 {

        font-size: 28px;

    }

}

</style>

</head>


<body>


<!-- ==================================
     NAVIGATION
================================== -->

<nav>

<div class="logo">

🚚 Smart Logistics | Customer

</div>


<div class="nav-links">

<a href="dashboard.php">
Dashboard
</a>

<a href="new_shipment.php">
📦 Book Shipment
</a>

<a href="track.php">
📍 Track
</a>

<a
    href="../auth/logout.php"
    class="logout"
>
Logout
</a>

</div>

</nav>


<!-- ==================================
     MAIN
================================== -->

<div class="container">


<div class="title">

<h1>
📋 My Shipments
</h1>

<p>
View all your booked shipments and track their current status.
</p>

</div>


<div class="table-box">


<?php if ($result->num_rows > 0): ?>


<table>

<thead>

<tr>

<th>
Tracking ID
</th>

<th>
Sender
</th>

<th>
Receiver
</th>

<th>
Route
</th>

<th>
Package
</th>

<th>
Weight
</th>

<th>
Priority
</th>

<th>
Status
</th>

<th>
ETA
</th>

<th>
Action
</th>

</tr>

</thead>


<tbody>


<?php while ($shipment = $result->fetch_assoc()): ?>


<?php

$status = $shipment["status"];

$status_class = "status-booked";

if ($status === "Picked Up") {

    $status_class = "status-picked";

}
elseif ($status === "In Transit") {

    $status_class = "status-transit";

}
elseif ($status === "Out for Delivery") {

    $status_class = "status-out";

}
elseif ($status === "Delivered") {

    $status_class = "status-delivered";

}
elseif ($status === "Cancelled") {

    $status_class = "status-cancelled";

}

?>


<tr>


<td>

<span class="tracking-id">

<?= htmlspecialchars(
    $shipment["tracking_id"]
) ?>

</span>

</td>


<td>

<?= htmlspecialchars(
    $shipment["sender_name"]
) ?>

</td>


<td>

<?= htmlspecialchars(
    $shipment["receiver_name"]
) ?>

</td>


<td>

<?= htmlspecialchars(
    $shipment["origin"]
) ?>

<br>

↓

<?= htmlspecialchars(
    $shipment["destination"]
) ?>

</td>


<td>

<?= htmlspecialchars(
    $shipment["package_type"]
) ?>

</td>


<td>

<?= htmlspecialchars(
    $shipment["weight"]
) ?>

kg

</td>


<td>

<?= htmlspecialchars(
    $shipment["priority"]
) ?>

</td>


<td>

<span class="status <?= $status_class ?>">

<?= htmlspecialchars($status) ?>

</span>

</td>


<td>

<?php

if (
    $shipment["eta_hours"] !== null &&
    $shipment["eta_hours"] !== ""
) {

    echo htmlspecialchars(
        $shipment["eta_hours"]
    ) . " hrs";

} else {

    echo "Not available";

}

?>

</td>


<td>

<a
    class="track-btn"
    href="track.php?tracking_id=<?= urlencode($shipment["tracking_id"]) ?>"
>

Track →

</a>

</td>


</tr>


<?php endwhile; ?>


</tbody>

</table>


<?php else: ?>


<div class="empty">

<div class="empty-icon">
📦
</div>

<h2>
No Shipments Yet
</h2>

<p>
You haven't booked any shipments.
</p>

<a
    href="new_shipment.php"
    class="book-btn"
>
📦 Book Your First Shipment
</a>

</div>


<?php endif; ?>


</div>


<div class="footer">

<p>
🚚 Smart Logistics
</p>

<p>
Fast • Secure • Reliable
</p>

</div>


</div>


</body>

</html>


<?php

$stmt->close();

$conn->close();

?> 
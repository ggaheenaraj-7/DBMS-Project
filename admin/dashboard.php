<?php

session_start();

require_once "../config/database.php";

/* ==============================
   ADMIN LOGIN CHECK
   ============================== */

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: ../auth/login.php");
    exit();
}


/* ==============================
   GET ADMIN NAME
   ============================== */

$adminName = $_SESSION["name"] ?? "Administrator";


/* ==============================
   SHIPMENT COUNTS
   ============================== */

$totalShipments = 0;
$bookedShipments = 0;
$inTransitShipments = 0;
$deliveredShipments = 0;


/* Total shipments */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM shipments"
);

if ($result) {
    $row = $result->fetch_assoc();
    $totalShipments = (int)$row["total"];
}


/* Booked */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM shipments
     WHERE status = 'Booked'"
);

if ($result) {
    $row = $result->fetch_assoc();
    $bookedShipments = (int)$row["total"];
}


/* In Transit */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM shipments
     WHERE status = 'In Transit'"
);

if ($result) {
    $row = $result->fetch_assoc();
    $inTransitShipments = (int)$row["total"];
}


/* Delivered */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM shipments
     WHERE status = 'Delivered'"
);

if ($result) {
    $row = $result->fetch_assoc();
    $deliveredShipments = (int)$row["total"];
}


/* ==============================
   USER COUNT
   ============================== */

$totalCustomers = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'customer'"
);

if ($result) {
    $row = $result->fetch_assoc();
    $totalCustomers = (int)$row["total"];
}


/* ==============================
   DRIVER COUNT
   ============================== */

$totalDrivers = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM drivers"
);

if ($result) {
    $row = $result->fetch_assoc();
    $totalDrivers = (int)$row["total"];
}


/* ==============================
   VEHICLE COUNT
   ============================== */

$totalVehicles = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM vehicles"
);

if ($result) {
    $row = $result->fetch_assoc();
    $totalVehicles = (int)$row["total"];
}


/* ==============================
   WAREHOUSE COUNT
   ============================== */

$totalWarehouses = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM warehouses"
);

if ($result) {
    $row = $result->fetch_assoc();
    $totalWarehouses = (int)$row["total"];
}


/* ==============================
   RECENT SHIPMENTS
   ============================== */

$recentShipments = $conn->query(
    "SELECT
        id,
        tracking_id,
        sender_name,
        receiver_name,
        origin,
        destination,
        status,
        priority,
        booked_at
     FROM shipments
     ORDER BY booked_at DESC
     LIMIT 8"
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard | Smart Logistics</title>


<style>

/* ==============================
   RESET
   ============================== */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


/* ==============================
   BODY
   ============================== */

body {

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:
        radial-gradient(
            circle at top left,
            #12345b 0%,
            #07111f 35%,
            #020617 100%
        );

    color: #ffffff;

    min-height: 100vh;
}


/* ==============================
   NAVBAR
   ============================== */

.navbar {

    height: 75px;

    padding: 0 6%;

    background:
        rgba(2, 6, 23, 0.92);

    border-bottom:
        1px solid rgba(255,255,255,0.08);

    display: flex;

    align-items: center;

    justify-content: space-between;

    position: sticky;

    top: 0;

    z-index: 1000;

    backdrop-filter: blur(15px);
}


.logo {

    font-size: 25px;

    font-weight: 800;

    letter-spacing: 0.5px;
}


.logo span {

    color: #38bdf8;
}


.nav-links {

    display: flex;

    align-items: center;

    gap: 10px;
}


.nav-links a {

    text-decoration: none;

    color: #cbd5e1;

    padding: 10px 14px;

    border-radius: 10px;

    transition: 0.3s;
}


.nav-links a:hover {

    background: rgba(56,189,248,0.12);

    color: #38bdf8;
}


.logout {

    background: #dc2626 !important;

    color: white !important;
}


.logout:hover {

    background: #b91c1c !important;

    color: white !important;
}


/* ==============================
   CONTAINER
   ============================== */

.container {

    width: 90%;

    max-width: 1400px;

    margin: 0 auto;

    padding: 45px 0 60px;
}


/* ==============================
   WELCOME
   ============================== */

.welcome {

    margin-bottom: 35px;

    animation: fadeDown 0.7s ease;
}


.welcome h1 {

    font-size: 38px;

    margin-bottom: 10px;
}


.welcome h1 span {

    color: #38bdf8;
}


.welcome p {

    color: #94a3b8;

    font-size: 16px;
}


/* ==============================
   STAT GRID
   ============================== */

.stats-grid {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 20px;

    margin-bottom: 35px;
}


/* ==============================
   STAT CARD
   ============================== */

.stat-card {

    background:
        linear-gradient(
            145deg,
            rgba(15,23,42,0.95),
            rgba(15,23,42,0.65)
        );

    border:
        1px solid rgba(255,255,255,0.08);

    border-radius: 20px;

    padding: 25px;

    position: relative;

    overflow: hidden;

    transition:
        transform 0.3s,
        border-color 0.3s;

    animation: fadeUp 0.7s ease;
}


.stat-card::before {

    content: "";

    position: absolute;

    width: 100px;

    height: 100px;

    right: -40px;

    top: -40px;

    background: #38bdf8;

    opacity: 0.08;

    border-radius: 50%;
}


.stat-card:hover {

    transform: translateY(-7px);

    border-color:
        rgba(56,189,248,0.4);
}


.stat-icon {

    font-size: 32px;

    margin-bottom: 15px;
}


.stat-card h3 {

    font-size: 31px;

    margin-bottom: 6px;
}


.stat-card p {

    color: #94a3b8;

    font-size: 14px;
}


/* ==============================
   ACTIONS
   ============================== */

.action-section {

    margin-bottom: 35px;
}


.section-title {

    font-size: 23px;

    margin-bottom: 18px;
}


.action-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 20px;
}


.action-card {

    text-decoration: none;

    color: white;

    padding: 25px;

    border-radius: 18px;

    background:
        rgba(15,23,42,0.85);

    border:
        1px solid rgba(255,255,255,0.08);

    transition: 0.3s;

    animation: fadeUp 0.8s ease;
}


.action-card:hover {

    transform: translateY(-6px);

    border-color: #38bdf8;

    background:
        rgba(15,23,42,1);
}


.action-card .icon {

    font-size: 35px;

    margin-bottom: 14px;
}


.action-card h3 {

    margin-bottom: 8px;
}


.action-card p {

    color: #94a3b8;

    font-size: 14px;

    line-height: 1.6;
}


/* ==============================
   TABLE
   ============================== */

.table-section {

    background:
        rgba(15,23,42,0.85);

    border:
        1px solid rgba(255,255,255,0.08);

    border-radius: 20px;

    overflow: hidden;

    animation: fadeUp 0.9s ease;
}


.table-header {

    padding: 22px 25px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    border-bottom:
        1px solid rgba(255,255,255,0.08);
}


.table-header h2 {

    font-size: 22px;
}


.manage-btn {

    text-decoration: none;

    background: #0284c7;

    color: white;

    padding: 10px 16px;

    border-radius: 9px;

    font-size: 14px;

    transition: 0.3s;
}


.manage-btn:hover {

    background: #0369a1;
}


.table-wrapper {

    width: 100%;

    overflow-x: auto;
}


table {

    width: 100%;

    border-collapse: collapse;

    min-width: 900px;
}


th {

    text-align: left;

    padding: 15px;

    color: #94a3b8;

    font-size: 13px;

    background:
        rgba(2,6,23,0.5);
}


td {

    padding: 16px 15px;

    border-top:
        1px solid rgba(255,255,255,0.06);

    font-size: 14px;
}


tr:hover td {

    background:
        rgba(56,189,248,0.04);
}


/* ==============================
   STATUS
   ============================== */

.status {

    display: inline-block;

    padding: 6px 10px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;
}


.status-booked {

    background: rgba(59,130,246,0.15);

    color: #60a5fa;
}


.status-transit {

    background: rgba(245,158,11,0.15);

    color: #fbbf24;
}


.status-delivered {

    background: rgba(34,197,94,0.15);

    color: #4ade80;
}


.status-other {

    background: rgba(148,163,184,0.15);

    color: #cbd5e1;
}


/* ==============================
   EMPTY
   ============================== */

.empty {

    padding: 40px;

    text-align: center;

    color: #94a3b8;
}


/* ==============================
   ANIMATIONS
   ============================== */

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


@keyframes fadeDown {

    from {

        opacity: 0;

        transform: translateY(-20px);

    }

    to {

        opacity: 1;

        transform: translateY(0);

    }

}


/* ==============================
   RESPONSIVE
   ============================== */

@media(max-width: 1100px) {

    .stats-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media(max-width: 800px) {

    .navbar {

        height: auto;

        padding: 18px 5%;

        flex-direction: column;

        gap: 15px;

    }


    .nav-links {

        flex-wrap: wrap;

        justify-content: center;

    }


    .action-grid {

        grid-template-columns: 1fr;

    }

}


@media(max-width: 550px) {

    .container {

        width: 94%;

    }


    .stats-grid {

        grid-template-columns: 1fr;

    }


    .welcome h1 {

        font-size: 29px;

    }

}

</style>

</head>


<body>


<!-- ==============================
     NAVBAR
     ============================== -->

<nav class="navbar">

    <div class="logo">
        Smart<span>Logistics</span>
    </div>


    <div class="nav-links">

        <a href="dashboard.php">
            🏠 Dashboard
        </a>

        <a href="shipments.php">
            📦 Shipments
        </a>

        <a href="live_tracking.php">
            📍 Live Tracking
        </a>

        <a
            href="../auth/logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

</nav>


<!-- ==============================
     MAIN
     ============================== -->

<main class="container">


    <!-- WELCOME -->

    <section class="welcome">

        <h1>
            Welcome,
            <span>
                <?php echo htmlspecialchars($adminName); ?>
            </span>
            👋
        </h1>

        <p>
            Monitor shipments, customers, logistics resources
            and real-time delivery locations.
        </p>

    </section>


    <!-- ==============================
         STATISTICS
         ============================== -->

    <section class="stats-grid">


        <div class="stat-card">

            <div class="stat-icon">
                📦
            </div>

            <h3>
                <?php echo $totalShipments; ?>
            </h3>

            <p>
                Total Shipments
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                👥
            </div>

            <h3>
                <?php echo $totalCustomers; ?>
            </h3>

            <p>
                Customers
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                🚚
            </div>

            <h3>
                <?php echo $totalDrivers; ?>
            </h3>

            <p>
                Drivers
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                🚘
            </div>

            <h3>
                <?php echo $totalVehicles; ?>
            </h3>

            <p>
                Vehicles
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                🏢
            </div>

            <h3>
                <?php echo $totalWarehouses; ?>
            </h3>

            <p>
                Warehouses
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                📋
            </div>

            <h3>
                <?php echo $bookedShipments; ?>
            </h3>

            <p>
                Booked
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                🚛
            </div>

            <h3>
                <?php echo $inTransitShipments; ?>
            </h3>

            <p>
                In Transit
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                ✅
            </div>

            <h3>
                <?php echo $deliveredShipments; ?>
            </h3>

            <p>
                Delivered
            </p>

        </div>


    </section>


    <!-- ==============================
         QUICK ACTIONS
         ============================== -->

    <section class="action-section">

        <h2 class="section-title">
            Quick Actions
        </h2>


        <div class="action-grid">


            <a
                href="shipments.php"
                class="action-card"
            >

                <div class="icon">
                    📦
                </div>

                <h3>
                    Manage Shipments
                </h3>

                <p>
                    View, search and update shipment
                    delivery statuses.
                </p>

            </a>


            <a
                href="live_tracking.php"
                class="action-card"
            >

                <div class="icon">
                    📍
                </div>

                <h3>
                    Live Location Tracking
                </h3>

                <p>
                    Use GPS to track the current
                    shipment location on a map.
                </p>

            </a>


            <a
                href="../index.php"
                class="action-card"
            >

                <div class="icon">
                    🌐
                </div>

                <h3>
                    View Website
                </h3>

                <p>
                    Open the Smart Logistics
                    public website.
                </p>

            </a>


        </div>

    </section>


    <!-- ==============================
         RECENT SHIPMENTS
         ============================== -->

    <section class="table-section">


        <div class="table-header">

            <h2>
                Recent Shipments
            </h2>


            <a
                href="shipments.php"
                class="manage-btn"
            >
                View All
            </a>

        </div>


        <div class="table-wrapper">


            <?php if ($recentShipments && $recentShipments->num_rows > 0): ?>

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
                            Priority
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Date
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php while ($shipment = $recentShipments->fetch_assoc()): ?>


                    <?php

                    $statusClass = "status-other";

                    if ($shipment["status"] === "Booked") {
                        $statusClass = "status-booked";
                    }

                    if ($shipment["status"] === "In Transit") {
                        $statusClass = "status-transit";
                    }

                    if ($shipment["status"] === "Delivered") {
                        $statusClass = "status-delivered";
                    }

                    ?>


                    <tr>

                        <td>
                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $shipment["tracking_id"]
                                );
                                ?>
                            </strong>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $shipment["sender_name"] ?? "-"
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $shipment["receiver_name"] ?? "-"
                            );
                            ?>
                        </td>


                        <td>

                            <?php
                            echo htmlspecialchars(
                                $shipment["origin"] ?? "-"
                            );
                            ?>

                            →

                            <?php
                            echo htmlspecialchars(
                                $shipment["destination"] ?? "-"
                            );
                            ?>

                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $shipment["priority"] ?? "-"
                            );
                            ?>
                        </td>


                        <td>

                            <span class="status <?php echo $statusClass; ?>">

                                <?php
                                echo htmlspecialchars(
                                    $shipment["status"] ?? "-"
                                );
                                ?>

                            </span>

                        </td>


                        <td>

                            <?php

                            if (!empty($shipment["booked_at"])) {

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $shipment["booked_at"]
                                    )
                                );

                            } else {

                                echo "-";

                            }

                            ?>

                        </td>


                    </tr>


                <?php endwhile; ?>


                </tbody>

            </table>


            <?php else: ?>

                <div class="empty">

                    No shipments available yet.

                </div>

            <?php endif; ?>


        </div>

    </section>


</main>


</body>

</html>
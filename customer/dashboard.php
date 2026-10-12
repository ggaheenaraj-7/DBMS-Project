<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {

    header("Location: ../auth/login.php");
    exit();

}

$customer_id = $_SESSION["user_id"];
$name = $_SESSION["name"];


function getCount($conn, $customer_id, $status = null)
{

    if ($status === null) {

        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS total
             FROM shipments
             WHERE customer_id = ?"
        );

        $stmt->bind_param("i", $customer_id);

    } else {

        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS total
             FROM shipments
             WHERE customer_id = ?
             AND status = ?"
        );

        $stmt->bind_param("is", $customer_id, $status);

    }

    $stmt->execute();

    $result = $stmt->get_result();

    $row = $result->fetch_assoc();

    $stmt->close();

    return $row["total"] ?? 0;
}


$total = getCount($conn, $customer_id);
$booked = getCount($conn, $customer_id, "Booked");
$transit = getCount($conn, $customer_id, "In Transit");
$delivered = getCount($conn, $customer_id, "Delivered");

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Customer Dashboard</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;

    font-family: Arial, sans-serif;

    background:
        radial-gradient(circle at 80% 20%, rgba(14,165,233,.15), transparent 30%),
        #07111f;

    color: white;
}

nav {

    height: 75px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 6%;

    background: rgba(2,6,23,.8);

    border-bottom: 1px solid #1e293b;

    backdrop-filter: blur(15px);
}

.logo {
    font-size: 24px;
    font-weight: bold;
}

.logo span {
    color: #38bdf8;
}

nav a {
    color: #cbd5e1;
    text-decoration: none;
    margin-left: 20px;
}

nav a:hover {
    color: #38bdf8;
}

.logout {
    color: #fca5a5 !important;
}

.container {

    width: 90%;
    max-width: 1200px;

    margin: auto;

    padding: 50px 0;
}

.welcome {
    margin-bottom: 35px;
}

.welcome h1 {
    font-size: 38px;
    margin-bottom: 10px;
}

.welcome p {
    color: #94a3b8;
}

.stats {

    display: grid;

    grid-template-columns: repeat(4,1fr);

    gap: 20px;

    margin-bottom: 40px;
}

.stat {

    padding: 25px;

    border-radius: 18px;

    background: rgba(255,255,255,.04);

    border: 1px solid #1e293b;

    transition: .3s;
}

.stat:hover {
    transform: translateY(-5px);
    border-color: #38bdf8;
}

.stat-icon {
    font-size: 30px;
}

.stat h2 {
    font-size: 35px;
    margin: 12px 0 5px;
}

.stat p {
    color: #94a3b8;
}

.actions {

    display: grid;

    grid-template-columns: repeat(4,1fr);

    gap: 20px;
}

.action {

    padding: 30px;

    text-decoration: none;

    color: white;

    background: linear-gradient(
        145deg,
        rgba(14,165,233,.12),
        rgba(30,41,59,.3)
    );

    border: 1px solid #1e293b;

    border-radius: 18px;

    transition: .35s;
}

.action:hover {

    transform: translateY(-8px);

    border-color: #38bdf8;

    box-shadow: 0 15px 40px rgba(0,0,0,.3);
}

.action-icon {
    font-size: 40px;
    margin-bottom: 15px;
}

.action h3 {
    margin-bottom: 10px;
}

.action p {
    color: #94a3b8;
    line-height: 1.5;
}

.track-box {

    margin-top: 40px;

    padding: 30px;

    border-radius: 20px;

    background: #0b1728;

    border: 1px solid #1e293b;
}

.track-box h2 {
    margin-top: 0;
}

.track-form {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.track-form input {

    flex: 1;

    padding: 14px;

    background: #07111f;

    border: 1px solid #334155;

    color: white;

    border-radius: 10px;
}

.track-form button {

    padding: 14px 25px;

    background: #0284c7;

    border: none;

    color: white;

    border-radius: 10px;

    cursor: pointer;
}

@media(max-width:900px) {

    .stats,
    .actions {
        grid-template-columns: 1fr 1fr;
    }

}

@media(max-width:600px) {

    .stats,
    .actions {
        grid-template-columns: 1fr;
    }

    nav {
        padding: 0 4%;
    }

    nav a {
        margin-left: 8px;
    }

    .track-form {
        flex-direction: column;
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

        <a href="../index.php">Home</a>

        <a href="dashboard.php">Dashboard</a>

        <a href="new_shipment.php">Book Shipment</a>

        <a href="track.php">Track</a>

        <a class="logout" href="../auth/logout.php">
            Logout
        </a>

    </div>

</nav>


<div class="container">

    <div class="welcome">

        <h1>
            Welcome, <?php echo htmlspecialchars($name); ?> 👋
        </h1>

        <p>
            Manage and track your shipments from one place.
        </p>

    </div>


    <div class="stats">

        <div class="stat">

            <div class="stat-icon">📦</div>

            <h2><?php echo $total; ?></h2>

            <p>Total Shipments</p>

        </div>


        <div class="stat">

            <div class="stat-icon">📝</div>

            <h2><?php echo $booked; ?></h2>

            <p>Booked</p>

        </div>


        <div class="stat">

            <div class="stat-icon">🚚</div>

            <h2><?php echo $transit; ?></h2>

            <p>In Transit</p>

        </div>


        <div class="stat">

            <div class="stat-icon">✅</div>

            <h2><?php echo $delivered; ?></h2>

            <p>Delivered</p>

        </div>

    </div>


    <div class="actions">

        <a class="action" href="new_shipment.php">

            <div class="action-icon">📦</div>

            <h3>Book Shipment</h3>

            <p>
                Create a new shipment and get a tracking ID.
            </p>

        </a>


        <a class="action" href="my_shipments.php">

            <div class="action-icon">📋</div>

            <h3>My Shipments</h3>

            <p>
                View all shipments booked by you.
            </p>

        </a>


        <a class="action" href="track.php">

            <div class="action-icon">📍</div>

            <h3>Track Shipment</h3>

            <p>
                Track shipment status and event history.
            </p>

        </a>


        <a class="action" href="../index.php#services">

            <div class="action-icon">💬</div>

            <h3>Support</h3>

            <p>
                Learn more about Smart Logistics services.
            </p>

        </a>

    </div>


    <div class="track-box">

        <h2>Quick Tracking</h2>

        <p>
            Enter your tracking ID to see shipment progress.
        </p>


        <form
            class="track-form"
            action="track.php"
            method="GET"
        >

            <input
                type="text"
                name="tracking_id"
                placeholder="Example: SL20260917ABC123"
                required
            >

            <button type="submit">
                Track
            </button>

        </form>

    </div>

</div>

</body>
</html>
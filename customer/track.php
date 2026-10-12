<?php

session_start();

require_once "../config/database.php";


/* ==============================
   CUSTOMER LOGIN CHECK
   ============================== */

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "customer"
) {

    header("Location: ../auth/login.php");

    exit();

}


$customerId = (int)$_SESSION["user_id"];

$trackingId = trim(
    $_GET["tracking_id"] ?? ""
);

$shipment = null;

$error = "";


/* ==============================
   SEARCH SHIPMENT
   ============================== */

if ($trackingId !== "") {


    $stmt = $conn->prepare(

        "SELECT
            id,
            customer_id,
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
         WHERE tracking_id = ?
         AND customer_id = ?
         LIMIT 1"

    );


    $stmt->bind_param(
        "si",
        $trackingId,
        $customerId
    );


    $stmt->execute();


    $result = $stmt->get_result();


    if ($result->num_rows > 0) {

        $shipment =
            $result->fetch_assoc();

    } else {

        $error =
            "Shipment not found. Please check your tracking ID.";

    }


    $stmt->close();

}


/* ==============================
   SHIPMENT EVENTS
   ============================== */

$events = null;


if ($shipment) {


    $eventStmt = $conn->prepare(

        "SELECT
            id,
            shipment_id,
            status,
            location,
            message,
            event_time
         FROM shipment_events
         WHERE shipment_id = ?
         ORDER BY event_time ASC"

    );


    $eventStmt->bind_param(
        "i",
        $shipment["id"]
    );


    $eventStmt->execute();


    $events =
        $eventStmt->get_result();


    $eventStmt->close();

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">


<title>
Track Shipment | Smart Logistics
</title>


<!-- LEAFLET -->

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>


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

    min-height: 100vh;

    color: white;

    background:

        radial-gradient(
            circle at top left,
            #12345b,
            #07111f 40%,
            #020617
        );

}


/* ==============================
   NAVBAR
   ============================== */

.navbar {

    min-height: 75px;

    padding:
        15px 6%;

    background:
        rgba(2,6,23,0.93);

    border-bottom:
        1px solid
        rgba(255,255,255,0.08);

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    position: sticky;

    top: 0;

    z-index: 1000;

    backdrop-filter: blur(15px);

}


.logo {

    font-size: 25px;

    font-weight: 800;
}


.logo span {

    color: #38bdf8;

}


.nav-links {

    display: flex;

    gap: 8px;

    align-items: center;

}


.nav-links a {

    color: #cbd5e1;

    text-decoration: none;

    padding:
        10px 13px;

    border-radius: 9px;

    transition: 0.3s;

}


.nav-links a:hover {

    color: #38bdf8;

    background:
        rgba(56,189,248,0.1);

}


.logout {

    background: #dc2626 !important;

    color: white !important;

}


/* ==============================
   CONTAINER
   ============================== */

.container {

    width: 90%;

    max-width: 1200px;

    margin: auto;

    padding:
        45px 0 70px;

}


/* ==============================
   PAGE TITLE
   ============================== */

.page-title {

    text-align: center;

    margin-bottom: 35px;

    animation:
        fadeDown 0.7s ease;

}


.page-title h1 {

    font-size: 38px;

    margin-bottom: 10px;

}


.page-title h1 span {

    color: #38bdf8;

}


.page-title p {

    color: #94a3b8;

}


/* ==============================
   SEARCH BOX
   ============================== */

.search-box {

    background:
        rgba(15,23,42,0.9);

    border:
        1px solid
        rgba(255,255,255,0.08);

    border-radius: 20px;

    padding: 25px;

    margin-bottom: 30px;

    animation:
        fadeUp 0.7s ease;

}


.search-box form {

    display: flex;

    gap: 12px;

}


.search-box input {

    flex: 1;

    padding:
        15px 17px;

    border-radius: 10px;

    border:
        1px solid #334155;

    background: #020617;

    color: white;

    outline: none;

    font-size: 15px;

}


.search-box input:focus {

    border-color:
        #38bdf8;

    box-shadow:
        0 0 0 3px
        rgba(56,189,248,0.1);

}


.search-box button {

    border: none;

    padding:
        0 25px;

    border-radius: 10px;

    background:
        #0284c7;

    color: white;

    font-weight: bold;

    cursor: pointer;

    transition: 0.3s;

}


.search-box button:hover {

    background:
        #0369a1;

}


/* ==============================
   ERROR
   ============================== */

.error {

    background:
        rgba(220,38,38,0.12);

    border:
        1px solid
        rgba(248,113,113,0.3);

    color:
        #fca5a5;

    padding: 16px;

    border-radius: 12px;

    margin-bottom: 25px;

}


/* ==============================
   SHIPMENT CARD
   ============================== */

.shipment-card {

    background:
        rgba(15,23,42,0.9);

    border:
        1px solid
        rgba(255,255,255,0.08);

    border-radius: 20px;

    padding: 30px;

    margin-bottom: 30px;

    animation:
        fadeUp 0.8s ease;

}


/* ==============================
   SHIPMENT HEADER
   ============================== */

.shipment-header {

    display: flex;

    justify-content:
        space-between;

    align-items:
        flex-start;

    gap: 20px;

    margin-bottom: 25px;

}


.tracking-id {

    color:
        #38bdf8;

    font-size: 25px;

    font-weight: bold;

}


.tracking-label {

    color:
        #94a3b8;

    font-size: 13px;

    margin-bottom: 6px;

}


/* ==============================
   STATUS
   ============================== */

.status {

    display: inline-block;

    padding:
        9px 14px;

    border-radius:
        30px;

    background:
        rgba(56,189,248,0.12);

    color:
        #38bdf8;

    font-weight: bold;

    font-size: 13px;

}


/* ==============================
   DETAILS GRID
   ============================== */

.details-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 15px;

}


.detail {

    background:
        rgba(2,6,23,0.55);

    border:
        1px solid
        rgba(255,255,255,0.06);

    border-radius: 13px;

    padding: 18px;

}


.detail small {

    display: block;

    color:
        #64748b;

    margin-bottom: 7px;

    font-size: 12px;

}


.detail strong {

    font-size: 15px;

    word-break: break-word;

}


/* ==============================
   LOCATION BOX
   ============================== */

.live-location-box {

    background:
        rgba(15,23,42,0.9);

    border:
        1px solid
        rgba(56,189,248,0.18);

    border-radius: 20px;

    padding: 25px;

    margin-bottom: 30px;

    animation:
        fadeUp 0.9s ease;

}


.live-location-box h2 {

    margin-bottom: 8px;

}


.location-description {

    color:
        #94a3b8;

    margin-bottom: 15px;

}


#locationStatus {

    color:
        #38bdf8;

    font-size: 14px;

    margin-bottom: 15px;

}


#customerMap {

    width: 100%;

    height: 450px;

    border-radius: 15px;

    overflow: hidden;

}


/* ==============================
   COORDINATES
   ============================== */

.coordinates {

    display: flex;

    gap: 20px;

    margin-top: 15px;

    flex-wrap: wrap;

}


.coordinate-box {

    background:
        rgba(2,6,23,0.6);

    padding: 12px 16px;

    border-radius: 10px;

    color:
        #cbd5e1;

    font-size: 13px;

}


/* ==============================
   TIMELINE
   ============================== */

.timeline-card {

    background:
        rgba(15,23,42,0.9);

    border:
        1px solid
        rgba(255,255,255,0.08);

    border-radius: 20px;

    padding: 30px;

    animation:
        fadeUp 1s ease;

}


.timeline-card h2 {

    margin-bottom: 25px;

}


.timeline {

    position: relative;

    padding-left: 35px;

}


.timeline::before {

    content: "";

    position: absolute;

    left: 8px;

    top: 5px;

    bottom: 5px;

    width: 2px;

    background:
        #334155;

}


.timeline-item {

    position: relative;

    padding-bottom: 30px;

}


.timeline-item:last-child {

    padding-bottom: 0;

}


.timeline-dot {

    position: absolute;

    left: -33px;

    top: 2px;

    width: 18px;

    height: 18px;

    border-radius: 50%;

    background:
        #0284c7;

    border:
        4px solid
        #0f172a;

    box-shadow:
        0 0 0 2px
        #0284c7;

}


.timeline-status {

    font-weight: bold;

    color:
        #38bdf8;

    margin-bottom: 5px;

}


.timeline-location {

    color:
        #e2e8f0;

    font-size: 14px;

    margin-bottom: 5px;

}


.timeline-message {

    color:
        #94a3b8;

    font-size: 14px;

    line-height: 1.5;

}


.timeline-time {

    color:
        #64748b;

    font-size: 12px;

    margin-top: 7px;

}


/* ==============================
   NO EVENTS
   ============================== */

.no-events {

    color:
        #94a3b8;

    padding: 20px 0;

}


/* ==============================
   ANIMATION
   ============================== */

@keyframes fadeUp {

    from {

        opacity: 0;

        transform:
            translateY(25px);

    }

    to {

        opacity: 1;

        transform:
            translateY(0);

    }

}


@keyframes fadeDown {

    from {

        opacity: 0;

        transform:
            translateY(-20px);

    }

    to {

        opacity: 1;

        transform:
            translateY(0);

    }

}


/* ==============================
   RESPONSIVE
   ============================== */

@media(max-width: 800px) {

    .navbar {

        flex-direction: column;

        gap: 15px;

    }


    .nav-links {

        flex-wrap: wrap;

        justify-content: center;

    }


    .details-grid {

        grid-template-columns: 1fr;

    }


    .shipment-header {

        flex-direction: column;

    }


    .search-box form {

        flex-direction: column;

    }


    .search-box button {

        height: 48px;

    }


    .page-title h1 {

        font-size: 30px;

    }

}


@media(max-width: 500px) {

    .container {

        width: 94%;

    }


    #customerMap {

        height: 350px;

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


<!-- ==============================
     MAIN
     ============================== -->

<main class="container">


    <!-- TITLE -->

    <section class="page-title">

        <h1>
            Track Your
            <span>Shipment</span>
            📦
        </h1>

        <p>
            Enter your tracking ID to view shipment
            status and live location.
        </p>

    </section>


    <!-- ==============================
         SEARCH
         ============================== -->

    <section class="search-box">

        <form
            method="GET"
            action="track.php"
        >

            <input
                type="text"
                name="tracking_id"
                placeholder="Enter Tracking ID e.g. SL20260917ABC123"
                value="<?php echo htmlspecialchars($trackingId); ?>"
                required
            >

            <button type="submit">
                🔍 Track Shipment
            </button>

        </form>

    </section>


    <!-- ERROR -->

    <?php if ($error !== ""): ?>

        <div class="error">

            ❌
            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <!-- ==============================
         SHIPMENT DETAILS
         ============================== -->

    <?php if ($shipment): ?>


    <section class="shipment-card">


        <div class="shipment-header">


            <div>

                <div class="tracking-label">
                    TRACKING ID
                </div>

                <div class="tracking-id">

                    <?php

                    echo htmlspecialchars(
                        $shipment["tracking_id"]
                    );

                    ?>

                </div>

            </div>


            <div>

                <span class="status">

                    <?php

                    echo htmlspecialchars(
                        $shipment["status"]
                    );

                    ?>

                </span>

            </div>


        </div>


        <div class="details-grid">


            <div class="detail">

                <small>
                    Sender
                </small>

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $shipment["sender_name"] ?? "-"
                    );

                    ?>

                </strong>

            </div>


            <div class="detail">

                <small>
                    Receiver
                </small>

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $shipment["receiver_name"] ?? "-"
                    );

                    ?>

                </strong>

            </div>


            <div class="detail">

                <small>
                    Package Type
                </small>

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $shipment["package_type"] ?? "-"
                    );

                    ?>

                </strong>

            </div>


            <div class="detail">

                <small>
                    Origin
                </small>

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $shipment["origin"] ?? "-"
                    );

                    ?>

                </strong>

            </div>


            <div class="detail">

                <small>
                    Destination
                </small>

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $shipment["destination"] ?? "-"
                    );

                    ?>

                </strong>

            </div>


            <div class="detail">

                <small>
                    Weight
                </small>

                <strong>

                    <?php

                    if (
                        $shipment["weight"] !== null &&
                        $shipment["weight"] !== ""
                    ) {

                        echo htmlspecialchars(
                            $shipment["weight"]
                        );

                        echo " kg";

                    } else {

                        echo "-";

                    }

                    ?>

                </strong>

            </div>


            <div class="detail">

                <small>
                    Priority
                </small>

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $shipment["priority"] ?? "-"
                    );

                    ?>

                </strong>

            </div>


            <div class="detail">

                <small>
                    ETA
                </small>

                <strong>

                    <?php

                    if (
                        $shipment["eta_hours"] !== null &&
                        $shipment["eta_hours"] !== ""
                    ) {

                        echo htmlspecialchars(
                            $shipment["eta_hours"]
                        );

                        echo " hours";

                    } else {

                        echo "Calculating...";

                    }

                    ?>

                </strong>

            </div>


            <div class="detail">

                <small>
                    Carbon Footprint
                </small>

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $shipment["carbon_kg"] ?? "0.00"
                    );

                    ?>

                    kg CO₂

                </strong>

            </div>


        </div>


    </section>


    <!-- ==============================
         LIVE LOCATION
         ============================== -->

    <section class="live-location-box">


        <h2>
            📍 Live Shipment Location
        </h2>


        <p class="location-description">

            The map automatically checks for the latest
            GPS location of your shipment.

        </p>


        <p id="locationStatus">

            🔄 Checking latest location...

        </p>


        <div id="customerMap"></div>


        <div class="coordinates">

            <div class="coordinate-box">

                Latitude:
                <strong id="latitude">
                    --
                </strong>

            </div>


            <div class="coordinate-box">

                Longitude:
                <strong id="longitude">
                    --
                </strong>

            </div>


            <div class="coordinate-box">

                Accuracy:
                <strong id="accuracy">
                    --
                </strong>

            </div>

        </div>


    </section>


    <!-- ==============================
         TIMELINE
         ============================== -->

    <section class="timeline-card">


        <h2>
            📋 Shipment Timeline
        </h2>


        <?php if ($events && $events->num_rows > 0): ?>


        <div class="timeline">


            <?php while ($event = $events->fetch_assoc()): ?>


            <div class="timeline-item">


                <div class="timeline-dot"></div>


                <div class="timeline-status">

                    <?php

                    echo htmlspecialchars(
                        $event["status"]
                    );

                    ?>

                </div>


                <?php if (!empty($event["location"])): ?>

                <div class="timeline-location">

                    📍

                    <?php

                    echo htmlspecialchars(
                        $event["location"]
                    );

                    ?>

                </div>

                <?php endif; ?>


                <?php if (!empty($event["message"])): ?>

                <div class="timeline-message">

                    <?php

                    echo htmlspecialchars(
                        $event["message"]
                    );

                    ?>

                </div>

                <?php endif; ?>


                <div class="timeline-time">

                    🕒

                    <?php

                    echo date(
                        "d M Y, h:i A",
                        strtotime(
                            $event["event_time"]
                        )
                    );

                    ?>

                </div>


            </div>


            <?php endwhile; ?>


        </div>


        <?php else: ?>


            <div class="no-events">

                No shipment events are available yet.

            </div>


        <?php endif; ?>


    </section>


    <?php endif; ?>


</main>


<!-- LEAFLET JS -->

<script
src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
</script>


<script>

/* ==============================
   SHIPMENT ID FROM PHP
   ============================== */

const shipmentId =
    <?php echo $shipment ? (int)$shipment["id"] : 0; ?>;


/* ==============================
   MAP VARIABLES
   ============================== */

let customerMap = null;

let customerMarker = null;


/* ==============================
   INITIALIZE MAP
   ============================== */

if (shipmentId > 0) {


    customerMap =
        L.map("customerMap").setView(

            [20.5937, 78.9629],

            5

        );


    L.tileLayer(

        "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",

        {

            maxZoom: 19,

            attribution:
                "&copy; OpenStreetMap contributors"

        }

    ).addTo(customerMap);


    /* Get location immediately */

    loadLocation();


    /*

       Check again every 5 seconds.

    */

    setInterval(

        loadLocation,

        5000

    );

}


/* ==============================
   GET LATEST LOCATION
   ============================== */

function loadLocation() {


    fetch(

        "../api/get_location.php?shipment_id=" +
        shipmentId

    )


    .then(

        response =>
            response.json()

    )


    .then(

        data => {


            if (!data.success) {


                document.getElementById(
                    "locationStatus"
                ).innerText =
                    "❌ Unable to load shipment location.";

                return;

            }


            if (!data.location_available) {


                document.getElementById(
                    "locationStatus"
                ).innerText =
                    "📍 Location is not available yet. Waiting for GPS update.";

                return;

            }


            const latitude =
                parseFloat(
                    data.latitude
                );


            const longitude =
                parseFloat(
                    data.longitude
                );


            const accuracy =
                parseFloat(
                    data.accuracy
                );


            const position = [

                latitude,

                longitude

            ];


            /* ==========================
               DISPLAY COORDINATES
               ========================== */

            document.getElementById(
                "latitude"
            ).innerText =
                latitude.toFixed(7);


            document.getElementById(
                "longitude"
            ).innerText =
                longitude.toFixed(7);


            document.getElementById(
                "accuracy"
            ).innerText =
                accuracy.toFixed(2) +
                " m";


            /* ==========================
               STATUS
               ========================== */

            document.getElementById(
                "locationStatus"
            ).innerText =

                "🟢 Live location available • Last updated: " +
                data.recorded_at;


            /* ==========================
               MOVE MAP
               ========================== */

            customerMap.setView(

                position,

                16

            );


            /* ==========================
               CREATE / MOVE MARKER
               ========================== */

            if (
                customerMarker === null
            ) {


                customerMarker =

                    L.marker(
                        position
                    )

                    .addTo(
                        customerMap
                    )

                    .bindPopup(
                        "📦 Current Shipment Location"
                    );


                customerMarker.openPopup();


            } else {


                customerMarker.setLatLng(
                    position
                );

            }


        }

    )


    .catch(

        error => {

            console.log(
                "Location error:",
                error
            );


            document.getElementById(
                "locationStatus"
            ).innerText =
                "⚠️ Waiting for location service...";

        }

    );

}

</script>


</body>

</html>
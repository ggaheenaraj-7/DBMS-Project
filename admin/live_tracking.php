<?php

session_start();

require_once "../config/database.php";

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "admin"
) {

    header("Location: ../auth/login.php");
    exit();

}


$shipments = $conn->query(
    "SELECT
        id,
        tracking_id,
        sender_name,
        receiver_name,
        origin,
        destination,
        status
     FROM shipments
     ORDER BY booked_at DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Live Location Tracking</title>


<!-- Leaflet Map -->

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>


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

    align-items: center;
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

    width: 92%;

    max-width: 1200px;

    margin: 40px auto;
}


.controls {

    background: #0f172a;

    padding: 25px;

    border-radius: 18px;

    border: 1px solid #1e293b;

    margin-bottom: 25px;
}


select {

    width: 100%;

    padding: 14px;

    margin: 15px 0;

    background: #07111f;

    color: white;

    border: 1px solid #334155;

    border-radius: 10px;
}


button {

    padding: 13px 25px;

    border: none;

    border-radius: 10px;

    background: #0284c7;

    color: white;

    cursor: pointer;

    font-weight: bold;

    margin-right: 10px;
}


button.stop {

    background: #dc2626;
}


#status {

    margin-top: 15px;

    color: #94a3b8;
}


#map {

    height: 550px;

    width: 100%;

    border-radius: 20px;

    overflow: hidden;

    border: 1px solid #334155;
}


.info {

    display: grid;

    grid-template-columns: repeat(3, 1fr);

    gap: 15px;

    margin-bottom: 25px;
}


.info-card {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 15px;

    padding: 20px;
}


.info-card small {

    display: block;

    color: #94a3b8;

    margin-bottom: 8px;
}


.info-card strong {

    color: #38bdf8;
}


@media(max-width:700px) {

    .info {

        grid-template-columns: 1fr;

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

<a href="dashboard.php">
Dashboard
</a>

<a href="shipments.php">
Shipments
</a>

<a href="../auth/logout.php">
Logout
</a>

</div>

</nav>


<div class="container">


<h1>📍 Live Location Tracking</h1>

<p style="color:#94a3b8;">
Track the current GPS location of a shipment.
</p>


<div class="controls">


<label>
Select Shipment
</label>


<select id="shipment">

<option value="">
-- Select Shipment --
</option>


<?php while ($row = $shipments->fetch_assoc()): ?>

<option
    value="<?php echo $row["id"]; ?>"
>

<?php echo htmlspecialchars($row["tracking_id"]); ?>

-
<?php echo htmlspecialchars($row["origin"]); ?>

→

<?php echo htmlspecialchars($row["destination"]); ?>

</option>

<?php endwhile; ?>

</select>


<button onclick="startTracking()">
📍 Start Location Tracking
</button>


<button
    class="stop"
    onclick="stopTracking()"
>
Stop Tracking
</button>


<div id="status">
Tracking is stopped.
</div>


</div>


<div class="info">

<div class="info-card">

<small>Latitude</small>

<strong id="latitude">
--
</strong>

</div>


<div class="info-card">

<small>Longitude</small>

<strong id="longitude">
--
</strong>

</div>


<div class="info-card">

<small>GPS Accuracy</small>

<strong id="accuracy">
--
</strong>

</div>

</div>


<div id="map"></div>


</div>


<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
</script>


<script>

let map;

let marker;

let watchId = null;


map = L.map("map").setView(
    [20.5937, 78.9629],
    5
);


L.tileLayer(
    "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
    {
        maxZoom: 19,
        attribution: "&copy; OpenStreetMap contributors"
    }
).addTo(map);


function startTracking() {

    const shipmentId =
        document.getElementById("shipment").value;


    if (!shipmentId) {

        alert("Please select a shipment.");

        return;

    }


    if (!navigator.geolocation) {

        alert(
            "Geolocation is not supported by this browser."
        );

        return;

    }


    if (watchId !== null) {

        navigator.geolocation.clearWatch(
            watchId
        );

    }


    document.getElementById("status").innerText =
        "📡 Requesting GPS location...";


    watchId =
        navigator.geolocation.watchPosition(

            position => {

                const latitude =
                    position.coords.latitude;

                const longitude =
                    position.coords.longitude;

                const accuracy =
                    position.coords.accuracy;


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
                    accuracy.toFixed(2) + " meters";


                updateMap(
                    latitude,
                    longitude
                );


                sendLocation(
                    shipmentId,
                    latitude,
                    longitude,
                    accuracy
                );


                document.getElementById(
                    "status"
                ).innerText =
                    "🟢 Live tracking active";


            },

            error => {

                document.getElementById(
                    "status"
                ).innerText =
                    "❌ GPS error: " +
                    error.message;

            },

            {

                enableHighAccuracy: true,

                maximumAge: 5000,

                timeout: 10000

            }

        );

}


function stopTracking() {

    if (watchId !== null) {

        navigator.geolocation.clearWatch(
            watchId
        );

        watchId = null;

    }


    document.getElementById(
        "status"
    ).innerText =
        "⏹️ Tracking stopped.";

}


function updateMap(
    latitude,
    longitude
) {

    const position = [
        latitude,
        longitude
    ];


    map.setView(
        position,
        16
    );


    if (marker === undefined) {

        marker = L.marker(position)
            .addTo(map)
            .bindPopup(
                "📍 Shipment Current Location"
            )
            .openPopup();

    } else {

        marker.setLatLng(position);

    }

}


function sendLocation(
    shipmentId,
    latitude,
    longitude,
    accuracy
) {

    const formData = new FormData();


    formData.append(
        "shipment_id",
        shipmentId
    );


    formData.append(
        "latitude",
        latitude
    );


    formData.append(
        "longitude",
        longitude
    );


    formData.append(
        "accuracy",
        accuracy
    );


    fetch("../api/update_location.php", {

        method: "POST",

        body: formData

    })

    .then(response => response.json())

    .then(data => {

        if (!data.success) {

            console.log(
                data.message
            );

        }

    })

    .catch(error => {

        console.log(
            "Location update error:",
            error
        );

    });

}

</script>


</body>

</html>
<?php

require_once "config/database.php";

$tables = [
    "users",
    "shipments",
    "shipment_events",
    "drivers",
    "vehicles",
    "warehouses",
    "feedback",
    "returns"
];

?>

<!DOCTYPE html>

<html>

<head>

<title>Smart Logistics Check</title>

<style>

body {

    font-family: Arial;

    background: #eef7f8;

    padding: 40px;

}

.box {

    max-width: 800px;

    margin: auto;

    background: white;

    padding: 30px;

    border-radius: 15px;

}

.ok {

    color: green;

    font-weight: bold;

}

.bad {

    color: red;

    font-weight: bold;

}

a {

    display: inline-block;

    margin-top: 20px;

    background: #126e82;

    color: white;

    padding: 12px 20px;

    text-decoration: none;

    border-radius: 8px;

}

</style>

</head>

<body>

<div class="box">

<h1>
🚚 Smart Logistics Health Check
</h1>

<p>
Database:
<b>smart_logistics</b>
</p>


<?php foreach ($tables as $table): ?>


<?php

$safe =
$conn->real_escape_string($table);

$r =
$conn->query(
    "SHOW TABLES LIKE '$safe'"
);

$exists =
$r && $r->num_rows > 0;

?>


<p>

<?= htmlspecialchars($table) ?>

:

<span class="<?= $exists ? 'ok' : 'bad' ?>">

<?= $exists
    ? "✅ Found"
    : "❌ Missing"
?>

</span>

</p>


<?php endforeach; ?>


<a href="auth/login.php">
Open Login
</a>

</div>

</body>

</html>
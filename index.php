<?php
session_start();

$isLoggedIn = isset($_SESSION["user_id"]);
$name = $_SESSION["name"] ?? "";
$role = $_SESSION["role"] ?? "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Smart Logistics</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #07111f;
    color: white;
    overflow-x: hidden;
}

nav {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;

    display: flex;
    justify-content: space-between;
    align-items: center;

    padding: 18px 7%;

    background: rgba(7,17,31,0.85);
    backdrop-filter: blur(15px);

    border-bottom: 1px solid rgba(255,255,255,0.08);
}

.logo {
    font-size: 25px;
    font-weight: bold;
}

.logo span {
    color: #38bdf8;
}

.nav-links {
    display: flex;
    gap: 25px;
    align-items: center;
}

.nav-links a {
    color: white;
    text-decoration: none;
    transition: .3s;
}

.nav-links a:hover {
    color: #38bdf8;
}

.btn {
    display: inline-block;
    padding: 13px 24px;
    border-radius: 30px;

    background: linear-gradient(135deg,#06b6d4,#3b82f6);

    color: white;
    text-decoration: none;
    font-weight: bold;

    transition: .3s;
}

.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(56,189,248,.3);
}

.hero {
    min-height: 100vh;

    display: flex;
    align-items: center;

    padding: 130px 7% 70px;

    background:
        radial-gradient(circle at 80% 30%, rgba(14,165,233,.22), transparent 35%),
        radial-gradient(circle at 20% 70%, rgba(59,130,246,.15), transparent 35%);

    position: relative;
}

.hero-content {
    max-width: 750px;
    animation: fadeUp 1s ease;
}

.badge {
    display: inline-block;

    padding: 8px 15px;
    margin-bottom: 20px;

    border-radius: 30px;

    background: rgba(56,189,248,.1);
    border: 1px solid rgba(56,189,248,.3);

    color: #38bdf8;
}

.hero h1 {
    font-size: clamp(45px,7vw,85px);
    line-height: 1.05;
    margin-bottom: 25px;
}

.hero h1 span {
    color: #38bdf8;
}

.hero p {
    font-size: 20px;
    line-height: 1.7;
    color: #b8c7d9;
    margin-bottom: 35px;
}

.buttons {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.btn-outline {
    display: inline-block;
    padding: 13px 24px;
    border-radius: 30px;

    color: white;
    text-decoration: none;

    border: 1px solid #334155;

    transition: .3s;
}

.btn-outline:hover {
    background: #17253a;
    transform: translateY(-3px);
}

section {
    padding: 90px 7%;
}

.section-title {
    text-align: center;
    font-size: 40px;
    margin-bottom: 50px;
}

.section-title span {
    color: #38bdf8;
}

.cards {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: 25px;
}

.card {
    padding: 35px;

    border-radius: 20px;

    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);

    transition: .4s;
}

.card:hover {
    transform: translateY(-10px);
    border-color: #38bdf8;
    box-shadow: 0 15px 40px rgba(0,0,0,.25);
}

.card-icon {
    font-size: 40px;
    margin-bottom: 20px;
}

.card h3 {
    margin-bottom: 15px;
}

.card p {
    color: #9fb0c5;
    line-height: 1.6;
}

.how {
    background: #0b1728;
}

.steps {
    display: grid;
    grid-template-columns: repeat(4,1fr);
    gap: 20px;
}

.step {
    text-align: center;
    padding: 30px;
}

.number {
    width: 55px;
    height: 55px;

    margin: auto auto 20px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #0ea5e9;

    font-size: 22px;
    font-weight: bold;
}

.cta {
    text-align: center;

    background:
        radial-gradient(circle,#0e7490,transparent 55%),
        #07111f;
}

.cta h2 {
    font-size: 45px;
    margin-bottom: 20px;
}

.cta p {
    color: #b7c5d5;
    margin-bottom: 30px;
}

footer {
    padding: 30px 7%;
    text-align: center;

    border-top: 1px solid rgba(255,255,255,.08);

    color: #8da0b7;
}

@keyframes fadeUp {

    from {
        opacity: 0;
        transform: translateY(40px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }

}

@media(max-width:900px) {

    .cards,
    .steps {
        grid-template-columns: 1fr 1fr;
    }

    .nav-links {
        gap: 10px;
    }

}

@media(max-width:650px) {

    nav {
        padding: 15px 4%;
    }

    .nav-links a:not(.btn) {
        display: none;
    }

    .hero {
        padding-left: 5%;
        padding-right: 5%;
    }

    .cards,
    .steps {
        grid-template-columns: 1fr;
    }

    section {
        padding: 70px 5%;
    }

}

</style>
</head>

<body>

<nav>

    <div class="logo">
        Smart<span>Logistics</span>
    </div>

    <div class="nav-links">

        <a href="#home">Home</a>
        <a href="#services">Services</a>
        <a href="#how">How It Works</a>

        <?php if ($isLoggedIn): ?>

            <?php if ($role === "admin"): ?>
                <a href="admin/dashboard.php">Dashboard</a>
            <?php else: ?>
                <a href="customer/dashboard.php">Dashboard</a>
            <?php endif; ?>

            <a class="btn" href="auth/logout.php">Logout</a>

        <?php else: ?>

            <a class="btn" href="auth/login.php">Login</a>

        <?php endif; ?>

    </div>

</nav>


<section class="hero" id="home">

    <div class="hero-content">

        <div class="badge">
            🚚 Smart Logistics Management
        </div>

        <h1>
            Move Smarter.<br>
            Deliver <span>Faster.</span>
        </h1>

        <p>
            Smart Logistics provides a modern platform for shipment
            booking, real-time tracking, delivery management and
            logistics monitoring.
        </p>

        <div class="buttons">

            <?php if ($isLoggedIn): ?>

                <?php if ($role === "admin"): ?>

                    <a class="btn" href="admin/dashboard.php">
                        Open Admin Dashboard
                    </a>

                <?php else: ?>

                    <a class="btn" href="customer/dashboard.php">
                        Open Customer Dashboard
                    </a>

                <?php endif; ?>

            <?php else: ?>

                <a class="btn" href="auth/login.php">
                    Get Started
                </a>

                <a class="btn-outline" href="#services">
                    Explore Services
                </a>

            <?php endif; ?>

        </div>

    </div>

</section>


<section id="services">

    <h2 class="section-title">
        Our <span>Services</span>
    </h2>

    <div class="cards">

        <div class="card">
            <div class="card-icon">📦</div>
            <h3>Shipment Booking</h3>
            <p>
                Create shipments quickly with sender,
                receiver, package and delivery information.
            </p>
        </div>

        <div class="card">
            <div class="card-icon">📍</div>
            <h3>Shipment Tracking</h3>
            <p>
                Track your shipment status and view
                the complete shipment event timeline.
            </p>
        </div>

        <div class="card">
            <div class="card-icon">🚛</div>
            <h3>Delivery Management</h3>
            <p>
                Administrators can monitor shipments and
                update delivery statuses in real time.
            </p>
        </div>

    </div>

</section>


<section class="how" id="how">

    <h2 class="section-title">
        How It <span>Works</span>
    </h2>

    <div class="steps">

        <div class="step">
            <div class="number">1</div>
            <h3>Login</h3>
            <p>Create or use your customer account.</p>
        </div>

        <div class="step">
            <div class="number">2</div>
            <h3>Book</h3>
            <p>Enter shipment and package details.</p>
        </div>

        <div class="step">
            <div class="number">3</div>
            <h3>Track</h3>
            <p>Monitor your shipment using tracking ID.</p>
        </div>

        <div class="step">
            <div class="number">4</div>
            <h3>Deliver</h3>
            <p>Follow shipment progress until delivery.</p>
        </div>

    </div>

</section>


<section class="cta">

    <h2>Ready to ship?</h2>

    <p>
        Manage your logistics journey with Smart Logistics.
    </p>

    <a href="auth/login.php" class="btn">
        Start Now
    </a>

</section>


<footer>

    © <?php echo date("Y"); ?> Smart Logistics.
    All Rights Reserved.

</footer>

</body>
</html>
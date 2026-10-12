<?php
session_start();

if (isset($_SESSION["user_id"])) {

    if ($_SESSION["role"] === "admin") {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../customer/dashboard.php");
    }

    exit();
}

$error = $_GET["error"] ?? "";
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login | Smart Logistics</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;

    font-family: Arial, sans-serif;

    min-height: 100vh;

    display: flex;
    justify-content: center;
    align-items: center;

    background:
        radial-gradient(circle at 20% 20%, #075985, transparent 35%),
        radial-gradient(circle at 80% 80%, #1e3a8a, transparent 35%),
        #020617;

    color: white;
}

.login-box {

    width: 420px;
    max-width: 92%;

    padding: 40px;

    background: rgba(15,23,42,.88);

    border: 1px solid rgba(255,255,255,.1);

    border-radius: 25px;

    box-shadow: 0 30px 80px rgba(0,0,0,.5);

    animation: show .7s ease;
}

.logo {
    text-align: center;

    font-size: 30px;
    font-weight: bold;

    margin-bottom: 10px;
}

.logo span {
    color: #38bdf8;
}

.subtitle {
    text-align: center;
    color: #94a3b8;
    margin-bottom: 30px;
}

label {
    display: block;
    margin-bottom: 8px;
    color: #cbd5e1;
}

input[type="email"],
input[type="password"] {

    width: 100%;

    padding: 14px;

    margin-bottom: 20px;

    border-radius: 10px;

    border: 1px solid #334155;

    background: #0f172a;

    color: white;

    outline: none;
}

input:focus {
    border-color: #38bdf8;
}

.roles {

    display: grid;
    grid-template-columns: 1fr 1fr;

    gap: 12px;

    margin-bottom: 25px;
}

.role {

    padding: 15px;

    border: 1px solid #334155;

    border-radius: 12px;

    text-align: center;

    cursor: pointer;
}

.role:hover {
    border-color: #38bdf8;
}

.role input {
    margin-right: 7px;
}

button {

    width: 100%;

    padding: 15px;

    border: none;

    border-radius: 12px;

    background: linear-gradient(135deg,#06b6d4,#2563eb);

    color: white;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;

    transition: .3s;
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(14,165,233,.3);
}

.error {

    padding: 12px;

    margin-bottom: 20px;

    border-radius: 10px;

    background: rgba(239,68,68,.15);

    border: 1px solid rgba(239,68,68,.4);

    color: #fca5a5;

}

.back {
    text-align: center;
    margin-top: 25px;
}

.back a {
    color: #38bdf8;
    text-decoration: none;
}

@keyframes show {

    from {
        opacity: 0;
        transform: translateY(30px) scale(.97);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }

}

</style>

</head>

<body>

<div class="login-box">

    <div class="logo">
        Smart<span>Logistics</span>
    </div>

    <div class="subtitle">
        Sign in to continue
    </div>

    <?php if ($error !== ""): ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <form action="login_process.php" method="POST">

        <label>Email</label>

        <input
            type="email"
            name="email"
            placeholder="Enter your email"
            required
        >


        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Enter your password"
            required
        >


        <label>Select Role</label>

        <div class="roles">

            <label class="role">
                <input
                    type="radio"
                    name="role"
                    value="admin"
                    required
                >
                Admin
            </label>

            <label class="role">
                <input
                    type="radio"
                    name="role"
                    value="customer"
                    required
                >
                Customer
            </label>

        </div>


        <button type="submit">
            Login
        </button>

    </form>


    <div class="back">
        <a href="../index.php">← Back to Home</a>
    </div>

</div>

</body>

</html>
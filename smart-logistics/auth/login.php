<?php
session_start(); $error=$_GET["error"]??"";
if(isset($_SESSION["role"])){header("Location: ".($_SESSION["role"]==="admin"?"../admin/dashboard.php":"../customer/dashboard.php"));exit();}
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login | Smart Logistics</title><style>
*{box-sizing:border-box;font-family:Arial,sans-serif}body{margin:0;min-height:100vh;display:grid;place-items:center;background:linear-gradient(135deg,#071e3d,#126e82,#1f8a70);padding:20px}.box{width:100%;max-width:440px;background:#fff;padding:35px;border-radius:22px;box-shadow:0 25px 60px #0004}.logo{text-align:center;color:#073b4c;font-size:27px;font-weight:bold}.sub{text-align:center;color:#777;margin:8px 0 25px}.error{background:#fee2e2;color:#b91c1c;padding:12px;border-radius:8px;margin-bottom:15px}.roles{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:18px}.role{padding:14px;border:2px solid #ddd;border-radius:10px;text-align:center}label{display:block;font-weight:bold;margin:8px 0}input[type=email],input[type=password]{width:100%;padding:13px;border:2px solid #ddd;border-radius:9px}.btn{width:100%;padding:14px;border:0;border-radius:9px;background:linear-gradient(135deg,#126e82,#1f8a70);color:#fff;font-weight:bold;font-size:16px}.home{text-align:center;margin-top:18px}.home a{color:#126e82;text-decoration:none;font-weight:bold}
</style></head><body><div class="box"><div class="logo">🚚 Smart Logistics</div><div class="sub">Secure Login</div>
<?php if($error):?><div class="error"><?=htmlspecialchars($error)?></div><?php endif;?>
<form action="login_process.php" method="POST"><div class="roles"><label class="role">👨‍💼 <input type="radio" name="role" value="admin" required> Admin</label><label class="role">👤 <input type="radio" name="role" value="customer" required> Customer</label></div>
<label>Email</label><input type="email" name="email" required><label>Password</label><input type="password" name="password" required><br><br><button class="btn">LOGIN →</button></form>
<div class="home"><a href="../index.php">← Back to Home</a></div></div></body></html>
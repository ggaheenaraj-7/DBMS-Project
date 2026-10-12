<?php
session_start(); require_once "../config/database.php";
if($_SERVER["REQUEST_METHOD"]!=="POST"){header("Location: login.php");exit();}
$email=trim($_POST["email"]??""); $password=$_POST["password"]??""; $role=$_POST["role"]??"";
if($email===""||$password===""||!in_array($role,["admin","customer"],true)){
 header("Location: login.php?error=".urlencode("Please enter valid login details.")); exit();
}
$stmt=$conn->prepare("SELECT id,name,email,password_hash,role FROM users WHERE email=? AND role=? LIMIT 1");
$stmt->bind_param("ss",$email,$role); $stmt->execute(); $r=$stmt->get_result();
if($r->num_rows!==1){header("Location: login.php?error=".urlencode("User not found or incorrect role."));exit();}
$u=$r->fetch_assoc();
if(!password_verify($password,$u["password_hash"])){header("Location: login.php?error=".urlencode("Incorrect password."));exit();}
session_regenerate_id(true);
$_SESSION["user_id"]=$u["id"]; $_SESSION["name"]=$u["name"]; $_SESSION["email"]=$u["email"]; $_SESSION["role"]=$u["role"];
header("Location: ".($role==="admin"?"../admin/dashboard.php":"../customer/dashboard.php")); exit();
?>
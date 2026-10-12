<?php
require_once "config/database.php";
if($_SERVER["REQUEST_METHOD"]==="POST"){
$name=trim($_POST["name"]??"");$email=trim($_POST["email"]??"");$message=trim($_POST["message"]??"");
if($name!==""&&$email!==""&&$message!==""){
$s=$conn->prepare("INSERT INTO feedback(name,email,message) VALUES(?,?,?)");
if($s){$s->bind_param("sss",$name,$email,$message);$s->execute();$s->close();}
}}
header("Location: index.php?feedback=success#feedback");exit();
?>
<?php
require_once "conexion.php";
session_destroy();
header("Location: index.php");
exit;
?>
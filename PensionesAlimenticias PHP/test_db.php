<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "<h3>Diagnóstico PHP</h3>";

echo "PHP version: " . PHP_VERSION . "<br>";
echo "Loaded php.ini: " . (php_ini_loaded_file() ?: "(none)") . "<br>";

echo "<br><b>PDO drivers:</b><br>";
print_r(PDO::getAvailableDrivers());
echo "<br><br>";

echo "<b>Intentando conectar...</b><br>";

$host = "127.0.0.1";
$port = 3306;
$db   = "pensiones_alimenticias";
$user = "root";
$pass = "sistemas11";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

try {
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
  ]);

  echo "<h2> Conexión OK</h2>";
  $v = $pdo->query("SELECT VERSION()")->fetchColumn();
  echo "Versión: " . $v;

} catch (Throwable $e) {
  echo "<h2>Falló</h2>";
  echo "<pre>" . $e->getMessage() . "</pre>";
}
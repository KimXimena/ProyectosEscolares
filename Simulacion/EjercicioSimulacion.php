<?php
$host = "127.0.0.1";
$port = 3306;
$db   = "test";
$user = "root";
$pass = "sistemas11";

$table = "edificios_escolares";
$colPass = "contrasena";
$passLen = 12;

$pdo = new PDO(
  "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
  $user,
  $pass,
  [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]
);

//Crear columna contraseña
$colExists = $pdo->prepare("
  SELECT COUNT(*) AS c
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = :db
    AND TABLE_NAME = :t
    AND COLUMN_NAME = :c
");
$colExists->execute(["db" => $db, "t" => $table, "c" => $colPass]);
if ((int)$colExists->fetch()["c"] === 0) {
  $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$colPass` VARCHAR(20) NULL");
  echo "Columna `$colPass` creada.\n";
} else {
  echo "Columna `$colPass` ya existe.\n";
}
//generar contraseña aleatoria
function generarPassword(int $len): string {
  $chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789@#";
  $out = "";
  $max = strlen($chars) - 1;
  for ($i = 0; $i < $len; $i++) {
    $out .= $chars[random_int(0, $max)];
  }
  return $out;
}
//traer filas sin contraseña
$rows = $pdo->query("
  SELECT cve_codpost, cve_incr_cp
  FROM `$table`
  WHERE `$colPass` IS NULL OR `$colPass` = ''
")->fetchAll();

echo "Filas a actualizar: " . count($rows) . "\n";
if (count($rows) === 0) exit("Nada que hacer.\n");

//actualizar en transacción
$pdo->beginTransaction();

$upd = $pdo->prepare("
  UPDATE `$table`
  SET `$colPass` = :pwd
  WHERE cve_codpost = :cp AND cve_incr_cp = :inc
");

$usadas = []; //para evitar repetir contraseñas en esta corrida
$actualizadas = 0;

$csvPath = __DIR__ . DIRECTORY_SEPARATOR . "contrasenas_generadas.csv";
$csv = fopen($csvPath, "w");
fputcsv($csv, ["cve_codpost", "cve_incr_cp", "contrasena"]);

foreach ($rows as $r) {
  do {
    $pwd = generarPassword($passLen);
  } while (isset($usadas[$pwd]));
  $usadas[$pwd] = true;

  $upd->execute([
    "pwd" => $pwd,
    "cp"  => $r["cve_codpost"],
    "inc" => $r["cve_incr_cp"]
  ]);

  $actualizadas += $upd->rowCount();
  fputcsv($csv, [$r["cve_codpost"], $r["cve_incr_cp"], $pwd]);
}

fclose($csv);
$pdo->commit();

echo "Actualizadas: $actualizadas\n";
echo "CSV generado: $csvPath\n";
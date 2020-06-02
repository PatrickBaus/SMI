<?php require_once( dirname(__FILE__) . "/../config.php");?>
<?php
// Open Postgres connection
function openConnection($timezone = null) {
  try {
    global $database_host, $username, $password, $database, $database_port;
    $conStr = sprintf("pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
      $database_host, 
      $database_port, 
      $database, 
      $username, 
      $password
    );
    $con = new PDO($conStr);
    $con->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    // Set the timezone for this connection
    $stmt = $con->prepare('SET time_zone = ?');
    if ($timezone === null) {
      $stmt->bindParam(1, date_default_timezone_get(), PDO::PARAM_STR);
    } else {
      $stmt->bindParam(1, $timezone, PDO::PARAM_STR);
    }
    $stmt->execute();

    return $con;
  } catch (PDOException $e) {
    printf("Connect failed: %s" . PHP_EOL, $e->getMessage());
    exit();
  }
}

function closeConnection(&$con) {
  $con = NULL;
}
?>

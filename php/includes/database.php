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
    if ($timezone === null) {
      $con->exec("SET SESSION TIME ZONE '" . date_default_timezone_get() . "';");
    } else {
      $con->exec("SET SESSION TIME ZONE '" . $timezone . "';");
    }

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

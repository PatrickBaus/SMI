<?php require_once( dirname(__FILE__) . "/../config.php");?>
<?php
// Open Postgres connection
function openConnection($timezone = null) {
  try {
    global $database_host, $username, $password, $database, $database_port;
    $conStr = sprintf("pgsql:host=%s;port=%d;dbname=%s;options=\'--client_encoding=UTF8\';user=%s;password=%s", 
      $database_host, 
      $database_port, 
      $database, 
      $username, 
      $password
    );
  	$con = new PDO($conStr);

    // Set the timezone for this connection
    $stmt = $con->prepare('SET time_zone = ?');
    if ($timezone === null) {
      $stmt->bind_param('s', date_default_timezone_get());
    } else {
  	  $stmt->bind_param('s', $timezone);
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

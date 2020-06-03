<?php require_once("../includes/database.php");?>
<?php include("../includes/sql_queries.php");?>
<?php
// Constants
$filter_regex_uid = "/^[123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ]+$/";

//Functions
function addSensor($mysqlCon, $query, $name, $uid, $unit, $callback_period, $node, $room) {
  if (!($stmt = $mysqlCon->prepare($query))) {
    printf('Prepare failed for query "%s": (%d) %s\n', $query, $mysqlCon->errno, $mysqlCon->error);
    exit();
  }
  $stmt->bindParam(1, $uid, PDO::PARAM_STR);
  $stmt->bindParam(2, $name, PDO::PARAM_STR);
  $stmt->bindParam(3, $unit, PDO::PARAM_INT);
  $stmt->bindParam(4, $node, PDO::PARAM_INT);
  $stmt->bindParam(5, $room, PDO::PARAM_INT);
  $stmt->bindParam(6, $callback_period, PDO::PARAM_STR);

  if (!$stmt->execute()) {
    if ($stmt->errno == 1062) {
      echo "Name already exists.";
    } else {
      printf('Execute failed for query "%s": (%d) %s\n', $query, $stmt->errno, $stmt->error);
    }
    exit();
  }

  $id = $stmt->fetchColumn();

  return ("0&id=" . $id);
}

if (! (isset($_POST["name"]) && isset($_POST["uid"]) && isset($_POST["unit"]) && isset($_POST["callback"]) && isset($_POST["room"]) && isset($_POST["node"]))) {
  echo "Invalid arguments.";
        exit();
}

$name = $_POST["name"];
$uid = $_POST["uid"];
$unit = $_POST["unit"];
$callback_period = $_POST["callback"];
$node = $_POST["node"];
$room = $_POST["room"];

// Check the input
if (filter_var($unit, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>1))) === False) {
  echo "Invalid unit.";
  exit();
  }
if (filter_var($callback_period, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>1))) === False) {
  echo "Invalid time: " . $callback_period;
  exit();
}
if (filter_var($node, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>1))) === False) {
  echo "Invalid node.";
  exit();
}
if (filter_var($room, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>1))) === False) {
  echo "Invalid room.";
  exit();

}
if (!preg_match($filter_regex_uid, $uid)) {
  echo "Invalid uid. Make sure it is Base58 encoded.";
  exit();
}

// Open the database connection
$con = openConnection();

// using prepared statements, so no more checking required
echo addSensor($con, $query_sensor["add"], $name, $uid, $unit, $callback_period, $node, $room);

// Close the database connection
closeConnection($con);
?>

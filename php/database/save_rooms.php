<?php require_once("../includes/database.php");?>
<?php require_once("../includes/sql_queries.php");?>
<?php
//Constants
$filter_regex_id = "/^type=(delete|name)&room_id=(\d+)/";

//Functions
function deleteRoom($con, $query_delete, $roomId) {
  if (!($stmt = $con->prepare($query_delete))) {
    printf('Prepare failed for query "%s": (%d) %s\n', $query_delete, $con->errno, $con->error);
    exit();
  }
  $stmt->bindParam(1, $room_id, PDO::PARAM_INT);
  if (!$stmt->execute()) {
    printf('Execute failed for query "%s": (%d) %s\n', $query_delete, $stmt->errno, $stmt->error);
    exit();
  }
  return ($stmt->rowCount() > 0);
}

function updateRoom($con, $query_update, $query_get, $roomId, $update) {
  if (!($stmt = $con->prepare($query_update))) {
    printf('Prepare failed for query "%s": (%d) %s\n', $query_update, $con->errno, $con->error);
    exit();
  };
  $stmt->bindParam(1, $update, PDO::PARAM_STR);
  $stmt->bindParam(2, $roomId, PDO::PARAM_INT);
  
  if (!$stmt->execute()) {
    printf('Execute failed for query "%s": (%d) %s\n', $query_update, $stmt->errno, $stmt->error);
    exit();
  }

  // Get the new value
  if (!($stmt = $con->prepare($query_get))) {
    printf('Prepare failed for query "%s": (%d) %s\n', $query_get, $con->errno, $con->error);
    exit();
  };
  $stmt->bindParam(1, $roomId, PDO::PARAM_INT);
  if (!$stmt->execute()) {
    printf('Execute failed for query "%s": (%d) %s\n', $query_get, $stmt->errno, $stmt->error);
    exit();
  }
  $value = NULL;
  if (!$stmt->execute()) {
    printf('Binding output parameters failed for query "%s": (%d) %s\n', $query_get, $stmt->errno, $stmt->error);
  }

  return $stmt->fetchColumn();
}

if (!isset($_POST["id"])) {
  echo "Invalid arguments.";
  exit();
}

preg_match($filter_regex_id, $_POST["id"], $match);
$command = $match[1];
$roomId = $match[2];

// Checking will be done in the switch statement
$update_value = $_POST["value"];

// Open the database connection
$con = openConnection();

// When adding to this switch command do not forget to add the new cases to $filter_regex
switch ($command) {
  case "delete":
    if (deleteRoom($con, $query_room["delete"], $roomId) != 0) {
      echo "Invalid room.";
    } else {
      echo "0";
    }
    break;
  case "name":
    // using prepared statements, so no more checking required
    if (isset($update_value)) {
      echo updateRoom($con, $query_room["update_name"], $query_room["get_name"], $roomId, $update_value);
    } else {
      echo "Invalid name.";
    }
    break;
}

// Close the database connection
closeConnection($con);
?>

<?php require_once("../includes/database.php");?>
<?php require_once("../includes/sql_queries.php");?>
<?php
//Constants
$filter_regex_id = "/^type=(delete|hostname|label|port)&node_id=(\d+)/";
$filter_url = "/^((([a-z\d]([a-z\d-]*[a-z\d])*)\.)+[a-z]{2,}|((\d{1,3}\.){3}\d{1,3})|(localhost))$/";

//Functions
function deleteNode($con, $query_delete, $nodeId) {
  if (!($stmt = $con->prepare($query_delete))) {
    printf('Prepare failed for query "%s": (%d) %s\n', $query_delete, $con->errno, $con->error);
    exit();
  }
  $stmt->bindParam(1, $nodeId, PDO::PARAM_INT);
  if (!$stmt->execute()) {
    printf('Execute failed for query "%s": (%d) %s\n', $query_delete, $stmt->errno, $stmt->error);
    exit();
  }
  return ($stmt->rowCount() > 0);
}

function updateNode($con, $query_update, $nodeId, $update) {
  if (!($stmt = $con->prepare($query_update))) {
    printf('Prepare failed for query "%s": (%d) %s\n', $query_update, $con->errno, $con->error);
    exit();
  }
  $stmt->bindParam(1, $update, PDO::PARAM_STR);
  $stmt->bindParam(2, $nodeId, PDO::PARAM_INT);

  if (!$stmt->execute()) {
    printf('Execute failed for query "%s": (%d) %s\n', $query_update, $stmt->errno, $stmt->error);
    exit();
  }

  // Get the new value
  return $stmt->fetchColumn();
}

if (!isset($_POST["id"])) {
  echo "Invalid arguments.";
  exit();
}

preg_match($filter_regex_id, $_POST["id"], $match);
$command = $match[1];
$nodeId = $match[2];

// Checking will be done in the switch statement
$update_value = $_POST["value"];

// Open the database connection
$con = openConnection();

// When adding to this switch command do not forget to add the new cases to $filter_regex
switch ($command) {
  case "delete":
    if (!deleteNode($con, $query_node["delete"], $nodeId)) {
      echo "Invalid node.";
    } else {
      echo "0";
    }
    break;
  case "hostname":
    if (preg_match($filter_url, $update_value)) {
      echo updateNode($con, $query_node["update_hostname"], $nodeId, $update_value);
    } else {
      echo "Invalid hostname.";
    }
    break;
  case "label":
    echo updateNode($con, $query_node["update_label"], $nodeId, $update_value);
    break;
  case "port":
    if (filter_var($update_value, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>1, "max_range"=>65535)))) {
      echo updateNode($con, $query_node["update_port"], $nodeId, $update_value);
    } else {
      printf('Invalid Port: "%s"', $update_value);
      break;
    }
}

// Close the database connection
closeConnection($con);
?>

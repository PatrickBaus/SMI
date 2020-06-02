<?php require_once("../includes/database.php");?>
<?php require_once("../includes/sql_queries.php");?>
<?php
//Constants
$filter_regex_id = "/^type=(delete|unit)&unit_id=(\d+)/";

//Functions
function deleteUnit($con, $query_delete, $unit_id) {
  if (!($stmt = $con->prepare($query_delete))) {
    printf('Prepare failed for query "%s": (%d) %s\n', $query_delete, $con->errno, $con->error);
    exit();
  }
  $stmt->bindParam(1, $unit_id, PDO::PARAM_INT);
  if (!$stmt->execute()) {
    printf('Execute failed for query "%s": (%d) %s\n', $query_delete, $stmt->errno, $stmt->error);
    exit();
  }
  return ($stmt->rowCount() > 0);
}

function updateUnit($con, $query_update, $query_get, $unitId, $update) {
	if (!($stmt = $con->prepare($query_update))) {
		printf('Prepare failed for query "%s": (%d) %s\n', $query_update, $con->errno, $con->error);
		exit();
	};
	$stmt->bindParam(1, $update, PDO::PARAM_INT);
	$stmt->bindParam(2, $unitId, PDO::PARAM_INT);
	if (!$stmt->execute()) {
		printf('Execute failed for query "%s": (%d) %s\n', $query_update, $stmt->errno, $stmt->error);
		exit();
	}

	// Get the new value
	if (!($stmt = $con->prepare($query_get))) {
		printf('Prepare failed for query "%s": (%d) %s\n', $query_get, $con->errno, $con->error);
		exit();
	};
	$stmt->bindParam(1, $unitId, PDO::PARAM_INT);
	if (!$stmt->execute()) {
		printf('Execute failed for query "%s": (%d) %s\n', $query_get, $stmt->errno, $stmt->error);
		exit();
	}
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
$unit_id = $match[2];

// Checking will be done in the switch statement
$update_value = $_POST["value"];

// Open the database connection
$con = openConnection();

// When adding to this switch command do not forget to add the new cases to $filter_regex
switch ($command) {
	case "delete":
		if (!deleteUnit($con, $query_unit["delete"], $unit_id)) {
			echo "Invalid unit";
		} else {
			echo "0";
		}
		break;
	case "unit":
		// using prepared statements, so no more checking required
		if (isset($update_value)) {
			echo updateUnit($con, $query_unit["update_unit"], $query_unit["get_unit"], $unit_id, $update_value);
		} else {
			echo "Invalid unit.";
		}
		break;
}

// Close the database connection
closeConnection($con);
?>

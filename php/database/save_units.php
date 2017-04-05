<?php require_once("../includes/database.php");?>
<?php require_once("../includes/sql_queries.php");?>
<?php
//Constants
$filter_regex_id = "/^type=(delete|type|unit)&unit_id=(\d+)/";

//Functions
function deleteUnit($mysqlCon, $query_delete, $unit_id) {
	if (!($stmt = $mysqlCon->prepare($query_delete))) {
		printf('Prepare failed for query "%s": (%d) %s\n', $query_delete, $mysqlCon->errno, $mysqlCon->error);
		exit();
	}
	if (!$stmt->bind_param("i", $unit_id)) {
		printf('Binding parameters failed for query "%s": (%d) %s\n', $query_delete, $stmt->errno, $stmt->error);
		exit();
	}
	if (!$stmt->execute()) {
		printf('Execute failed for query "%s": (%d) %s\n', $query_delete, $stmt->errno, $stmt->error);
		exit();
	}
	$success = ($stmt->affected_rows > 0);
	$stmt->close();
	return ($success ? 0 : 1);
}

function updateUnit($mysqlCon, $query_update, $query_get, $unitId, $update) {
	if (!($stmt = $mysqlCon->prepare($query_update))) {
		printf('Prepare failed for query "%s": (%d) %s\n', $query_update, $mysqlCon->errno, $mysqlCon->error);
		exit();
	};
	$param_type = "si";
	if (!$stmt->bind_param($param_type, $update, $unitId)) {
		printf('Binding parameters failed for query "%s": (%d) %s\n', $query_update, $stmt->errno, $stmt->error);
		exit();
	}
	if (!$stmt->execute()) {
		printf('Execute failed for query "%s": (%d) %s\n', $query_update, $stmt->errno, $stmt->error);
		exit();
	}
	$stmt->close();

	// Get the new value
	if (!($stmt = $mysqlCon->prepare($query_get))) {
		printf('Prepare failed for query "%s": (%d) %s\n', $query_get, $mysqlCon->errno, $mysqlCon->error);
		exit();
	};
	if (!$stmt->bind_param("i", $unitId)) {
		printf('Binding parameters failed for query "%s": (%d) %s\n', $query_get, $stmt->errno, $stmt->error);
		exit();
	}
	if (!$stmt->execute()) {
		printf('Execute failed for query "%s": (%d) %s\n', $query_get, $stmt->errno, $stmt->error);
		exit();
	}
	$name = NULL;
	if (!$stmt->bind_result($name)) {
		printf('Binding output parameters failed for query "%s": (%d) %s\n', $query_get, $stmt->errno, $stmt->error);
	}

	$stmt->fetch();
	$stmt->close();
	return $name;
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
		if (deleteUnit($con, $query_unit["delete"], $unit_id) != 0) {
			echo "Invalid unit";
		} else {
			echo "0";
		}
		break;
	case "type":
		// using prepared statements, so no more checking required
		if (isset($update_value)) {
			echo updateUnit($con, $query_unit["update_type"], $query_unit["get_type"], $unit_id, $update_value);
		} else {
			echo "Invalid type.";
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

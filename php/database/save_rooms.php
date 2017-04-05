<?php require_once("../includes/database.php");?>
<?php require_once("../includes/sql_queries.php");?>
<?php
//Constants
$filter_regex_id = "/^type=(delete|high_threshold|high_unit|low_threshold|low_unit|name|enabled)&room_id=(\d+)/";

//Functions
function deleteRoom($mysqlCon, $query_delete, $roomId) {
	if (!($stmt = $mysqlCon->prepare($query_delete))) {
		printf('Prepare failed for query "%s": (%d) %s\n', $query_delete, $mysqlCon->errno, $mysqlCon->error);
		exit();
	}
	if (!$stmt->bind_param("i", $roomId)) {
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

function updateRoom($mysqlCon, $query_update, $query_get, $roomId, $update) {
	if (!($stmt = $mysqlCon->prepare($query_update))) {
		printf('Prepare failed for query "%s": (%d) %s\n', $query_update, $mysqlCon->errno, $mysqlCon->error);
		exit();
	};
	$param_type = "si";
	if (!$stmt->bind_param($param_type, $update, $roomId)) {
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
	if (!$stmt->bind_param("i", $roomId)) {
		printf('Binding parameters failed for query "%s": (%d) %s\n', $query_get, $stmt->errno, $stmt->error);
		exit();
	}
	if (!$stmt->execute()) {
		printf('Execute failed for query "%s": (%d) %s\n', $query_get, $stmt->errno, $stmt->error);
		exit();
	}
	$value = NULL;
	if (!$stmt->bind_result($value)) {
		printf('Binding output parameters failed for query "%s": (%d) %s\n', $query_get, $stmt->errno, $stmt->error);
	}

	$stmt->fetch();
	$stmt->close();
	return $value;
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
	case "high_threshold":
		if (!(filter_var($update_value, FILTER_VALIDATE_FLOAT) === False)) {
			echo updateRoom($con, $query_room["update_high_threshold"], $query_room["get_high_threshold"], $roomId, $update_value);
		} else {
			echo "Invalid low threshold.";
		}
		break;
	case "high_unit":
		if (filter_var($update_value, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>1)))) {
			echo updateRoom($con, $query_room["update_high_unit"], $query_room["get_high_unit"], $roomId, $update_value);
		} else {
			echo "Invalid low threshold unit.";
		}
		break;
	case "low_threshold":
		if (!(filter_var($update_value, FILTER_VALIDATE_FLOAT) === False)) {
			echo updateRoom($con, $query_room["update_low_threshold"], $query_room["get_low_threshold"], $roomId, $update_value);
		} else {
			echo "Invalid high threshold.";
		}
		break;
	case "low_unit":
		if (filter_var($update_value, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>1)))) {
			echo updateRoom($con, $query_room["update_low_unit"], $query_room["get_low_unit"], $roomId, $update_value);
		} else {
			echo "Invalid high threshold unit.";
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
	case "enabled":
		// use "=== False" syntax, because int(0) is avaluated as false too
		if (!(filter_var($update_value, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>0, "max_range"=>1))) === False)) {
			$result = updateRoom($con, $query_room["update_enabled"], $query_room["get_enabled"], $roomId, $update_value);
			if ($result) {
				echo "enabled";
			} else {
				echo "disabled";
			}
		} else {
			echo "Invalid selection: \"$update_value\"";
		}
		break;
}

// Close the database connection
closeConnection($con);
?>

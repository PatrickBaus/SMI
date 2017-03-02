<?php require_once("../includes/database.php");?>
<?php require_once("../includes/sql_queries.php");?>
<?php
//Constants
$filter_regex_id = "/^type=(callback_period|delete|name|nodes|room|uid|unit|enabled)&sensor_id=(\d+)/";
$filter_regex_uid = "/^[123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ]+$/";

//Functions
function deleteSensor($mysqlCon, $query_delete, $sensor_id) {
	if (!($stmt = $mysqlCon->prepare($query_delete))) {
		printf('Prepare failed for query "%s": (%d) %s\n', $query_delete, $mysqlCon->errno, $mysqlCon->error);
		exit();
	}
	if (!$stmt->bind_param("i", $sensor_id)) {
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

function updateSensor($mysqlCon, $query_update, $query_get, $sensorId, $update) {
	if (!($stmt = $mysqlCon->prepare($query_update))) {
		printf('Prepare failed for query "%s": (%d) %s\n', $query_update, $mysqlCon->errno, $mysqlCon->error);
		exit();
	};
	$param_type = "si";
	if (!$stmt->bind_param($param_type, $update, $sensorId)) {
		printf('Binding parameters failed for query "%s": (%d) %s\n', $query_update, $stmt->errno, $stmt->error);
		exit();
	}
	if (!$stmt->execute()) {
		printf('Execute failed for query "%s": (%d) %s\n', $query_update, $stmt->errno, $stmt->error);
		exit();
	}
	$stmt->close();

	// Get the new node name
	if (!($stmt = $mysqlCon->prepare($query_get))) {
		printf('Prepare failed for query "%s": (%d) %s\n', $query_get, $mysqlCon->errno, $mysqlCon->error);
		exit();
	};
	if (!$stmt->bind_param("i", $sensorId)) {
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
$sensor_id = $match[2];

// Checking will be done in the switch statement
$update_value = $_POST["value"];

// Open the database connection
$con = openConnection();

// When adding to this switch command do not forget to add the new cases to $filter_regex
switch ($command) {
	case "callback_period":
		if (filter_var($update_value, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>1)))) {
			echo updateSensor($con, $query_sensor["update_callback"], $query_sensor["get_callback"], $sensor_id, $update_value);
		} else {
			echo "Invalid time.";
		}
		break;
	case "delete":
		if (deleteSensor($con, $query_sensor["delete"], $sensor_id) != 0) {
			echo "Invalid node.";
		} else {
			echo "0";
		}
		break;
	case "name":
		// using prepared statements, so no more checking required
		if (isset($update_value)) {
			echo updateSensor($con, $query_sensor["update_name"], $query_sensor["get_name"], $sensor_id, $update_value);
		} else {
			echo "Invalid name.";
		}
		break;
	case "nodes":
		if (filter_var($update_value, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>1)))) {
			echo updateSensor($con, $query_sensor["update_node"], $query_sensor["get_node"], $sensor_id, $update_value);
		} else {
			echo "Invalid node.";
		}
		break;
	case "room":
		if (filter_var($update_value, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>1)))) {
			echo updateSensor($con, $query_sensor["update_room"], $query_sensor["get_room"], $sensor_id, $update_value);
		} else {
			echo "Invalid room.";
		}
		break;
	case "uid":
		// Check whether the input is base58 encoded
		if (preg_match($filter_regex_uid, $update_value)) {
			echo updateSensor($con, $query_sensor["update_uid"], $query_sensor["get_uid"], $sensor_id, $update_value);
		} else {
			echo "Invalid uid. Make sure it is Base58 encoded.";
		}
		break;
	case "unit":
		if (filter_var($update_value, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>1)))) {
			echo updateSensor($con, $query_sensor["update_unit"], $query_sensor["get_unit"], $sensor_id, $update_value);
		} else {
			echo "Invalid unit.";
		}
		break;
	case "enabled":
		// use "=== False" syntax, because int(0) is avaluated as false too
		if (!(filter_var($update_value, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>0, "max_range"=>1))) === False)) {
			$result = updateSensor($con, $query_sensor["update_enabled"], $query_sensor["get_enabled"], $sensor_id, $update_value);
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

<?php require_once("../includes/database.php");?>
<?php require_once("../includes/sql_queries.php");?>
<?php
//Constants
$filter_regex_id = "/^type=(delete|name|port)&node_id=(\d+)/";
$filter_url = "/^((([a-z\d]([a-z\d-]*[a-z\d])*)\.)+[a-z]{2,}|((\d{1,3}\.){3}\d{1,3})|(localhost))$/";

//Functions
function deleteNode($mysqlCon, $query_delete, $node_id) {
	if (!($stmt = $mysqlCon->prepare($query_delete))) {
		printf('Prepare failed for query "%s": (%d) %s\n', $query_delete, $mysqlCon->errno, $mysqlCon->error);
		exit();
	}
	if (!$stmt->bind_param("i", $node_id)) {
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

function updateNode($mysqlCon, $query_update, $query_get, $nodeId, $update) {
	if (!($stmt = $mysqlCon->prepare($query_update))) {
		printf('Prepare failed for query "%s": (%d) %s\n', $query_update, $mysqlCon->errno, $mysqlCon->error);
		exit();
	}
	$param_type = "si";
	if (!$stmt->bind_param($param_type, $update, $nodeId)) {
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
	}
	if (!$stmt->bind_param("i", $nodeId)) {
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
$node_id = $match[2];

// Checking will be done in the switch statement
$update_value = $_POST["value"];

// Open the database connection
$con = openConnection();

// When adding to this switch command do not forget to add the new cases to $filter_regex
switch ($command) {
	case "delete":
		if (deleteNode($con, $query_node["delete"], $node_id) != 0) {
			echo "Invalid node.";
		} else {
			echo "0";
		}
		break;
	case "name":
		if (preg_match($filter_url, $update_value)) {
			echo updateNode($con, $query_node["update_name"], $query_node["get_name"], $node_id, $update_value);
		} else {
			echo "Invalid hostname.";
		}
		break;
	case "port":
		if (filter_var($update_value, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>1, "max_range"=>65535)))) {
			echo updateNode($con, $query_node["update_port"], $query_node["get_port"], $node_id, $update_value);
		} else {
			printf('Invalid Port: "%s"', $update_value);
			break;
		}
}

// Close the database connection
closeConnection($con);
?>

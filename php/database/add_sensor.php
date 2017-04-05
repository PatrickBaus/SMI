<?php require_once("../includes/database.php");?>
<?php include("../includes/sql_queries.php");?>
<?php
// Constants
$filter_regex_uid = "/^[123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ]+$/";

//Functions
function addSensor($mysqlCon, $query, $name, $uid, $unit, $callback_period, $node, $room) {
	global $query_get_last_id;

	if (!($stmt = $mysqlCon->prepare($query))) {
		printf('Prepare failed for query "%s": (%d) %s\n', $query, $mysqlCon->errno, $mysqlCon->error);
		exit();
	}
	if (!$stmt->bind_param("ssiiii", $uid, $name, $unit, $node, $room, $callback_period)) {
		printf('Binding parameters failed for query "%s": (%d) %s\n', $query, $stmt->errno, $stmt->error);
		exit();
	}
	if (!$stmt->execute()) {
		if ($stmt->errno == 1062) {
			echo "Name already exists.";
		} else {
			printf('Execute failed for query "%s": (%d) %s\n', $query, $stmt->errno, $stmt->error);
		}
		exit();
	}
	
	$id = "";
	if ($result = $mysqlCon->query($query_get_last_id)) {
		$row = $result->fetch_array(MYSQLI_ASSOC);
	        $id = $row['id'];
		$result->close();
	}

	$success = ($stmt->affected_rows > 0) && !empty($id);
	$stmt->close();
	return ($success ? "0&id=" . $id : 1);
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

<?php require_once("../includes/database.php");?>
<?php include("../includes/sql_queries.php");?>
<?php
//Functions
function addRoom($mysqlCon, $query, $name, $low_threshold, $low_unit, $high_threshold, $high_unit) {
	global $query_get_last_id;

	if (!($stmt = $mysqlCon->prepare($query))) {
		printf('Prepare failed for query "%s": (%d) %s\n', $query, $mysqlCon->errno, $mysqlCon->error);
		exit();
	}
	if (!$stmt->bind_param("didis", $low_threshold, $low_unit, $high_threshold, $high_unit, $name)) {
		printf('Binding parameters failed for query "%s": (%d) %s\n', $query, $stmt->errno, $stmt->error);
		exit();
	}
	if (!$stmt->execute()) {
		if ($stmt->errno == 1062) {
			echo "Room already exists.";
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

if (! (isset($_POST["name"]) && isset($_POST["low_threshold"]) && isset($_POST["low_unit"]) && isset($_POST["high_threshold"]) && isset($_POST["high_unit"]))) {
	echo "Invalid arguments.";
        exit();
}

$name = $_POST["name"];
$low_threshold = $_POST["low_threshold"];
$low_unit = $_POST["low_unit"];
$high_threshold = $_POST["high_threshold"];
$high_unit = $_POST["high_unit"];

// Check the input
if ((filter_var($low_threshold, FILTER_VALIDATE_FLOAT) === False)
	|| (filter_var($low_unit, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>1))) === False)) {
	echo "Invalid low threshold: " . $low_threshold . $low_unit;
	exit();
}
if ((filter_var($high_threshold, FILTER_VALIDATE_FLOAT) === False)
	|| (filter_var($high_unit, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>1))) === False)) {
	echo "Invalid high threshold.";
	exit();
}

// Open the database connection
$con = openConnection();

// using prepared statements, so no more checking required
echo addRoom($con, $query_room["add"], $name, $low_threshold, $low_unit, $high_threshold, $high_unit);

// Close the database connection
closeConnection($con);
?>

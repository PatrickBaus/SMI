<?php require_once("../includes/database.php");?>
<?php include("../includes/sql_queries.php");?>
<?php
//Functions
function addunit($mysqlCon, $query, $type, $unit) {
	global $query_get_last_id;

	if (!($stmt = $mysqlCon->prepare($query))) {
		printf('Prepare failed for query "%s": (%d) %s\n', $query, $mysqlCon->errno, $mysqlCon->error);
		exit();
	}
	if (!$stmt->bind_param("ss", $type, $unit)) {
		printf('Binding parameters failed for query "%s": (%d) %s\n', $query, $stmt->errno, $stmt->error);
		exit();
	}
	if (!$stmt->execute()) {
		if ($stmt->errno == 1062) {
			echo "Unit already exists.";
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

if (! (isset($_POST["type"]) && isset($_POST["unit"]))) {
	echo "Invalid arguments.";
        exit();
}

$type = $_POST["type"];
$unit = $_POST["unit"];

// Open MySQL connection
$con = openConnection();

// using prepared statements, so no more checking required
echo addUnit($con, $query_unit["add"], $type, $unit);

// Close the database connection
closeConnection($con);
?>

<?php require_once("../includes/database.php");?>
<?php include("../includes/sql_queries.php");?>
<?php
//Constants
$filter_url = "/^((([a-z\d]([a-z\d-]*[a-z\d])*)\.)+[a-z]{2,}|((\d{1,3}\.){3}\d{1,3})|(localhost))$/";

//Functions
function addNode($mysqlCon, $query, $name, $port) {
	global $query_get_last_id;

	if (!($stmt = $mysqlCon->prepare($query))) {
		printf('Prepare failed for query "%s": (%d) %s\n', $query, $mysqlCon->errno, $mysqlCon->error);
		exit();
	}
	if (!$stmt->bind_param("si", $name, $port)) {
		printf('Binding parameters failed for query "%s": (%d) %s\n', $query, $stmt->errno, $stmt->error);
		exit();
	}
	if (!$stmt->execute()) {
		if ($stmt->errno == 1062) {
			echo "Node already exists.";
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

if (! (isset($_POST["name"]) && isset($_POST["port"]))) {
	echo "Invalid arguments.";
        exit();
}

$name = $_POST["name"];
$port = $_POST["port"];

// Check the input
if (filter_var($port, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>1, "max_range"=>65535))) === False) {
	echo "Invalid port.";
	exit();
}
if (!preg_match($filter_url, $name)) {
        echo "Invalid name. Please enter a domain hostname or ip address.";
        exit();
}

// Open the database connection
$con = openConnection();

// using prepared statements, so no more checking required
echo addNode($con, $query_node["add"], $name, $port);

// Close the database connection
closeConnection($con);
?>

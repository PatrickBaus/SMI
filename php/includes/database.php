<?php require_once( dirname(__FILE__) . "/../config.php");?>
<?php
// Open MySQL connection
function openConnection($timezone = null) {
	global $database_host, $username, $password, $database, $database_port;
	$con = new mysqli($database_host, $username, $password, $database, $database_port);
	if ($con->connect_errno) {
		printf("Connect failed: (%d) %s" . PHP_EOL, $con->connect_errno, $con->connect_error);
		exit();
	}

	// Change character set to utf8
	if (!$con->set_charset("utf8")) {
		printf('Error loading character set "utf8": %s' . PHP_EOL, $con->error);
		exit();
	}

	// Set the timezone for this connection
	$stmt = $con->prepare('SET time_zone = ?');
	if ($timezone === null) {
		$stmt->bind_param('s', date_default_timezone_get());
	} else {
		$stmt->bind_param('s', $timezone);
	}
	$stmt->execute();

	return $con;
}

function closeConnection($con) {
	mysqli_close($con);
}
?>

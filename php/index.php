<?php require("includes/header.php");?>
<?php require_once("includes/sql_queries.php");?>
<?php require_once("includes/database.php");?>

<?php
// Functions
function createTableRow($name, $uid, $unit, $callback_period, $room, $master_node, $last_update, $last_value) {
        $html = "\t\t" . '<div class="tableRow">' . PHP_EOL;
        $tableRow = "\t\t\t" . ' <div class="tableCell">%s</div>' . PHP_EOL;
        $html .= sprintf($tableRow, $name);
        $tableRow = "\t\t\t" . ' <div class="tableCell">%s</div>' . PHP_EOL;
        $html .= sprintf($tableRow, $uid);
        $tableRow = "\t\t\t" . ' <div class="tableCell">%s</div>' . PHP_EOL;
        $html .= sprintf($tableRow, $unit);
        $tableRow = "\t\t\t" . ' <div class="tableCell">%s</div>' . PHP_EOL;
        $html .= sprintf($tableRow, $callback_period);
        $tableRow = "\t\t\t" . ' <div class="tableCell">%s</div>' . PHP_EOL;
        $html .= sprintf($tableRow, $room);
        $tableRow = "\t\t\t" . ' <div class="tableCell">%s</div>' . PHP_EOL;
        $html .= sprintf($tableRow, $master_node);
        $tableRow = "\t\t\t" . ' <div class="tableCell">%s</div>' . PHP_EOL;
        $html .= sprintf($tableRow, $last_update);
        $tableRow = "\t\t\t" . ' <div class="tableCell">%s</div>' . PHP_EOL;
        $html .= sprintf($tableRow, $last_value);
        $html .= "\t\t</div>" . PHP_EOL;
	return $html;
}

// Open the database connection
$con = openConnection();

//$result = $con->query($query_get_overview);
$result = $con->query($query_overview["get_all"]);

if (!$result) {
	printf("Query failed: %s" . PHP_EOL, mysqli_error($con));
	exit();
}

$sensors = array();
while($row = $result->fetch_assoc()){
    $sensors[$row['id']] = $row;
}

// Collect the latest value for each sensor
// We will first collect all sensors, then we will search for the latest
// update for each sensor. This is faster than using an ORDER BY statement.
foreach($sensors as $sensor) {
	if (!($stmt = $con->prepare($query_overview['get_latest_value']))) {
		printf('Prepare failed for query "%s": (%d) %s\n', $query_overview['get_latest'], $mysqlCon->errno, $mysqlCon->error);
		exit();
	}
	if (!$stmt->bind_param("s", $sensor['id'])) {
		printf('Binding parameters failed for query "%s": (%d) %s\n', $query, $stmt->errno, $stmt->error);
		exit();
	}
	if (!$stmt->execute()) {
		printf('Execute failed for query "%s": (%d) %s\n', $query, $stmt->errno, $stmt->error);
		exit();
	}
	
	$result = $stmt->get_result();
	while($row = $result->fetch_assoc()){
		$sensors[$sensor['id']]['last_update'] = $row['last_update'];
		$sensors[$sensor['id']]['last_value'] = $row['last_value'];
	}
}


echo <<<EOF
	<div class="main">
	<h1>Sensor Overview</h1>
	<div class="table">
		<div class="tableRow">
			<div class="tableHeader">Name</div>
			<div class="tableHeader">Uid</div>
			<div class="tableHeader">Type and Unit</div>
			<div class="tableHeader">Measurement Intervall [ms]</div>
			<div class="tableHeader">Room</div>
			<div class="tableHeader">Master Node</div>
			<div class="tableHeader">Last Update</div>
			<div class="tableHeader">Last Value</div>
		</div>
EOF;

foreach($sensors as $sensor) {
	$last_update = new DateTime($sensor['last_update']);
	$last_update->setTimeZone(new DateTimeZone(date_default_timezone_get()));

	echo createTableRow($sensor['name'], $sensor['uid'], $sensor['type'] . " [" . $sensor['unit'] . "]", $sensor['callback_period'], $sensor['room'], $sensor['master_node'], $last_update->format('H:i:s d-M-Y T'), $sensor['last_value']. " " . $sensor['unit']);
}
echo "</div>" . PHP_EOL;

// Close the database connection
closeConnection($con);
echo "\t</div>";
?>

<?php include("includes/footer.php");?>

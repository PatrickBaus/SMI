<?php require("includes/header.php");?>
<?php require_once("includes/functions.php");?>
<?php require_once("includes/database.php");?>
<?php
// Functions
function createPanelContent($filler, $id, $name) {
	$filler = $filler . "\t";

	$html = $filler . "\t" . '<div class="sensorPanelCheckboxGroup">' . PHP_EOL;
	$tableRow = $filler . "\t\t" . '<input type="checkbox" value="None" name="sensorCheckbox" id="%s"/>' . PHP_EOL;
	$html .= sprintf($tableRow, $id);
	$tableRow = $filler . "\t\t" . '<label for="%s"></label>' . PHP_EOL;
	$html .= sprintf($tableRow, $id);
	$tableRow = $filler . "\t\t" . '<label for="%s">%s</label>' . PHP_EOL;
	$html .= sprintf($tableRow, $id, $name);
	$html .= $filler . "\t" . '</div>' . PHP_EOL;

	return $html;
}

function createTableRow($room_name, $sensors) {
	$filler = "\t\t\t";

	$html = $filler . '<div class="sensorPanel sensorPanelCollapsed">' . PHP_EOL;
	$tableRow = $filler . "\t" . '<div class="sensorPanelHeader">' . PHP_EOL;
	$html .= sprintf($tableRow, $room_name);
	$tableRow = $filler . "\t\t" . '<div class="sensorPanelCheckboxGroup">' . PHP_EOL;
	$html .= sprintf($tableRow, $room_name);
	$tableRow = $filler . "\t\t\t" . '<input type="checkbox" value="None" name="roomCheckbox" id="checkBox%s"/>' . PHP_EOL;
	$html .= sprintf($tableRow, $room_name);
	$tableRow = $filler . "\t\t\t" . '<label for="checkBox%s"></label>' . PHP_EOL;
	$html .= sprintf($tableRow, $room_name);
	$tableRow = $filler . "\t\t\t" . '<label for="checkBox%s">%s (%d)</label>' . PHP_EOL;
	$html .= sprintf($tableRow, $room_name, $room_name, count($sensors));
	$html .= $filler . "\t\t" . '</div>' . PHP_EOL;;
	$html .= $filler . "\t" . "</div>" . PHP_EOL;
	$html .= $filler . "\t" . '<div class="sensorPanelContent">' . PHP_EOL;
	foreach ($sensors as $key => &$value) {
		$html .= createPanelContent($filler . "\t\t", $key, $value);
	}
	unset($key); unset($value); // break the reference with the last element
	$html .= $filler . "\t" . "</div>" . PHP_EOL;
//	$tableRow = $filler ."\t" . '<div class="tableCell"><button id="node_id=%d" class="button deleteButton">Delete</button></div>' . PHP_EOL;
//	$html .= sprintf($tableRow, $id);
	$html .= $filler . "</div>" . PHP_EOL;

	return $html;
}

// Open the database connection
$con = openConnection();

// Extract all available rooms from the database and create a hashtable linking rooms to sensorgrouups
$rooms_list = getRooms($con);
$rooms = array();
foreach ($rooms_list as &$room) {
    $rooms[$room] = array();
}
unset($room); // break the reference with the last element
unset($rooms_list);
krsort($rooms);

// Extract first value's date from database
$result = $con->query($query_export["get_first_date"]);
$firstDate = $result->fetchColumn();

echo <<<EOF
	<script type="text/javascript" src="static/js/jquery.simple-dtpicker.js"></script>
	<link type="text/css" href="static/css/jquery.simple-dtpicker.css" rel="stylesheet" />
	<script type="text/javascript" src="static/js/export.js"></script>
	<div class="main">
		<h1>Export Sensor Data</h1>
		<div id="message_export"></div>
		<form id="download_form" name="downloadForm" action="database/export.php" method="POST">
			<input type="hidden" id="input_startDate" name="startDate" value="">
			<input type="hidden" id="input_endDate" name="endDate" value="">
			<input type="hidden" id="input_outputType" name="outputType" value="">
			<input type="hidden" id="input_sensors" name="sensors" value="">
		</form>
		<div class="table">
			<div class="tableRow">
				<div class="tableHeader">Start Date</div>
				<div class="tableHeader">End Date</div>
				<div class="tableHeader">Export Type</div>
				<div class="tableHeader"></div>
				<div class="tableHeader"></div>
				<div class="tableHeader"></div>
			</div>
			<div class="tableRow">
				<div class="tableCell">
					<input type="text" id="start_date" value="$firstDate">
				</div>
				<div class="tableCell">
					<input type="text" id="end_date" value="">
				</div>
EOF;
$options = array(
	"file_csv" => "File (csv)",
//	"text_csv" => "On Screen (csv)",
);
$tableRow = "\t\t\t\t<div class=\"tableCell\">" . PHP_EOL . "%s" . PHP_EOL . "\t\t\t\t</div>" . PHP_EOL;
echo sprintf($tableRow, generateSelect($options, "", "\t\t\t\t\t", "", "output_type"));
ECHO <<<EOF
				<div class="tableCell">
					<button id="select_all_button" class="button">select all</button>
				</div>
				<div class="tableCell">
					<button id="select_none_button" class="button">select none</button>
				</div>
				<div class="tableCell">
					<button id="export_button" class="button">Export</button>
					<div id="export_loading" style="display:none;"><img src="img/indicator.gif" alt="" /></div>
				</div>
			</div>
		</div>
		<h1>Available Sensors</h1>
		<div id="exportTable" class="table">

EOF;

// Extract all units from the database
$result = $con->query($query_export["get_all"]);
if (!$result) {
	printf("Query failed: %s" . PHP_EOL, mysqli_error($con));
	exit();
}

foreach($result as $row) {
        $rooms[$row['room_name']][$row['id']] = $row['label'];
}

// Close the database connection
closeConnection($con);

// The key of the array $rooms will be $room and the value $sensors
foreach ($rooms as $room => $sensors) {
	if (count($sensors)) {
		echo createTableRow($room, $sensors);
	}
}
unset($room); unset($sensors); // break the reference with the last element
// Close table
echo "\t\t</div>" . PHP_EOL;

// Close main div
echo "\t</div>";
?>

<?php include("includes/footer.php");?>

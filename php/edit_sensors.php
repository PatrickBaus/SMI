<?php require("includes/header.php");?>
<?php require_once("includes/functions.php");?>
<?php require_once("includes/database.php");?>
<?php
// Functions
function createTableRow($id, $name, $uid, $unit, $callback_period, $room, $master_node, $enabled) {
	$filler = "\t\t\t";

	$html = $filler . '<div class="tableRow">' . PHP_EOL;
	$tableRow = $filler . "\t" . ' <div class="tableCell"><div class="edit editableCell" id="type=name&sensor_id=%d">%s</div></div>' . PHP_EOL;
	$html .= sprintf($tableRow, $id, $name);
	$tableRow = $filler . "\t" . ' <div class="tableCell"><div class="edit editableCell" id="type=uid&sensor_id=%d">%s</div></div>' . PHP_EOL;
	$html .= sprintf($tableRow, $id, $uid);
	$tableRow = $filler ."\t" . ' <div class="tableCell"><div class="edit_select_units editableCell" id="type=unit&sensor_id=%d">%s</div></div>' . PHP_EOL;
	$html .= sprintf($tableRow, $id, $unit);
	$tableRow = $filler . "\t" . ' <div class="tableCell"><div class="edit editableCell" id="type=callback_period&sensor_id=%d">%s</div></div>' . PHP_EOL;
	$html .= sprintf($tableRow, $id, $callback_period);
	$tableRow = $filler ."\t" . ' <div class="tableCell"><div class="edit_select_rooms editableCell" id="type=room&sensor_id=%d">%s</div></div>' . PHP_EOL;
	$html .= sprintf($tableRow, $id, $room);
	$tableRow = $filler ."\t" . ' <div class="tableCell"><div class="edit_select_nodes editableCell" id="type=nodes&sensor_id=%d">%s</div></div>' . PHP_EOL;
	$html .= sprintf($tableRow, $id, $master_node);
	$tableRow = $filler ."\t" . ' <div class="tableCell"><div class="edit_select_enabled editableCell %s" id="type=enabled&sensor_id=%d">%s</div></div>' . PHP_EOL;
	if ($enabled) {
		$html .= sprintf($tableRow, "enabled", $id, "enabled");
	} else {
		$html .= sprintf($tableRow, "disabled", $id, "disabled");
	}

	$tableRow = $filler . "\t" . ' <div class="tableCell"><button id="sensor_id=%d" class="button deleteButton">Delete</button></div>' . PHP_EOL;
	$html .= sprintf($tableRow, $id);
	$html .= $filler . "</div>" . PHP_EOL;

	return $html;
}

// Open the database connection
$con = openConnection();

// Extract all sensors from the database
$result = $con->query($query_sensor["get_all"]);

if (!$result) {
	printf("Query failed: %s" . PHP_EOL, mysqli_error($con));
	exit();
}

// Extract all available units from the database
$units = getUnits($con);
$units_json = json_encode($units, JSON_FORCE_OBJECT);

// Extract all available sensor nodes (Tinkerforge brick daemons) from the database
$nodes = getNodes($con);
$nodes_json = json_encode($nodes, JSON_FORCE_OBJECT);

// Extract all available rooms
$rooms = getRooms($con);
$rooms_json = json_encode($rooms, JSON_FORCE_OBJECT);

// All available enabled states
$enabled_json = json_encode(array("0" => "disabled", "1" => "enabled"), JSON_FORCE_OBJECT);

// Extract default callback period from database
$callback_period = getDefaultCallbackPeriod($con, $database);

echo <<<EOF
	<script type="text/javascript" charset="utf-8">var default_callback_period={$callback_period};</script>
	<script src="static/js/edit_sensors.js" type="text/javascript" charset="utf-8"></script>
	<script type="text/javascript" charset="utf-8">
		function AddAllJEditables() {
			addJEditableSelect("edit_select_units", $units_json);
			addJEditableSelect("edit_select_rooms", $rooms_json);
			addJEditableSelect("edit_select_nodes", $nodes_json);
			addJEditableSelect("edit_select_enabled", $enabled_json, updateEnabled);
			addJEditable("edit");
		}
		function updateValue(result, status) {
			return "You selected: " + result + ". ";
		}
		function updateEnabled(value, settings) {
			if (value == "enabled") {
			  $(this).removeClass('disabled').addClass('enabled');
			} else if ( value == "disabled" ) {
			  $(this).removeClass('enabled').addClass('disabled');
			} else {
			  $(this).removeClass('enabled').removeClass('disabled');
			}
		}
		function getUnitFromId(id) {
			var units = $units_json;
			return units[id]
		}
		function getRoomFromId(id) {
			var units = $rooms_json;
			return units[id]
		}
		function getNodeFromId(id) {
			var units = $nodes_json;
			return units[id]
		}

		$(function() {
			AddAllJEditables();
		});
	</script>
	<div class="main">
		<h1>Add Sensor</h1>
		<div id="message_add"></div>
		<div class="table">
			<div class="tableRow">
				<div class="tableCell"><input type="text" id="add_name" placeholder="Any name" class="input_add"></div>
				<div class="tableCell"><input type="text" id="add_uid" placeholder="Uid" class="input_add"></div>

EOF;

$tableRow = "\t\t\t\t<div class=\"tableCell\">" . PHP_EOL . "%s" . PHP_EOL . "\t\t\t\t</div>" . PHP_EOL;
echo sprintf($tableRow, generateSelect($units, "", "\t\t\t\t\t", "select_add", "add_unit"));
echo "\t\t\t\t" . '<div class="tableCell"><input type="text" id="add_callback" placeholder="Time in ms (def: ' . convertTime($callback_period) . ')" class="input_add"></div>' . PHP_EOL;
$tableRow = "\t\t\t\t<div class=\"tableCell\">" . PHP_EOL . "%s" . PHP_EOL . "\t\t\t\t</div>" . PHP_EOL;
echo sprintf($tableRow, generateSelect($rooms, "", "\t\t\t\t\t", "select_add", "add_room"));
$tableRow = "\t\t\t\t<div class=\"tableCell\">" . PHP_EOL . "%s" . PHP_EOL . "\t\t\t\t</div>" . PHP_EOL;
echo sprintf($tableRow, generateSelect($nodes, "", "\t\t\t\t\t", "select_add", "add_node"));

echo <<<EOF
				<div class="tableCell">
					<button id="add_button" class="button addButton">Add</button>
					<div id="export_loading" style="display:none;"><img src="img/indicator.gif" alt="" /></div>
				</div>
			</div>
		</div>
		<h1>Edit Sensors</h1>
		<div id="message_edit"></div>
		<div id="sensorsTable" class="table">
			<div class="tableRow">
				<div class="tableHeader">Name</div>
				<div class="tableHeader">Uid</div>
				<div class="tableHeader">Unit</div>
				<div class="tableHeader">Measurement Intervall [ms]</div>
				<div class="tableHeader">Room</div>
				<div class="tableHeader">Master Node</div>
				<div class="tableHeader">Enabled</div>
				<div class="tableHeader"></div>
			</div>

EOF;

foreach($result as $row) {
	echo createTableRow($row['id'], $row['label'], $row['uid'], $units[$row['unit_id']], $row['callback_period'], $row['room'], $row['master_node'], $row['enabled']);
}

// Close the database connection
closeConnection($con);

// Close table
echo "\t\t</div>" . PHP_EOL;

// Close main div
echo "\t</div>";
?>

<?php include("includes/footer.php");?>

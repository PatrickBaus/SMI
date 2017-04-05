<?php require("includes/header.php");?>
<?php require_once("includes/functions.php");?>
<?php require_once("includes/database.php");?>
<?php
// Functions
function createTableRow($id, $name, $low_temp_threshold, $low_temp_unit, $high_temp_threshold, $high_temp_unit, $enabled) {
	$filler = "\t\t\t";

	$html = $filler . '<div class="tableRow">' . PHP_EOL;
	$tableRow = $filler ."\t" . '<div class="tableCell"><div class="edit editableCell" id="type=name&room_id=%d">%s</div></div>' . PHP_EOL;
	$html .= sprintf($tableRow, $id, $name);
	$tableRow = $filler ."\t" . '<div class="tableCell"><div class="edit editableCell" id="type=low_threshold&room_id=%d">%s</div></div>' . PHP_EOL;
	$html .= sprintf($tableRow, $id, $low_temp_threshold);
	$tableRow = $filler ."\t" . '<div class="tableCell"><div class="edit_select_units editableCell" id="type=low_unit&room_id=%d">%s</div></div>' . PHP_EOL;
        $html .= sprintf($tableRow, $id, $low_temp_unit);
	$tableRow = $filler ."\t" . '<div class="tableCell"><div class="edit editableCell" id="type=high_threshold&room_id=%d">%s</div></div>' . PHP_EOL;
	$html .= sprintf($tableRow, $id, $high_temp_threshold);
	$tableRow = $filler ."\t" . '<div class="tableCell"><div class="edit_select_units editableCell" id="type=high_unit&room_id=%d">%s</div></div>' . PHP_EOL;
        $html .= sprintf($tableRow, $id, $high_temp_unit);
	$tableRow = $filler ."\t" . '<div class="tableCell"><div class="edit_select_enabled editableCell %s" id="type=enabled&room_id=%d">%s</div></div>' . PHP_EOL;
	if ($enabled) {
		$html .= sprintf($tableRow, "enabled", $id, "enabled");
	} else {
		$html .= sprintf($tableRow, "disabled", $id, "disabled");
	}

	$tableRow = $filler ."\t" . '<div class="tableCell"><button id="node_id=%d" class="button deleteButton">Delete</button></div>' . PHP_EOL;
	$html .= sprintf($tableRow, $id);
	$html .= $filler . "</div>" . PHP_EOL;

	return $html;
}

// All available enabled states
$enabled_json = json_encode(array("0" => "disabled", "1" => "enabled"), JSON_FORCE_OBJECT);

// Open the database connection
$con = openConnection();

// Extract all units from the database
$result = $con->query($query_room["get_all"]);
if (!$result) {
	printf("Query failed: %s" . PHP_EOL, mysqli_error($con));
	exit();
}

// Extract all available units from the database
$units = getUnits($con);
$units_json = json_encode($units);

echo <<<EOF
	<script src="static/js/edit_rooms.js" type="text/javascript" charset="utf-8"></script>
	<script type="text/javascript" charset="utf-8">
		function AddAllJEditables() {
			addJEditableSelect("edit_select_units", $units_json);
			addJEditableSelect("edit_select_enabled", $enabled_json, "updateEnabled");
			addJEditable("edit");
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
		$(function() {
			AddAllJEditables();
		});
	</script>
	<div class="main">
		<h1>Add Room</h1>
		<div id="message_add"></div>
		<div class="table">
			<div class="tableRow">
				<div class="tableCell"><input type="text" id="add_name" placeholder="Name" class="input_add"></div>
				<div class="tableCell"><input type="text" id="add_low_temp_value" placeholder="Low Threshold" class="input_add"></div>

EOF;
$tableRow = "\t\t\t\t<div class=\"tableCell\">" . PHP_EOL . "%s" . PHP_EOL . "\t\t\t\t</div>" . PHP_EOL;
echo sprintf($tableRow, generateSelect($units, "", "\t\t\t\t\t", "select_add", "add_low_temp_unit"));
echo "\t\t\t\t" . '<div class="tableCell"><input type="text" id="add_high_temp_value" placeholder="High Threshold" class="input_add"></div>' . PHP_EOL;
$tableRow = "\t\t\t\t<div class=\"tableCell\">" . PHP_EOL ."%s" . PHP_EOL . "\t\t\t\t</div>" . PHP_EOL;
echo sprintf($tableRow, generateSelect($units, "", "\t\t\t\t\t", "select_add", "add_high_temp_unit"));
ECHO <<<EOF
				<div class="tableCell">
					<button id="add_button" class="button addButton">Add</button>
					<div id="add_loading" style="display:none;"><img src="img/indicator.gif" alt="" /></div>
				</div>
			</div>
		</div>
		<h1>Edit Rooms</h1>
		<div id="message_edit"></div>
		<div id="roomsTable" class="table">
			<div class="tableRow">
				<div class="tableHeader">Name</div>
				<div class="tableHeader">Low Temp Warning Value</div>
				<div class="tableHeader">Low Temp Warning Unit</div>
				<div class="tableHeader">High Temp Warning Value</div>
				<div class="tableHeader">High Temp Warning Unit</div>
				<div class="tableHeader">Enabled</div>
				<div class="tableHeader"></div>
			</div>

EOF;

while($row = $result->fetch_array(MYSQLI_ASSOC)) {
	echo createTableRow($row['id'], $row['name'], $row['low_temp_threshold'], $row['low_temp_unit'], $row['high_temp_threshold'], $row['high_temp_unit'], $row['enabled']);
}
$result->close();

// Close the database connection
closeConnection($con);

// Close table
echo "\t\t</div>" . PHP_EOL;

// Close main div
echo "\t</div>";
?>

<?php include("includes/footer.php");?>


<?php require("includes/header.php");?>
<?php require_once("includes/functions.php");?>
<?php require_once("includes/database.php");?>
<?php
// Functions
function createTableRow($id, $unit) {
	$filler = "\t\t\t";

	$html = $filler . '<div class="tableRow">' . PHP_EOL;
	$tableRow = $filler . "\t" . ' <div class="tableCell"><div class="edit editableCell" id="type=unit&unit_id=%d">%s</div></div>' . PHP_EOL;
	$html .= sprintf($tableRow, $id, $unit);
	$tableRow = $filler . "\t" . ' <div class="tableCell"><button id="unit_id=%d" class="button deleteButton">Delete</button></div>' . PHP_EOL;
	$html .= sprintf($tableRow, $id);
	$html .= $filler . "</div>" . PHP_EOL;

	return $html;
}

// Open the database connection
$con = openConnection();

// Extract all units from the database
$result = $con->query($query_unit["get_all"]);
if (!$result) {
	printf("Query failed: %s" . PHP_EOL, mysqli_error($con));
	exit();
}

echo <<<EOF
	<script src="static/js/edit_units.js" type="text/javascript" charset="utf-8"></script>
	<script type="text/javascript" charset="utf-8">
		function AddAllJEditables() {
			addJEditable("edit");
		}
		$(function() {
			AddAllJEditables();
		});
	</script>
	<div class="main">
		<h1>Add Unit</h1>
		<div id="message_add"></div>
		<div class="table">
			<div class="tableRow">
				<div class="tableCell"><input type="text" id="add_unit" placeholder="Unit" class="input_add"></div>
				<div class="tableCell">
					<button id="add_button" class="button addButton">Add</button>
					<div id="add_loading" style="display:none;"><img src="img/indicator.gif" alt="" /></div>
				</div>
			</div>
		</div>
		<h1>Edit Units</h1>
		<div id="message_edit"></div>
		<div id="unitsTable" class="table">
			<div class="tableRow">
				<div class="tableHeader">Unit</div>
				<div class="tableHeader"></div>
			</div>

EOF;

while($row = $result->fetch_array(MYSQLI_ASSOC)) {
	echo createTableRow($row['id'], $row['unit']);
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

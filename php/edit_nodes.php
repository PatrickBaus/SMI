<?php require("includes/header.php");?>
<?php require_once("includes/functions.php");?>
<?php require_once("includes/database.php");?>
<?php
// Functions
function createTableRow($id, $hostname, $label, $port) {
  $filler = "\t\t\t";

  $html = $filler . '<div class="tableRow">' . PHP_EOL;
  $tableRow = $filler . "\t" . '<div class="tableCell"><div class="edit editableCell lowercase" id="type=hostname&node_id=%d">%s</div></div>' . PHP_EOL;
  $html .= sprintf($tableRow, $id, $hostname);
  $tableRow = $filler . "\t" . '<div class="tableCell"><div class="edit editableCell" id="type=label&node_id=%d">%s</div></div>' . PHP_EOL;
  $html .= sprintf($tableRow, $id, $label);
  $tableRow = $filler . "\t" . '<div class="tableCell"><div class="edit editableCell" id="type=port&node_id=%d">%s</div></div>' . PHP_EOL;
  $html .= sprintf($tableRow, $id, $port);
  $tableRow = $filler . "\t" . '<div class="tableCell"><button id="node_id=%d" class="button deleteButton">Delete</button></div>' . PHP_EOL;
  $html .= sprintf($tableRow, $id);
  $html .= $filler . "</div>" . PHP_EOL;

	return $html;
}

// Open the database connection
$con = openConnection();

// Extract all units from the database
$result = $con->query($query_node["get_all"]);
if (!$result) {
  printf("Query failed: %s" . PHP_EOL, mysqli_error($con));
  exit();
}

// Extract the default port number used by the sensor daemons
$default_port = getDefaultDaemonPort($con, $database);

echo <<<EOF
	<script type="text/javascript" charset="utf-8">var default_port={$default_port};</script>
	<script src="static/js/edit_nodes.js" type="text/javascript" charset="utf-8"></script>
	<script type="text/javascript" charset="utf-8">
		function AddAllJEditables() {
			addJEditable("edit:not(.lowercase)");
			addJEditableHostname("edit.lowercase");
		}
		$(function() {
			AddAllJEditables();
		});
	</script>
	<div class="main">
		<h1>Add Tinkerforge Daemon Node</h1>
		<div id="message_add"></div>
		<div class="table">
			<div class="tableRow">
				<div class="tableCell"><input type="text" id="add_hostname" placeholder="DNS name or IP" class="input_add"></div>
				<div class="tableCell"><input type="text" id="add_label" placeholder="Label" class="input_add"></div>
				<div class="tableCell"><input type="text" id="add_port" placeholder="Port (def: {$default_port})" class="input_add"></div>
				<div class="tableCell">
					<button id="add_button" class="button addButton">Add</button>
					<div id="add_loading" style="display:none;"><img src="img/indicator.gif" alt="" /></div>
				</div>
			</div>
		</div>
		<h1>Edit Tinkerforge Daemon Nodes</h1>
		<div id="message_edit"></div>
		<div id="nodesTable" class="table">
			<div class="tableRow">
				<div class="tableHeader">Hostname</div>
				<div class="tableHeader">Label</div>
				<div class="tableHeader">Port</div>
				<div class="tableHeader"></div>
			</div>

EOF;

foreach($result as $row) {
  echo createTableRow($row['id'], $row['hostname'], $row['label'], $row['port']);
}

// Close the database connection
closeConnection($con);

// Close table
echo "\t\t</div>" . PHP_EOL;

// Close main div
echo "\t</div>";
?>

<?php include("includes/footer.php");?>

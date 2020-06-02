<?php include("sql_queries.php");?>
<?php
/**
 *
 * Generates an HTML list box
 *
 * @param array() $options  an array of strings containing the options
 * @param string $name  optional name of the list box. Can be used in post environment.
 * @param string $linePrefix  optional characters to prepend to every line. Can be used to format the HTML source.
 * @param string $classname optional class name(s) of the list box
 * @param string $id optional id tag of the created object
 * @return string
 */
function generateSelect($options = array(), $name = "", $linePrefix = "", $classname = "", $id = "") {
	$html = $linePrefix . "<select " . (($classname != "") ? "class=\"$classname\"" : "") . (($name != "") ? "name=\"$name\"" : "") . (($id != "") ? "id=\"$id\"" : "") .">\n";
        foreach ( $options as $key => $value ) {
                $html .= $linePrefix . "\t<option value=\"$key\">$value</option>\n";
        }
        $html .= $linePrefix . '</select>';
        return $html;
}

function convertTime($time) {
	$days = floor($time / 1000 / 60 / 600 / 24);
	$hours = $time / 1000 / 60 / 60 % 24;
	$mins = $time / 1000 / 60 % 60;
	$secs = $time / 1000 % 60;
	$millisecs = $time % 1000;

	$timeStr = "";
	$timeStr .= ($days > 0 ? "$days d ": "");
	$timeStr .= ($hours > 0 ? "$hours h " : "");
	$timeStr .= ($mins > 0 ? "$mins min " : "");
	$timeStr .= ($secs > 0 ? "$secs s " : "");
	$timeStr .= ($millisecs > 0 ? "$millisecs ms " : "");

	return ($timeStr == null ? "0 ms" : trim($timeStr));
}

// Extract all available units from the database
function getUnits($con) {
	global $query_get_units;
	$units = array();
	$result = $con->query($query_get_units);
	while($row = $result->fetch()) {
		$units[$row['id']] = $row['unit'];
	}
	return $units;
}

// Extract all available sensor nodes (Tinkerforge brick daemons) from the database
function getNodes($con) {
	global $query_get_nodes;
	$nodes = array();
	$result = $con->query($query_get_nodes);
	while($row = $result->fetch()) {
		$nodes[$row['id']] = $row['hostname'];
	}
	return $nodes;
}

// Extract all available rooms
function getRooms($con) {
	global $query_get_rooms;
	$rooms = array();
	$result = $con->query($query_get_rooms);
	while($row = $result->fetch()) {
		$rooms[$row['id']] = ($row['room'] == "" ? "ID: " . $row['id'] : $row['room']);
	}
	return $rooms;
}

// Get default callback period
function getDefaultCallbackPeriod($con, $database) {
	global $query_get_callback_default;
  $stmt = $con->query($query_get_callback_default);
	if (!$stmt) {
		printf('Binding output parameters failed for query "%s": (%d) %s' . PHP_EOL, $query_get_callback_default, $stmt->errno, $stmt->error);
	}

	return $stmt->fetchColumn();
}

// Get the default port number of the sensor nodes
// For Tinkerforge deamons this is 4223.
function getDefaultDaemonPort($con, $database) {
  global $query_get_port_default;
  $stmt = $con->query($query_get_port_default);
  if (!$stmt) {
    printf('Binding output parameters failed for query "%s": (%d) %s' . PHP_EOL, $query_get_callback_default, $stmt->errno, $stmt->error);
  }
  return $stmt->fetchColumn();
}
?>

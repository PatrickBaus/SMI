<?php
// Auxiliary tables
$query_get_units = 'SELECT id, unit FROM sensor_units ORDER BY unit';
$query_get_nodes = "SELECT id, hostname FROM sensor_nodes ORDER BY hostname";
$query_get_rooms = "SELECT id, label AS room FROM rooms ORDER BY room";

// Get last auto increment id inserted
$query_get_last_id = "SELECT LAST_INSERT_ID() AS id";

// Overview
$query_overview = array(
	"get_all" => 'SELECT S.id, S.label, S.sensor_uid AS uid, SU.unit, S.callback_period, R.label as room, SN.hostname AS master_node FROM sensors S, sensor_units SU, sensor_nodes SN, rooms R WHERE S.enabled AND SN.id = S.node_id AND S.unit_id = SU.id AND S.room_id = R.id ORDER BY room, unit, master_node',
	"get_latest_value" => 'SELECT SD.time as last_update, SD.value as last_value FROM sensor_data SD WHERE SD.sensor_id=(?) ORDER BY time DESC LIMIT 1',
);
// Get default callback period
SELECT * FROM INFORMATION_SCHEMA.columns WHERE table_name='sensors' AND column_name='callback_period';
$query_get_callback_default = "SELECT column_default FROM INFORMATION_SCHEMA.columns WHERE table_name='sensors' AND column_name='callback_period'";
// Get the default port number used by the sensor daemons
$query_get_port_default = 'SELECT COLUMN_DEFAULT FROM INFORMATION_SCHEMA.columns WHERE TABLE_SCHEMA=(?) AND TABLE_NAME="sensor_nodes" AND COLUMN_NAME="port"';

// Nodes
$query_node = array(
	"add" => "INSERT INTO sensor_nodes (id, hostname, port) VALUES (NULL, (?), (?))",
	"get_all" => "SELECT SN.id, SN.hostname, SN.port FROM sensor_nodes SN ORDER BY hostname",
	"get_name" => "SELECT hostname FROM sensor_nodes WHERE id=(?)",
	"get_port" => "SELECT port FROM sensor_nodes WHERE id=(?)",
	"delete" => "DELETE FROM sensor_nodes WHERE id=(?)",
	"update_name" => "UPDATE sensor_nodes SET hostname=(?) WHERE id=(?)",
	"update_port" => "UPDATE sensor_nodes SET port=(?) WHERE id=(?)",
);

// Rooms
$query_room = array(
	"add" => 'INSERT INTO floorplan_rooms (id, floor, walls, low_temp_threshold, low_temp_threshold_unit_id, high_temp_threshold, high_temp_threshold_unit_id, comment) VALUES (NULL, "", "", (?), (?), (?), (?), (?))',
	"get_all" => 'SELECT R.id, R.label AS name FROM rooms R ORDER BY name',
	"delete" => "DELETE FROM floorplan_rooms WHERE id=(?)",
	"get_name" => "SELECT comment AS name from floorplan_rooms WHERE id=(?)",
	"update_name" => "UPDATE floorplan_rooms SET comment=(?) WHERE id=(?)",
);

// Sensors
$query_sensor = array(
	"add" => "INSERT INTO sensors (id, sensor_uid, name, unit_id, node_id, room_id, callback_period) VALUES (NULL, (?), (?), (?), (?), (?), (?))",
	"get_all" => "SELECT S.id, S.label, S.sensor_uid AS uid, S.unit_id, S.callback_period, R.label AS room, SN.hostname AS master_node, S.enabled FROM sensors S, sensor_units SU, sensor_nodes SN, rooms R WHERE SN.id = S.node_id AND S.unit_id = SU.id AND S.room_id = R.id ORDER BY enabled DESC, room, unit, master_node",
	"get_name" => "SELECT label FROM sensors WHERE id=(?)",
	"get_callback" => "SELECT callback_period FROM sensors WHERE id=(?)",
	"get_node" => "SELECT SN.hostname FROM sensors S, sensor_nodes SN WHERE S.node_id = SN.id AND S.id=(?)",
	"get_room" => "SELECT R.label FROM sensors S, rooms R WHERE S.room_id = R.id AND S.id=(?)",
	"get_uid" => "SELECT S.sensor_uid FROM sensors S WHERE S.id=(?)",
	"get_enabled" => "SELECT enabled FROM sensors WHERE id=(?)",
	"get_unit" => 'SELECT SU.unit FROM sensors S, sensor_units SU WHERE S.unit_id = SU.id AND S.id=(?)',
	"delete" => "DELETE FROM sensors WHERE id=(?)",
	"update_callback" => "UPDATE sensors SET callback_period=(?) WHERE id=(?)",
	"update_name" => "UPDATE sensors SET label=(?) WHERE id=(?)",
	"update_room" => "UPDATE sensors SET room_id=(?) WHERE id=(?)",
	"update_node" => "UPDATE sensors SET node_id=(?) WHERE id=(?)",
	"update_uid" => "UPDATE sensors SET sensor_uid=(?) WHERE id=(?)",
	"update_unit" => "UPDATE sensors SET unit_id=(?) WHERE id=(?)",
	"update_enabled" => "UPDATE sensors SET enabled=(?) WHERE id=(?)",
);

// Units
$query_unit = array(
  "add" => "INSERT INTO sensor_units (unit) VALUES ((?)) RETURNING id",
	"get_all" => "SELECT SU.id, SU.unit FROM sensor_units SU ORDER BY unit",
	"get_unit" => "SELECT unit FROM sensor_units WHERE id=(?)",
	"delete" => "DELETE FROM sensor_units WHERE id=(?)",
	"update_unit" => "UPDATE sensor_units SET unit=(?) WHERE id=(?)",
);

// Export
$query_export = array(
	"get_all" => "SELECT S.id, S.label, R.label AS room_name FROM sensors S, rooms R WHERE S.room_id = R.id",
	"get_first_date" => "SELECT MIN(SD.time) as date from sensor_data SD",
	"get_data" => 'SELECT SD.sensor_id, SD.time, SD.value, SU.unit FROM sensor_data SD, sensors S, sensor_units SU WHERE time >= ? AND time <= ? AND sensor_id IN (%s) AND S.id=SD.sensor_id AND SU.id=S.unit_id ORDER BY date',
	"get_sensor_info" => "SELECT S.id, S.label, R.label as room FROM sensors S, rooms R WHERE S.id IN (%s) AND S.room_id = R.id ORDER BY room",
);
?>

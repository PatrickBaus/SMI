<?php
// Auxiliary tables
$query_get_units = 'SELECT id, CONCAT(type, " [", unit, "]") AS unit FROM sensor_units';
$query_get_nodes = "SELECT id, hostname FROM sensor_nodes";
$query_get_rooms = "SELECT id, comment AS room FROM floorplan_rooms";

// Get last auto increment id inserted
$query_get_last_id = "SELECT LAST_INSERT_ID() AS id";

// Overview
$query_overview = array(
	"get_all" => 'SELECT S.id, S.name, S.sensor_uid AS uid, SU.type, SU.unit, S.callback_period, SR.comment as room, SN.hostname AS master_node FROM sensors S, sensor_units SU, sensor_nodes SN, floorplan_rooms SR WHERE S.enabled=1 AND SN.id = S.node_id AND S.unit_id = SU.id AND S.room_id = SR.id ORDER BY room, type, master_node',
	"get_latest_value" => 'SELECT SD.date as last_update, SD.value as last_value FROM sensor_data SD WHERE SD.sensor_id=(?) ORDER BY date DESC LIMIT 1',
);
// Get default callback period
$query_get_callback_default = 'SELECT COLUMN_DEFAULT FROM INFORMATION_SCHEMA.columns WHERE TABLE_SCHEMA=(?) AND TABLE_NAME="sensors" AND COLUMN_NAME="callback_period"';
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
	"get_all" => 'SELECT SR.id, SR.comment AS name, SR.low_temp_threshold, CONCAT(SUL.type, " [", SUL.unit, "]") AS low_temp_unit, SR.high_temp_threshold, CONCAT(SUH.type, " [", SUH.unit, "]") AS high_temp_unit, SR.enabled FROM floorplan_rooms SR, sensor_units SUL, sensor_units SUH WHERE SR.low_temp_threshold_unit_id = SUL.id AND SR.high_temp_threshold_unit_id = SUH.id ORDER BY enabled DESC, name',
	"delete" => "DELETE FROM floorplan_rooms WHERE id=(?)",
	"get_high_threshold" => "SELECT ROUND(high_temp_threshold, 2) FROM floorplan_rooms WHERE id=(?)",
	"get_high_unit" => 'SELECT CONCAT(SU.type, " [", SU.unit, "]") AS unit FROM floorplan_rooms SR, sensor_units SU WHERE SR.id=(?) AND SR.high_temp_threshold_unit_id = SU.id',
	"get_low_threshold" => "SELECT ROUND(low_temp_threshold, 2) FROM floorplan_rooms WHERE id=(?)",
	"get_low_unit" => 'SELECT CONCAT(SU.type, " [", SU.unit, "]") AS unit FROM floorplan_rooms SR, sensor_units SU WHERE SR.id=(?) AND SR.low_temp_threshold_unit_id = SU.id',
	"get_name" => "SELECT comment AS name from floorplan_rooms WHERE id=(?)",
	"get_enabled" => "SELECT enabled from floorplan_rooms WHERE id=(?)",
	"update_high_threshold" => "UPDATE floorplan_rooms SET high_temp_threshold=(?) WHERE id=(?)",
	"update_high_unit" => "UPDATE floorplan_rooms SET high_temp_threshold_unit_id=(?) WHERE id=(?)",
	"update_low_threshold" => "UPDATE floorplan_rooms SET low_temp_threshold=(?) WHERE id=(?)",
	"update_low_unit" => "UPDATE floorplan_rooms SET low_temp_threshold_unit_id=(?) WHERE id=(?)",
	"update_name" => "UPDATE floorplan_rooms SET comment=(?) WHERE id=(?)",
	"update_enabled" => "UPDATE floorplan_rooms SET enabled=(?) WHERE id=(?)",
);

// Sensors
$query_sensor = array(
	"add" => "INSERT INTO sensors (id, sensor_uid, name, unit_id, node_id, room_id, callback_period) VALUES (NULL, (?), (?), (?), (?), (?), (?))",
	"get_all" => "SELECT S.id, S.name, S.sensor_uid AS uid, SU.type, S.unit_id, S.callback_period, SR.comment AS room, SN.hostname AS master_node, S.enabled FROM sensors S, sensor_units SU, sensor_nodes SN, floorplan_rooms SR WHERE SN.id = S.node_id AND S.unit_id = SU.id AND S.room_id = SR.id ORDER BY enabled DESC, room, type, master_node",
	"get_name" => "SELECT name FROM sensors WHERE id=(?)",
	"get_callback" => "SELECT callback_period FROM sensors WHERE id=(?)",
	"get_node" => "SELECT SN.hostname FROM sensors S, sensor_nodes SN WHERE S.node_id = SN.id AND S.id=(?)",
	"get_room" => "SELECT SR.comment FROM sensors S, floorplan_rooms SR WHERE S.room_id = SR.id AND S.id=(?)",
	"get_uid" => "SELECT S.sensor_uid FROM sensors S WHERE S.id=(?)",
	"get_enabled" => "SELECT S.enabled FROM sensors S WHERE S.id=(?)",
	"get_unit" => 'SELECT CONCAT(SU.type, " ", "[", SU.unit, "]")  FROM sensors S, sensor_units SU WHERE S.unit_id = SU.id AND S.id=(?)',
	"delete" => "DELETE FROM sensors WHERE id=(?)",
	"update_callback" => "UPDATE sensors SET callback_period=(?) WHERE id=(?)",
	"update_name" => "UPDATE sensors SET name=(?) WHERE id=(?)",
	"update_room" => "UPDATE sensors SET room_id=(?) WHERE id=(?)",
	"update_node" => "UPDATE sensors SET node_id=(?) WHERE id=(?)",
	"update_uid" => "UPDATE sensors SET sensor_uid=(?) WHERE id=(?)",
	"update_unit" => "UPDATE sensors SET unit_id=(?) WHERE id=(?)",
	"update_enabled" => "UPDATE sensors SET enabled=(?) WHERE id=(?)",
);

// Units
$query_unit = array(
	"add" => "INSERT INTO sensor_units (id, type, unit) VALUES (NULL, (?), (?))",
	"get_all" => "SELECT SU.id, SU.type, SU.unit FROM sensor_units SU ORDER BY type, unit",
	"get_type" => "SELECT type FROM sensor_units WHERE id=(?)",
	"get_unit" => "SELECT unit FROM sensor_units WHERE id=(?)",
	"delete" => "DELETE FROM sensor_units WHERE id=(?)",
	"update_type" => "UPDATE sensor_units SET type=(?) WHERE id=(?)",
	"update_unit" => "UPDATE sensor_units SET unit=(?) WHERE id=(?)",
);

// Export
$query_export = array(
	"get_all" => "SELECT S.id, S.name, SR.comment AS room_name FROM sensors S, floorplan_rooms SR WHERE S.room_id = SR.id",
	"get_first_date" => "SELECT MIN(SD.date) as date from sensor_data SD",
	"get_data" => 'SELECT SD.sensor_id, SD.date, SD.value, SU.unit FROM sensor_data SD, sensors S, sensor_units SU WHERE date >= ? AND date <= ? AND sensor_id IN (%s) AND S.id=SD.sensor_id AND SU.id=S.unit_id ORDER BY date',
	"get_sensor_info" => "SELECT S.id, S.name, SR.comment as room FROM sensors S, floorplan_rooms SR WHERE S.id IN (%s) AND S.room_id = SR.id ORDER BY room",
);
?>

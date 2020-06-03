<?php header('Content-type: text/html; charset=utf-8'); ?>
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>SMI: Sensor Management Interface</title>
	<meta http-equiv="description" content="page description" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="static/css/smi.css" media="screen" />
	<script src="static/js/jquery.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="static/js/jquery.jeditable.mini.js" type="text/javascript" charset="utf-8"></script>
</head>

<body>
<?php
function checkPermissions() {
	clearstatcache();
	$perms = fileperms("config.php") & 511;
	if(($perms & 7) > 0) {
		$html = "\t" . '<div class="errorBox">';
		$html .= 'Error: "config.php" file permission set to world readable or worse. Please change this to at least "640" before running this script. Current permissions: ' . decoct( $perms );
		$html .= "</div>\n";
		echo $html;
		include("includes/footer.php");
		exit;
	}
}

checkPermissions();
?>
	<div class="navbar">
		<nav>
			<div class="menu">
				<a href="index.php">Overview</a>
				<a href="edit_sensors.php">Sensors</a>
				<a href="edit_units.php">Units</a>
				<a href="edit_nodes.php">Nodes</a>
				<a href="edit_rooms.php">Rooms</a>
				<a href="export.php">Export Data</a>
				<a href="#">Help</a>
			</div>
		</nav>
	</div>

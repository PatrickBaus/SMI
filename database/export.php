<?php require_once("../includes/database.php");?>
<?php require_once("../includes/sql_queries.php");?>
<?php require_once("../includes/constants.php");?>
<?php

//Constants
$filter_regex_date = "/^20\d\d[- \/.](0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01]) ([01][0-9]|2[0123]):([0-5][0-9])$/";
$filter_regex_type = "/^(file_csv|text_csv)$/";

//Functions
function getInfo($mysqlCon, $query_get, $sensor_ids) {
	$query_get = sprintf($query_get, implode(',', $sensor_ids));
	$returnValue = "";
	if (!($result = $mysqlCon->query($query_get))) {
		printf('Execute failed for query "%s": (%d) %s', $query_get, $mysqlCon->connect_errno, $mysqlCon->connect_error);
		exit();
	}
	while($row = $result->fetch_assoc()) {
		$returnValue .= sprintf("# %d, %s, %s\n", $row["id"], $row["room"], $row["name"]);
	}
	return $returnValue;
}

function getData($mysqlCon, $query_get_info, $query_get_data, $sensor_ids, $startDate, $endDate) {
	global $VERSION;
	$header = sprintf("# Data exported by SMI v%s on %s.\n", $VERSION, date('Y-m-d H:i:s', time()));
	$header .= sprintf("# This file contains the raw sensor data in the following format: \n");
	$header .= sprintf("# Sensor id, date (UTC), sensor value, sensor unit\n");
	$header .= sprintf("# The data was taken from the following sensors:\n");
	$header .= sprintf("# Sensor ID, sensor location, sensor name\n");
	$header .= getInfo($mysqlCon, $query_get_info, $sensor_ids);
	$header .= "#\n";

	$query_get_data = sprintf($query_get_data, implode(',', $sensor_ids));
	if (!($stmt = $mysqlCon->prepare($query_get_data))) {
		printf('Prepare failed for query "%s": (%d) %s\n', $query, $mysqlCon->errno, $mysqlCon->error);
		exit();
	}
	if (!$stmt->bind_param("ss", $startDate->format("Y-m-d H:i:s"), $endDate->format("Y-m-d H:i:s"))) {
		printf('Binding parameters failed for query "%s": (%d) %s\n', $query, $stmt->errno, $stmt->error);
		exit();
	}
	$stmt->execute();

	if (!($result = $stmt->get_result())) {
		printf('Execute failed for query "%s": (%d) %s', $query_get_data, $mysqlCon->connect_errno, $mysqlCon->connect_error);
		exit();
	}
	$fields = $result->fetch_fields();
	foreach ($fields as $field) {
		$header .= $field->name . ",";
	}
	unset($field);
	$header = trim($header, ",");

	$data = "";
	while($row = $result->fetch_assoc()) {
		$line = '';
		//for each field in a row
		foreach($row as $value) {
			//if null, create blank field
			if ((!isset($value)) || ($value == "")){
				$value = ",";
			}
			//else, assign field value to our data
			else {
				$value = str_replace('"' , '""' , $value);
				$value = '"' . $value . '"' . ",";
			}
			//add this field value to our row
			$line .= $value;
		}
		unset($value);
		//trim whitespace and comma from each row
		$line = trim($line, ",");
		$data .= trim($line) . "\n";
	}
	//remove all carriage returns from the data
	$data = str_replace( "\r" , "" , $data );

	$result->close();
	return array("header" => $header, "data" => $data);
}

// Check input
if (!(isset($_POST["startDate"]) && isset($_POST["endDate"]) && isset($_POST["outputType"]) && isset($_POST["sensors"]))) {
	echo "Invalid arguments.";
	exit();
}

$startDate = $_POST["startDate"];
$endDate = $_POST["endDate"];
$outputType = $_POST["outputType"];
$sensors = json_decode($_POST["sensors"]);

if (!preg_match($filter_regex_date, $startDate)) {
	printf('Invalid start date "%s"',  $startDate);
	exit();
}

if (!preg_match($filter_regex_date, $endDate)) {
	printf('Invalid end date "%s"',  $endDate);
	exit();
}

if (!preg_match($filter_regex_type, $outputType)) {
	printf('Invalid output type "%s"',  $outputType);
	exit();
}

// Must stack the array, because filter_var_array needs an *associative* array
$sensors = array("sensors" => $sensors);
$filter_options = array(
	"sensors"=>array(
		"filter" => FILTER_VALIDATE_INT,
		"flags" => FILTER_REQUIRE_ARRAY,
		"options"=> array(
			"min_range"=>1,
		)
	)
);
$filtered_array = filter_var_array($sensors, $filter_options);

$sensors = array();
foreach ($filtered_array["sensors"] as $value) {
	if ($value) {
		$sensors[] = $value;
	}
}
if (count($sensors) == 0) {
	echo 'No sensor ids given';
	exit();
}

$startDate = new DateTime($startDate);
$endDate = new DateTime($endDate);
// Finished checking input

// Open the database connection
// and set the timezone to UTC for exports
$con = openConnection("UTC");

// Convert to UTC, because we have set the connection timezon to UTC
$startDate = $startDate->setTimezone(new DateTimeZone('UTC'));
$endDate = $endDate->setTimezone(new DateTimeZone('UTC'));

$data = getData($con, $query_export["get_sensor_info"], $query_export["get_data"], $sensors, $startDate, $endDate);

// Close the database connection
closeConnection($con);

if (empty($data["data"])) {
	printf('No data found between %s and %s.', $startDate, $endDate);
	exit();
}

// When adding to this switch command do not forget to add the new cases to $filter_regex
switch ($outputType) {
	case "file_csv":
		$file = $data["header"]. "\n" . $data["data"];
		header("Content-Description: File Transfer");
		header("Content-type: application/octet-stream");
		header(sprintf('Content-disposition: attachment; filename="sensorData_%s_%s.csv"', $startDate->format("Y-m-d H:i:s"), $endDate->format("Y-m-d H:i:s")));
		header("Content-length: " . strlen($file));
		header("Content-Transfer-Encoding: binary");
		header("Pragma: no-cache");
		header("Cache-Control: private",false);
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Expires: 0");
		print $file;
		break;
	case "text_csv":
		$file = array("status" => 0, "data" => $data["header"]. "\n" . $data["data"]);
		break;
}
?>

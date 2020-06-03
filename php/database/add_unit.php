<?php require_once("../includes/database.php");?>
<?php include("../includes/sql_queries.php");?>
<?php
//Functions
function addunit($mysqlCon, $query, $unit) {
  if (!($stmt = $mysqlCon->prepare($query))) {
    printf('Prepare failed for query "%s": (%d) %s\n', $query, $mysqlCon->errno, $mysqlCon->error);
    exit();
  }
  if (!$stmt->bindParam(1, $unit, PDO::PARAM_STR)) {
    printf('Binding parameters failed for query "%s": (%d) %s\n', $query, $stmt->errno, $stmt->error);
    exit();
  }
  if (!$stmt->execute()) {
    if ($stmt->errno == 1062) {
      echo "Unit already exists.";
    } else {
      printf('Execute failed for query "%s": (%d) %s\n', $query, $stmt->errno, $stmt->error);
    }
    exit();
  }

  $id = $stmt->fetchColumn();

  return ("0&id=" . $id);
}

if (! isset($_POST["unit"])) {
	echo "Invalid arguments.";
  exit();
}

$unit = $_POST["unit"];

// Open MySQL connection
$con = openConnection();

// using prepared statements, so no more checking required
echo addUnit($con, $query_unit["add"], $unit);

// Close the database connection
closeConnection($con);
?>

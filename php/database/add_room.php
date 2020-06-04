<?php require_once("../includes/database.php");?>
<?php include("../includes/sql_queries.php");?>
<?php
//Functions
function addRoom($con, $query, $name) {
  if (!($stmt = $con->prepare($query))) {
    printf('Prepare failed for query "%s": (%d) %s\n', $query, $con->errno, $con->error);
    exit();
  }
  if (!$stmt->bindParam(1, $name, PDO::PARAM_STR)) {
    printf('Binding parameters failed for query "%s": (%d) %s\n', $query, $stmt->errno, $stmt->error);
    exit();
  }
  if (!$stmt->execute()) {
    if ($stmt->errno == 1062) {
      echo "Room already exists.";
    } else {
      printf('Execute failed for query "%s": (%d) %s\n', $query, $stmt->errno, $stmt->error);
    }
    exit();
  }

  $id = $stmt->fetchColumn();

  return ("0&id=" . $id);
}

if (! isset($_POST["name"])) {
  echo "Invalid arguments.";
  exit();
}

$name = $_POST["name"];

// Open the database connection
$con = openConnection();

// using prepared statements, so no more checking required
echo addRoom($con, $query_room["add"], $name);

// Close the database connection
closeConnection($con);
?>

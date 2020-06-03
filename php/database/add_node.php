<?php require_once("../includes/database.php");?>
<?php include("../includes/sql_queries.php");?>
<?php
//Constants
$filter_url = "/^((([a-z\d]([a-z\d-]*[a-z\d])*)\.)+[a-z]{2,}|((\d{1,3}\.){3}\d{1,3})|(localhost))$/";

//Functions
function addNode($mysqlCon, $query, $hostname, $label, $port) {
  global $query_get_last_id;

  if (!($stmt = $mysqlCon->prepare($query))) {
    printf('Prepare failed for query "%s": (%d) %s\n', $query, $mysqlCon->errno, $mysqlCon->error);
    exit();
  }
  $stmt->bindParam(1, $hostname, PDO::PARAM_STR);
  $stmt->bindParam(2, $label, PDO::PARAM_STR);
  $stmt->bindParam(3, $port, PDO::PARAM_INT);

  if (!$stmt->execute()) {
    if ($stmt->errno == 1062) {
      echo "Node already exists.";
    } else {
      printf('Execute failed for query "%s": (%d) %s\n', $query, $stmt->errno, $stmt->error);
    }
    exit();
  }

  $id = $stmt->fetchColumn();

  return ("0&id=" . $id);
}

if (! (isset($_POST["hostname"]) && isset($_POST["label"]) && isset($_POST["port"]))) {
  echo "Invalid arguments.";
        exit();
}

$hostname = $_POST["hostname"];
$label = $_POST["label"];
$port = $_POST["port"];

// Check the input
if (filter_var($port, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>1, "max_range"=>65535))) === False) {
  echo "Invalid port.";
  exit();
}
if (!preg_match($filter_url, $hostname)) {
        echo "Invalid hostname. Please enter a domain hostname or ip address.";
        exit();
}

// Open the database connection
$con = openConnection();

// using prepared statements, so no more checking required
echo addNode($con, $query_node["add"], $hostname, $label, $port);

// Close the database connection
closeConnection($con);
?>

<?
//ini_set('display_errors',1);
//error_reporting(E_ALL);

require_once("security.php");
require_once("config.php");

$link = mysqli_connect($db_host, $db_user, $db_pasw, $db_db);

$query = "INSERT INTO places_images SET
		placeID = " . $_POST['placeID'] . ",
		handle = '" . $_POST['handle'] . "'
";
//$result['query'] = $query;
//$result['postdata'] = $_POST;

if (mysqli_query($link, $query)) {
  $result['msg'] = "New record created successfully";
} else {
  $result['msg'] = "Error: " . $query . "<br>" . mysqli_error($link);
}
echo json_encode($result);
?>

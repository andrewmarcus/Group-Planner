<?
	$BASE = "..";

	include_once("$BASE/autoload.inc");

	$eventKey = $_REQUEST['eventKey'];
	$details = $_REQUEST['details'];
	
	$status = PDetails::save($eventKey, $details);
?>

<?
	$BASE = "..";
	include_once("$BASE/_data/status.inc");
	
	header("Content-Type: text/json"); 
	
	echo '{"' . implode('", "', $statuses) . '"}';
?>

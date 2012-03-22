<?
	$BASE = "..";
	
	include_once("$BASE/autoload.inc");
		
	$user->login();	
	session_write_close();

	header("Location: $BASE/index.php");
?>
<?
	$BASE = "..";
	
	session_start();
	include_once("$BASE/autoload.inc");
	
	$user->logout();
	session_write_close();
	
	header("Location: index.php");
?>
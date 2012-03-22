<?
$BASE = "..";
session_start();
include_once("$BASE/_data/styles.inc");
include_once("$BASE/autoload.inc");

include ("header.inc");

?>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title><? echo $styles['name']; ?> Event Planner</title>
    <meta http-equiv="Cache-Control" content="no-cache"/>
  </head>

  <body>
<?
	// If no user is logged in and no cookies have been set, show the login page
	if (!$user->isLoggedIn() && !$user->loginWithCookies()) {	
		include("login.php");
	}
	else {
		$eventId = $_REQUEST["event"];
		if (isset($eventId)) {
			include("event.php");
		}
		else {
			include("events.php");
		}
	}
?>
  </body>
</html>
<?
  session_write_close();
?>
<?
	$BASE = "..";
	include_once("$BASE/autoload.inc");
	
	$eventKey = $_REQUEST["event"];
	
	if (isset($_REQUEST['event']) && isset($_REQUEST["memberKey"])) {
	  $eventKey = $_REQUEST['event'];
		$memberKey = $_REQUEST['memberKey'];
		$status = $_REQUEST["status"];
		$guests = $_REQUEST["guests"];
		
		// Update the event attendees
    $attendees = PAttendees::getEventAttendees($eventKey);
    $attendees->setAttendee($memberKey, array('status' => $status, 'guests' => $guests));
    $attendees->save();
	}

	// Redirect mobile browsers to the event page
  $acceptHeader = $_SERVER["HTTP_ACCEPT"];
	if (stristr($acceptHeader, "application/vnd.wap.xhtml+xml")) {
		header("Content-Type: application/vnd.wap.xhtml+xml");
	}
	else if (stristr($acceptHeader, "application/xhtml+xml")) {
		header("Content-Type: application/xhtml+xml");
	}
	else {
		header("Content-Type: text/html");
	}
	header("Location: index.php?event=" . $eventKey. (isset($memberKey) ? "#" . $memberKey : ""));
?>


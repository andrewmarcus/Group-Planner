<?
	$BASE = "..";
	
	include_once("$BASE/autoload.inc");
	
	$eventKey = $_REQUEST["event"];	
	$statuses = $_REQUEST['status'];
	$guestses = $_REQUEST['guests'];
	$removes = $_REQUEST['remove'];
	
	$attendees = PAttendees::getEventAttendees($eventKey);
	if (!$attendees) {
	  $attendees = new PAttendees($eventKey);
	}
	
	// First remove attendees
	foreach ($removes as $memberKey => $status) {
	  $attendees->removeAttendee($memberKey);
	}
	
	foreach ($statuses as $memberKey => $status) {
		if ($status != "Unknown" && !isset($removes[$memberKey])) {
		  $guests = $guestses[$memberKey];
		  
		  $attendees->setAttendee($memberKey, array(
		    'status' => $status, 
		    'guests' => $guests
		  ));
		}
	}
	$attendees->save();
?>

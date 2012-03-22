<?
	$BASE = "..";
	include_once("$BASE/autoload.inc");
	session_write_close();

	$memberKey = $user->getUserKey();
	
	$statuses = $_REQUEST['status'];
	$guestses = $_REQUEST['guests'];
	$removes = $_REQUEST['remove'];
	
	// First remove attendees
	foreach ($removes as $eventKey => $status) {
		$attendees = PAttendees::getEventAttendees($eventKey);
		if ($attendees) {
		  $attendees->removeAttendee($memberKey);
		  $attendees->save();
		}
	}
	
	foreach ($statuses as $eventKey => $status) {
		if ($status != 'Unknown' && !isset($removes[$eventKey])) {
			$guests = $guestses[$eventKey];
			
			$attendees = PAttendees::getEventAttendees($eventKey);
			if (!$attendees) {
			  $attendees = new PAttendees($eventKey);
		  }
		  $attendees->setAttendee($memberKey, array(
		    'status' => $status,
		    'guests' => $guests,
		  ));
		  $attendees->save();
		}
	}
?>

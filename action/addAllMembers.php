<?
	$BASE = "..";

	include_once("$BASE/autoload.inc");
	
	$eventKey = $_REQUEST["id"];
	
  $attendees = PAttendees::getEventAttendees($eventKey);
  if ($attendees) {
	  $members = PMember::getMembers();
    foreach ($members as $memberKey => $member) {
      if (!$member->inactive && !$attendees->getAttendee($memberKey)) {
  	    $attendees->setAttendee($memberKey, array(
  	      'status' => 'Unknown',
  	      'guests' => 0,
  	    ));
  	  }
	  }
	  $attendees->save();
	}
?>

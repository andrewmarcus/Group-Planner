<?
$BASE = "..";

include_once("$BASE/autoload.inc");

session_write_close();

$eventKey = $_REQUEST["event"];
$memberKey = $_REQUEST["member"];
$edit = isset($_REQUEST["edit"]) && $_REQUEST["edit"] == "true";
$added = isset($_REQUEST["added"]) && $_REQUEST["added"] == "true";
$mode = $_REQUEST['mode'];

$attendees = PAttendees::getEventAttendees($eventKey);
if (!$attendees) {
  $attendees = new PAttendees($eventKey);
}
$attendee = $attendees->getAttendee($memberKey, true);

if ($mode == 'myevents') {
	$attendee->insertMyEventRow($edit, $added);
}
else {
	$attendee->insertAttendeeRow($edit, $added);
}
?>

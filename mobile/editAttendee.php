<?
  $BASE = "..";

  include_once("$BASE/autoload.inc");

  $self = $_SERVER["PHP_SELF"];

  $eventKey = $_REQUEST["event"];
  $attendeeKey = $_REQUEST["attendee"];
  
  $attendees = PAttendees::getEventAttendees($eventKey);
  if ($attendees) {
    $attendee = $attendees->getAttendee($attendeeKey);
  }
  
	if ($attendee) {
    $status = $attendee->status;
		$guests = $attendee->guests;
		$member = $attendee->getMember();
	}

  include ("header.inc");
?>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>Foggy Bottom Event Planner</title>
    <meta http-equiv="Cache-Control" content="no-cache"/>
  </head>
  <body>

<form method="post" action="setAttendee.php?event=<? echo $eventKey ?>">
  <p><? echo $member->name; ?></p>
  <input type="hidden" name="memberKey" value="<? echo $attendeeKey; ?>"/>
  
  <select name="status">
  <? if (!$attendee->isValidStatus()) : ?>
    <option value="Unknown" selected="selected">---</option>
  <? endif; ?>
  <? foreach ($statuses as $s) : ?>
    <option value="<? echo $s ?>" <? echo $status == $s ? "selected=\"selected\"" : "" ?>><? echo $s ?></option>
  <? endforeach; ?>
  </select>
  Guests: 
  <select name="guests">
  <? for ($i = 0; $i < 9; $i++) : ?>
    <option value="<? echo $i ?>" <? echo $guests == $i ? "selected=\"selected\"" : "" ?>><? echo $i ?></option>
  <? endfor; ?>
  </select>
  <input type="submit" value="Submit"/>
  <a href="index.php?event=<? echo $eventKey ?>#<? echo $attendeeKey ?>">Cancel</a>
</form>

  </body>
</html>
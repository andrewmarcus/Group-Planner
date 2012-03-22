<?
  $BASE = "..";
  include_once("$BASE/autoload.inc");

  $self = $_SERVER["PHP_SELF"];
  $eventKey = $_REQUEST["event"];

  $members = PMember::getMembers();
  $attendees = PAttendees::getEventAttendees($eventKey);

  // Find out which members are not attendees
  $nonAttendees = array();
  foreach ($members as $memberKey => $member) {
    if (!$attendees || !$attendees->getAttendee($memberKey)) {
      $nonAttendees[$member->inactive][$memberKey] = $member;
    }
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
  <p>
    Add new attendee:<br/>

    <select name="memberKey">
    <? foreach ($nonAttendees as $inactive => $data) : ?>
      <optgroup label="<? echo $inactive ? 'Inactive Members' : 'Active Members'; ?>">
      <? foreach ($data as $memberKey => $member) : ?>
        <option value="<? echo $memberKey ?>"><? echo $member->name; ?></option>
      <? endforeach; ?>
      </optgroup>
    <? endforeach; ?>
    </select>
    
    <select name="status">
      <option value="Unknown" selected="selected">---</option>
    <? foreach ($statuses as $s) : ?>
      <option value="<? echo $s ?>"><? echo $s ?></option>
    <? endforeach; ?>
    </select>

	Guests: 
    <select name="guests">
    <? for ($i = 0; $i < 9; $i++) : ?>
      <option value="<? echo $i; ?>"><? echo $i; ?></option>
    <? endfor; ?>
    </select>
    <input type="submit" value="Submit"/>
    <a href="index.php?event=<? echo $eventKey ?>#attendees">Cancel</a>
  </p>
</form>

  </body>
</html>
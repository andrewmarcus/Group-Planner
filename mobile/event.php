<?
session_start();

include_once("$BASE/autoload.inc");

session_write_close();

$self = $_SERVER["PHP_SELF"];
$eventKey = $_REQUEST["event"];

$calendar = PCalendar::instance();

if (!empty($eventKey)) {
	$event = $calendar->getEvent($eventKey);
	if (!isset($event)) {
		include("eventNotFound.php");
		exit;
	}
}

$nextId = $calendar->getNextId($event);
$prevId = $calendar->getPrevId($event);
	
$date = $event->getStartTime();
	
$attendees = PAttendees::getEventAttendees($eventKey);
?>

<!-- EVENT TITLE -->

<a id="top"></a>
<h2><? echo htmlspecialchars($event->getTitle()); ?></h2>
<h3><? echo $date["datePart"] ?><? echo $date["timePart"] != "" ? "<br/>" . $date["timePart"] : "" ?></h3>

<!-- NAVIGATION LINKS -->

<p>
  <a href="<? echo $self ?>">Back to event list</a><br/>
  <? echo (isset($prevId) ? "<a href=\"$self?event=$prevId\">&lt; Previous</a>" : "&lt; Previous") ?>
  <? echo (isset($nextId) ? "<a href=\"$self?event=$nextId\">Next &gt;</a>" : "Next &gt;") ?><br/>
  <a href="<? echo "$self?event=$eventKey" ?>">Refresh</a><br/>
</p>
<p>
  <a href="#summary">Jump to summary</a><br/>
  <a href="#attendees">Jump to attendees</a>
</p>

<!-- EVENT DETAILS -->
<p>
  <? $location = $event->getLocation(); ?>
  <? echo $location ? '<i>' . htmlspecialchars($location) . '</i>' : "<i>No location yet</i>"; ?>
</p>
<p>
  <? $details = $event->getDetails(); ?>
  <? echo $details ? str_replace("\n", "<br/>", htmlspecialchars($details)) : "<i>No details yet</i>" ?>
</p>
<? $priv_details = $event->getPrivateDetails(); ?>
<? if ($priv_details) : ?>
<p>
  <? echo stripslashes(str_replace("\n", "<br/>", htmlspecialchars($priv_details))); ?>
</p>
<? endif; ?>
<p>
  <a href="#top">Jump to top</a><br/>
  <a href="#attendees">Jump to attendees</a>
</p>

<!-- EVENT ATTENDEE SUMMARY -->

<?
// Count up the total number of attendees with each status
$counts = $attendees->getStatusCounts();
?>
<hr/>
<a id="summary"></a>
<h3>Summary</h3>
<table cellpadding="3" cellspacing="0" border="0">
<?
foreach ($counts['statuses'] as $s => $c) {
?>
  <tr>
    <td><? echo $s ?>:</td>
    <td><b><? echo $c ?></b></td>
  </tr>
<?
}
?>
  <tr>
    <td colspan="2"><hr/></td>
  </tr>
  <tr>
    <td>Guests:</td>
    <td><b><? echo $counts['guests']; ?></b></td>
  </tr>
</table>
<p><a href="#top">Jump to top</a></p>

<!-- EVENT ATTENDEE FORM -->

<hr/>
<a id="attendees"></a>
<h3>Attendees</h3>
<table cellpadding="5" cellspacing="0" border="0">
  <tr>
  <? if ($user->isAdmin()) : ?>
    <td colspan="2"><a href="addAttendee.php?event=<? echo $eventKey ?>">Add a new attendee</a></td>
  <? else : ?>
    <td colspan="2"><a href="editAttendee.php?event=<? echo $eventKey ?>&amp;attendee=<? echo $user->getUserKey(); ?>">Add me to this event</a></td>
  <? endif; ?>
  </tr>
<?
foreach ($attendees->members as $memberKey => $attendee) {
  $member = $attendee->getMember();
      
	// If no matching member info could be found for this member key, don't bother rendering the member.
	// Perhaps they were deleted?
  if (!$member) {
    continue;
  }
    
  $status = $attendee->status;
  $guests = $attendee->guests;

  if (!$attendee->isValidStatus()) {
    $status = '---';
  }

  if ($user->isAdmin() || $memberKey == $user->getUserKey()) {
		$status = "<a id='$memberKey' href='editAttendee.php?event=$eventKey&amp;attendee=$memberKey'>$status</a>";
	}
	
	if ($guests > 0) {
		$status = $status . " +$guests";
	}
?>
  <tr>
  	<td><b><? echo $status; ?></b></td>
    <td><? echo $member->name; ?></td>
  </tr>
<?
}
?>
  <tr>
  <? if ($user->isAdmin()) : ?>
    <td colspan="2"><a href="addAttendee.php?event=<? echo $eventKey ?>">Add a new attendee</a></td>
  
  <? elseif (!isset($attendees->members[$user->getUserKey()])) : ?>
    <td colspan="2"><a href="editAttendee.php?event=<? echo $eventKey ?>&amp;attendee=<? echo $user->getUserKey(); ?>">Add me to this event</a></td>
  
  <? endif; ?>
  </tr>
</table>
<p>
  <a href="#top">Jump to top</a><br/>
  <a href="#summary">Jump to summary</a><br/>
  <a href="#attendees">Jump to attendees</a>
</p>


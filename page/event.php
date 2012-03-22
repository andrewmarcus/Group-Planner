<?
$BASE = "..";
include_once("$BASE/autoload.inc");

session_write_close();

$calendar = PCalendar::instance();

$eventKey = $_REQUEST["event"];
if (isset($eventKey)) {
	$event = $calendar->getEvent($eventKey);
	if (!isset($event)) {
		include("eventNotFound.php");
		exit;
	}
}
$nextId = $calendar->getNextId($event);
$prevId = $calendar->getPrevId($event);
	
$members = PMember::getMembers();
$attendees = PAttendees::getEventAttendees($eventKey);
if (!$attendees) {
  $attendees = new PAttendees($eventKey);
}

// Find out which members are not attendees
$nonAttendees = array();
foreach ($members as $memberKey => $member) {
  if (!$attendees->getAttendee($memberKey)) {
    $nonAttendees[$member->inactive][$memberKey] = $member;
  }
}

$location = $event->getLocation();
$details = $event->getDetails();
$priv_details = $event->getPrivateDetails();

if ($location) {
	$mapLink = "http://maps.google.com/maps?f=q&hl=en&q=" . urlencode($location);
}

// Compute the dates to display
$dateDisplay = "";

$startDate = $event->getStartTime();
$endDate = $event->getEndTime();

// Show the starting date
$dateDisplay .= $startDate["datePart"];

// If there is a starting time, show it
if ($startDate["timePart"] != "") {
	$dateDisplay .= ", " . $startDate["timePart"];
}
// Unless the event is an all day event, show the end date/time
if ($endDate["timestamp"] != $startDate["timestamp"] + 60*60*24 || $startDate["hour"] != 0) {
	$dateDisplay .= " to ";
	
	if ($endDate["datePart"] != $startDate["datePart"]) {
		// If there is no time part, the event actually ends the previous day
		if ($endDate["hour"] == 0) {
			$dateDisplay .= date("D M d, Y", $endDate["timestamp"] - 60*60*24);
		} else {
			$dateDisplay .= $endDate["datePart"] . ", ";
		}
	}
	// If there is an ending time, add it
	if ($endDate["timePart"] != "") {
		$dateDisplay .= $endDate["timePart"];
	}
}

?>
<div dojoType="dijit.TitlePane" open="false" title="<? echo $event->getTitle(); ?> - <? echo $dateDisplay ?>" class="event_title">
  <p>
    <? echo $location ? '<i>' . htmlspecialchars($location) . "</i> &nbsp; [<a href='$mapLink'>Map It</a>]" : "<i>No location yet</i>" ?>
  </p>
  <p>
    <? echo $details ? str_replace("\n", "<br/>", htmlspecialchars($details)) : "<i>No details yet</i>" ?>
  </p>  
  <hr/>
  <p><em>Information for members:</em></p>
  <? if ($user->isAdmin()) : ?>
    <p>
      <textarea id="private-details-box" onChange="return javascript:page.setEditing('events')" cols="20" rows="10"><? echo stripslashes($priv_details); ?></textarea>
      <button dojoType="dijit.form.Button" onClick="javascript:events.setPrivateDetails()">Save</button>
    </p>
  <? elseif (!empty($priv_details)) : ?>
	  <p><? echo stripslashes(str_replace("\n", '<br/>', htmlspecialchars($priv_details))); ?></p>
  <? endif; ?>
</div>
<p>
<? echo (isset($prevId) ? "<a href=\"javascript:events.loadEvent('$prevId')\">&lt; Previous</a>" : "&lt; Previous") ?>
&nbsp;&nbsp;
<? echo (isset($nextId) ? "<a href=\"javascript:events.loadEvent('$nextId')\">Next &gt;</a>" : "Next &gt;") ?>
</p>
<form id="attendeeForm">
  <? renderButtons($attendees, $nonAttendees); ?>
  <table id="attendeeTable" cellpadding="5" cellspacing="0" border="0">
  <? if (!empty($attendees->members)) : ?>
    <? foreach ($attendees->members as $memberKey => $attendee) : ?>
      <?
        $member = $attendee->getMember();
    		
    		// If no matching member info could be found for this member key, don't bother rendering the member.
    		// Perhaps they were deleted?
    		if (!$member) {
    			continue;
    		}
      ?>
      <tr id="<? echo "attendee_$memberKey" ?>" <? echo ($memberKey == $user->getUserKey()) ? "class='selected'" : "" ?>>
        <? $attendee->insertAttendeeRow(false, false); ?>
      </tr>
    <? endforeach; ?>
  <? endif; ?>
  </table>
  
  <? if (!empty($attendees->members)) : ?>
    <? renderButtons($attendees, $nonAttendees); ?>
  <? else : ?>
    <p><i>No one has yet signed up for this event</i></p>
  <? endif; ?>
</form>

<?
/**
 * Render the buttons and links to add users to the event
 */
function renderButtons($attendees, $nonAttendees) {
	global $user;
	if (!$user->isPublic()) {
?>
<p>
  <button dojoType="dijit.form.Button" onClick="javascript:events.saveAttendees()">Submit</button>
  <button dojoType="dijit.form.Button" onClick="javascript:events.reloadEvent()">Reset</button>
<? if ($user->isAdmin()) : ?>
  &nbsp;Add:
  <select name="new_attendee"
  	dojoType="dijit.form.ComboBox" 
  	autocomplete="true"
  	value=""
  	onChange="events.addNewAttendee(this)">
    <option value="" selected="true"></option>
    <? foreach ($nonAttendees as $inactive => $data) : ?>
      <option value="">--- <? echo $inactive ? 'Inactive members' : 'Active members'; ?> ---</option>
      <? foreach ($data as $memberKey => $member) : ?>
        <option value="<? echo $member->name; ?>"><? echo $member->name; ?></option>
      <? endforeach; ?>
    <? endforeach; ?>
  </select>
<? endif; ?>

<? if (isset($nonAttendees[0][$user->getUserKey()]) || isset($nonAttendees[1][$user->getUserKey()])) : ?>
  <a href="javascript:events.addNewAttendee('<? echo $user->getUserName() ?>')"><? echo $user->isAdmin() ? "Me" : "Add me to this event" ?></a>
<? endif; ?>
</p>
<?
  }
}
?>
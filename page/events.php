<?
$BASE = "..";

session_start();
include_once("$BASE/autoload.inc");

$practices = (!isset($_REQUEST["practices"]) || $_REQUEST["practices"] == "true");
	
// Load the events from Google
$calendar = PCalendar::instance();
$calendar->getEventMaps($practices);
$calendar->store();
session_write_close();

?>
<h2>Choose an upcoming event</h2>
<table cellpadding="0" cellspacing="0" border="0" id="eventsTable">
  <tr>
    <td colspan="2"><a href="javascript:events.loadEventList()">Refresh List</a></td>
    <td>
<? if ($practices == true) { ?>
<a href="javascript:page.hidePractices()">Hide Weekly Practices <img src="images/arrowDown.png" border="0"/></a>
<? } else { ?>
<a href="javascript:page.showPractices()">Show Weekly Practices <img src="images/arrowRight.png" border="0"/></a>
<? } ?>
    </td>
  </tr>
<?
	$year = "";
	$month = "";
	
	$one_day = 60*60*24;
	$one_week = $one_day * 7;
	
	foreach ($calendar->eventTimeMap as $startTime => $ids) {
		$startDate = PCalendar::parseISODateTime($startTime);
		
		foreach ($ids as $eventKey) {
			$event = $calendar->getEvent($eventKey);
			if (!$event) {
			  continue;
		  }
			
			$newyear = date("Y", $startDate["timestamp"]);
			$newmonth = date("M", $startDate["timestamp"]);
			
			// Sort all events into buckets by year and month
			if ($newmonth != $month) {
				$year = $newyear;
				$month = $newmonth;
?>
  <tr>
  	<td colspan="3" align="left"><hr width="100%"/><b><? echo "$month $year" ?></b></td>
  </tr>
<?	
			}
			
			$dt = $event->processDateAndTime();
			
			// Has this event been updated in the last week?
			$updated = $event->getModifiedTime();
			if ($updated["timestamp"] + $one_week > time()) {
				$is_updated = true;
			} 
			else {
				$is_updated = false;
			}
			
			// Get the title of the event
			$title = $event->getTitle();
			$class = '';
			if ($event->isTentative()) {
				$class = 'tentative';
			}
			if ($event->isCanceled()) {
				$class = 'canceled';
			}
?>
  <tr id="event_<? echo $eventKey; ?>" class="<? echo $class ?>">
    <td><? echo $dt['date']; ?></td>
    <td><? echo $dt['time'];  ?></td>
    <td><? echo $is_updated ? "<span class='updated'>*</span>" : ""?>
	  <a href="javascript:events.loadEvent('<? echo $eventKey; ?>')"><? echo $title; ?></a>
	</td>
  </tr>
<?
		}
  }
?>
</table>
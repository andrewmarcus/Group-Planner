<?
session_start();

include_once("$BASE/autoload.inc");

$calendar = PCalendar::instance();
$calendar->getEventMaps(false, false, false);
$calendar->store();

session_write_close();

?>
<h2>Upcoming Events</h2>
<a href="<? echo $_SERVER["PHP_SELF"] ?>">Refresh</a><br/>
<? echo $user->getUserName(); ?> - <a href="logout.php">Logout</a>
<table cellpadding="5" cellspacing="0" border="0">
<?
	$year = "";
	$month = "";
	foreach ($calendar->eventTimeMap as $startTime => $ids) {
		$date = PCalendar::parseISODateTime($startTime);
		
		foreach ($ids as $id) {
			$event = $calendar->getEvent($id);
			
			$newyear = date("Y", $date["timestamp"]);
			$newmonth = date("M", $date["timestamp"]);
			
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
			$wk = date("D", $date["timestamp"]);
			$day = date("d", $date["timestamp"]);
?>
  <tr>
    <td><? echo $wk ?></td>
    <td><? echo $day ?></td>
    <td><a href="<? echo $_SERVER["PHP_SELF"] . "?event=$id" ?>"><? echo htmlspecialchars($event->getTitle()); ?></a></td>
  </tr>
<?
		}
  }
?>
</table>

<?
// Set the base of the planner directory
$BASE = ".";
include_once("$BASE/autoload.inc");

// Define constants to be used in algorithms
define(ONE_DAY, 60*60*24);
define(ONE_WEEK, ONE_DAY * 7);

?>
<!doctype html public "-//w3c//dtd html 4.0 transitional//en">
<html>
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
   <meta name="Content-Type" content="text/html; charset=utf-8">
   <meta name="Author" content="Andrew Marcus">
   <title><? echo $styles['title']; ?> | Schedule</title>
   <link href="../styles.css" rel="stylesheet" type="text/css">
   <style type="text/css">
   
.year {
	font-weight: bold;
	font-size: 1.1em;
	border-top: 1px solid <? echo $styles['hr_color']; ?>;
}

.date {
	font-weight: bold;
}

.location {
	font-size: 0.9em;
	padding-left: 10px;
}

.title {
	font-weight: bold;
	padding-left: 10px;
	color: #D47A13;
}

.description {
	padding-top: 0.5em;
	padding-left: 10px;
	display: none;
}

span.updated {
  font-weight: bold;
  font-color: <? echo $styles['hr_color']; ?>
}

.expander {
	font-weight: normal;
	font-size: 0.9em;
}

.col-left {
  float: left;
  width: 49%;
  padding-right: 5px;
}

.col-right {
  float: right;
  width: 49%;
  padding-left: 5px;
}

</style>
<script type="text/javascript" language="javascript">
function toggleDescription(index) {
	if (document.getElementById) {
		var link = document.getElementById("link" + index);
		var div = document.getElementById("descr" + index);
	}
	else if (document.all) {
		var link = document.all["link" + index];
		var div = document.all["descr" + index];
	}
	if (link != null) {
		link.innerHTML = (link.innerHTML == "Show" ? "Hide" : "Show");
	}
	if (div != null) {
		div.style.display = (div.style.display == "block" ? "none" : "block");
	}
}
</script>
</head>
<body>

<div align="center">
  <a href="<? echo $styles['main_site']; ?>"><img src="<? echo $styles['main_logo']; ?>" border="0" height=103 width=150></a>
</div>

<h2><? echo $styles['name']; ?> Schedule</h2>

<!p To see our practice schedule and a complete list of our events, <a href="http://www.google.com/calendar/embed?src=lohrgpkg1ag430e3l8oo2p5ki0%40group.calendar.google.com" view the complete calendar </a. </p>

<p class="backbutton"><a href="<? echo $styles['main_site']; ?>">Back</a></p>

<div class="container">
  <div class="col-left">
    <h3>Upcoming Confirmed Events</h3>
    <? displayEvents(false); ?>
  </div>
  <div class="col-right">
    <h3>Recent Events</h3>
    <? displayEvents(true); ?>
  </div>
</div>

<br clear="all"/>
<p class="credits">Powered by <a href="http://www.google.com/calendar">Google Calendars</a>, with some help from <a href="http://www.amarcus.org">Andrew Marcus</a></p>
</body>
</html>


<?
function displayEvents($reverse) {
  $calendar = PCalendar::instance();
	$calendar->getEventMaps(false, $reverse);
	$calendar->store();
	
	$year = "";
	foreach ($calendar->eventTimeMap as $startTime => $ids) {
		$startDate = PCalendar::parseISODateTime($startTime);
		$startStamp = $startDate["timestamp"];
		
		foreach ($ids as $id) {
			$event = $calendar->getEvent($id);
			$title = format($event->getTitle());
			
			// Skip events whose title contains the words "practice" or "rehearsal" or ends in a question mark
			if (contains($title, array("practice", "rehearsal", "audition", "?", "canceled", "cancelled"))) {
				continue;
			}
			
			$newyear = date("Y", $startStamp);
			
			// Sort all events into buckets by year
			if ($newyear != $year) {
				$year = $newyear;
?>
<p class="year"><? echo "$year" ?></p>
<?	
			}
			// Get the end date
			$endDate = $event->getEndTime();
			$dateView = calculateDateView($startDate, $endDate);
			
			// Render the event
			printEvent($dateView, $title, $event);
		}
  }
}

// Describes the formatting for a single event
function printEvent($dateView, $title, $event) {
	static $div_index;
	
	if (!isset($div_index)) {
		$div_index = 0;
	}
	
	$link_id = "link" . $div_index;
	$div_id = "descr" . $div_index;
	
	$details = $event->getDetails();
	$location = $event->getLocation();
	
  // Has this event been updated in the last week?
  $updated = $event->getModifiedTime();
  if ($updated["timestamp"] + ONE_WEEK > time()) {
    $is_updated = true;
  }
  else {
    $is_updated = false;
  }
?>
<p class="calendar">
  <div class="date"><? echo $dateView; ?></div>
  <div class="title"><? echo $title; ?><? echo $is_updated ? ' <span class="updated">*</span>': ''; ?>

  <? if (!empty($details)) : ?>
     &nbsp; <span class="expander"><a href="javascript:toggleDescription(<? echo $div_index ?>)">[<span id="<? echo $link_id ?>">Show</span> Details]</a></span>
  <? endif; ?>
  </div>
  <? if (!empty($location)) : ?>
    <? $mapLink = "http://maps.google.com/maps?f=q&hl=en&q=" . urlencode($location); ?>
    <div class="location"><? echo format($location); ?>&nbsp;[<a target="_blank" href="<? echo $mapLink; ?>">Map It]</div>
  <? endif; ?>
  <? if (!empty($details)) : ?>
    <div class="description" id="<? echo $div_id ?>"><? echo formatDescription($details); ?></div>
    <? $div_index++; ?>
  <? endif; ?>
</p>
<?
}

function calculateDateView($startISODate, $endISODate) {
	// Get the start date
	$startStamp = $startISODate["timestamp"];
	$startMonth = $startISODate["month"];
	$startDay = $startISODate["day"];			
	$startTime = $startISODate["timePart"];
	
	// Get the end date
	$endStamp = $endISODate["timestamp"];
	$endMonth = $endISODate["month"];
	$endDay = $endISODate["day"];
	$endTime = $endISODate["timePart"];
	
	// Render something useful as the start and end date
	$dateView = date("D, M d", $startStamp);
	
	// If the event spans more than one day, include an ending day
	if ($endStamp > $startStamp + ONE_DAY) {
		// If the event ends at midnight, report it in the previous day
		if ($endTime == "") {
			$dateView .= " to " . date("D, M d", $endStamp - ONE_DAY);
		} else {
			$dateView .= " to " . date("D, M d", $endStamp);
		}
	}
	// Now print the start time of the event
	if ($startTime != "") {
		$dateView .= " - $startTime";
	}
	if ($endTime != "" && $endTime != $startTime) {
		$dateView .= " to $endTime";
	}
	return preg_replace("/ /", "&nbsp;", $dateView);
}

function format($str) {
	return stripslashes(urldecode($str));
}

function formatDescription($str) {
	$str = format($str);
	$str = convertUrls($str);
	$str = preg_replace("/  /", " &nbsp;", $str);
	$str = preg_replace("/\n/", "<br/>", $str);
	return $str;
}

function convertUrls($str) {
	$str = preg_replace("/<a rel=nofollow href=\"(\\S*)\" class=linkified target=_blank>\\S*?<\\/a>/i", "\\0", $str);
//	$str = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]", "<a href=\"\\0\">\\0</a>", $str);
	return $str;
}

function is_substr($haystack, $needle){
	return stristr($haystack, $needle) ? true : false;
}

function contains($haystack, $needles) {
	foreach ($needles as $needle) {
		if (is_substr($haystack, $needle)) {
			return true;
		}
	}
	return false;
}
?>

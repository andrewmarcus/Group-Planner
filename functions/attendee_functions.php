<?
include_once("member_functions.php");
include_once("$BASE/functions/userauth_functions.php");
include_once("$BASE/_data/status.inc");

$attendeesDir = "$BASE/_attendees";

function loadAttendees($eventKey) {
	global $attendeesDir;
	
	if (is_file("$attendeesDir/$eventKey.inc")) {
		include("$attendeesDir/$eventKey.inc");
		
		uksort($attendees[$eventKey], "cmp");
		return $attendees;
	}
	return array();
}

/** Update the status of an attendee
 */
function setAttendeeStatus(&$attendees, $eventKey, $memberKey, $status, $guests = 0) {
	if ($status == "_remove") {
		unset($attendees[$eventKey][$memberKey]);
	} else {
		$attendees[$eventKey][$memberKey] = array("status" => $status, "guests" => $guests);
	}
}

function saveAttendees(&$attendees, $eventKey) {
	global $attendeesDir;
	
	$filename = "$attendeesDir/$eventKey.inc";
	$fh = fopen($filename, "w");
	fwrite($fh, "<" . "?\n");
	fwrite($fh, "\$attendees['$eventKey'] = array(\n");
	
	$comma = false;
	foreach ($attendees[$eventKey] as $memberKey => $attendee) {
		if (empty($memberKey)) {
			continue;
		}
		if ($comma) {
			fwrite($fh, ",\n");
		}
		$comma = true;
		$status = $attendee["status"];
		$guests = $attendee["guests"];
		if (empty($guests)) {
			$guests = 0;
		}
		fwrite($fh, "    '$memberKey' => array('status' => '$status', 'guests' => $guests)");
	}
	fwrite($fh, "\n)\n?" . ">\n");
	fclose($fh);
}

function insertAttendeeRow($memberKey, $member, $attendee, $editable, $added) {
	global $BASE, $statuses, $user;
	
	if (isset($attendee)) {
		$status = $attendee['status'];
		$guests = $attendee['guests'];
	}
	if (empty($guests)) {
		$guests = 0;
	}
	
	$unknown = true;
	foreach ($statuses as $s) {
		$unknown = $unknown && $status != $s;
	}
?>
	<td><? echo $member["name"] ?></td>
    <td class="status">
<?	if ($editable) { ?>
	  <select name="status[<? echo $memberKey; ?>]"
		  dojoType="dijit.form.FilteringSelect" 
		  autocomplete="true"
		  value="<? echo $unknown ? "Unknown" : $status ?>">
<?		if ($unknown) { ?>
	    <option value="Unknown" selected="true">---</option>
<? 		}
		foreach ($statuses as $s) { ?>
	    <option value="<? echo $s ?>" <? echo $status == $s ? "selected='true'" : "" ?>><? echo $s ?></option>
<?		} ?>
	  </select>	  
	  <input type="hidden" name="remove[<? echo $memberKey ?>]" value="_remove" disabled="true"></input>
<?	} else { ?>
    <? echo $unknown ? "---" : $status ?>
<? 	} ?>
	</td>
	<td class="guests">
<?	if ($editable) { ?>
	  <select name="guests[<? echo $memberKey; ?>]"
	  		dojoType="dijit.form.FilteringSelect"
			autocomplete="true"
			value="<? echo $guests; ?>">
<?		for ($i = 0; $i < 10; $i++) { ?>
			<option value="<? echo $i; ?>" <? echo $i == $guests ? "selected='true'" : "" ?>><? echo $i; ?></option>
<?		} ?>
	  </select> guests
<? 	} else {
		if ($guests > 0) {
	  		echo "$guests guest" . ($guests != 1 ? 's' : '');
		}
		else {
			echo "&nbsp;";
		}
    } ?>
	</td>
<?	if ($editable && !$added) { ?>
	<td><a class="action edit" href="javascript:events.editAttendee('<? echo $memberKey ?>', false)">Cancel</a> &nbsp;
	    <a class="action remove" href="javascript:events.removeAttendee('<? echo $memberKey ?>')">Remove</a></td>
<?	} else if ($editable && $added) { ?>
	<td><a class="action remove" href="javascript:events.removeAttendee('<? echo $memberKey ?>')">Remove</a></td>
<?	} else if ($user->isAdmin() || $memberKey == $user->getUserKey()) { ?>
	<td><a class="action edit" href="javascript:events.editAttendee('<? echo $memberKey ?>', true)">Edit</a></td>
<?
	}
}

function insertMyEventRow($eventKey, $memberKey, $attendee, $editable = false, $added = false) {
	global $BASE, $statuses;
	
	if (isset($attendee)) {
		$status = $attendee['status'];
		$guests = $attendee['guests'];
	}
	if (empty($guests)) {
		$guests = 0;
	}
	
	$unknown = true;
	foreach ($statuses as $s) {
		$unknown = $unknown && $status != $s;
	}
?>
    <td class="status">
<?	if ($editable) { ?>
	  <select name="status[<? echo $eventKey; ?>]"
		  dojoType="dijit.form.FilteringSelect" 
		  autocomplete="true"
		  value="<? echo $unknown ? "Unknown" : $status ?>">
<?		if ($unknown) { ?>
	    <option value="Unknown" selected="true">---</option>
<? 		}
		foreach ($statuses as $s) { ?>
	    <option value="<? echo $s ?>" <? echo $status == $s ? "selected='true'" : "" ?>><? echo $s ?></option>
<?		} ?>
	  </select>	  
	  <input type="hidden" name="remove[<? echo $eventKey ?>]" value="_remove" disabled="true"></input>
<?	} else { ?>
    <? echo $unknown ? "---" : $status ?>
<? 	} ?>
	</td>
	<td class="guests">
<?	if ($editable) { ?>
	  <select name="guests[<? echo $eventKey; ?>]"
	  		dojoType="dijit.form.FilteringSelect"
			autocomplete="true"
			value="<? echo $guests; ?>">
<?		for ($i = 0; $i < 10; $i++) { ?>
			<option value="<? echo $i; ?>" <? echo $i == $guests ? "selected='true'" : "" ?>><? echo $i; ?></option>
<?		} ?>
	  </select> guests
<? 	} else {
		if ($guests > 0) {
	  		echo "$guests guest" . ($guests != 1 ? 's' : '');
		}
		else {
			echo "&nbsp;";
		}
    } ?>
	</td>
<?	if ($editable && !$added) { ?>
	<td><a class="action edit" href="javascript:myevents.editAttendee('<? echo $eventKey ?>', '<? echo $memberKey ?>', false, false)">Cancel</a> &nbsp;
	    <a class="action remove" href="javascript:myevents.removeAttendee('<? echo $eventKey ?>')">Remove</a></td>
<?	} else if ($editable && $added) { ?>
	<td><a class="action remove" href="javascript:myevents.removeAttendee('<? echo $eventKey ?>')">Remove</a></td>
<?	} else if ($unknown) { ?>
	<td><a class="action edit" href="javascript:myevents.editAttendee('<? echo $eventKey ?>', '<? echo $memberKey ?>', true, true)">Add</a></td>
<?	} else { ?>
	<td><a class="action edit" href="javascript:myevents.editAttendee('<? echo $eventKey ?>', '<? echo $memberKey ?>', true, false)">Edit</a></td>
<?
	}
}
?>
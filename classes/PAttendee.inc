<?
class PAttendees {
  const ATTENDEES_DIR = '_attendees';
  
  protected static $events = array();
  
  public $eventKey;
  public $members = array();
  
  /**
   * Get all the attendees for an event. 
   *
   * @return PAttendees
   */
  public static function getEventAttendees($eventKey, $refresh = false) {
    if (!isset(self::$events[$eventKey]) || $refresh) {
      // Load the file
      $filename = self::getFilename($eventKey);
      if (is_file($filename)) {
        self::$events[$eventKey] = new PAttendees($eventKey);

        include($filename);
        foreach ($attendees[$eventKey] as $memberKey => $params) {
          self::$events[$eventKey]->setAttendee($memberKey, $params);
        }
      }
    }
    if (isset(self::$events[$eventKey])) {
      return self::$events[$eventKey];
    }
    return false;
  }
  
  protected static function getFilename($eventKey) {
    global $BASE;
    return "$BASE/" . self::ATTENDEES_DIR . "/$eventKey.inc";
  }
  
  public function __construct($eventKey) {
    $this->eventKey = $eventKey;
  }
  
  public function getAttendee($memberKey, $create = false) {
    if (isset($this->members[$memberKey])) {
      return $this->members[$memberKey];
    }
    if ($create) {
      return $this->setAttendee($memberKey);
    }
    return false;
  }
  
  public function removeAttendee($memberKey) {
    unset($this->members[$memberKey]);
  }
  
  /** 
   * Update the status of an attendee
   */
  public function setAttendee($memberKey, $params = array()) {
    $params += array(
      'status' => 'Unknown',
      'guests' => 0,
    );
    
  	if ($status == "_remove") {
  		unset($this->members[$memberKey]);
  		return false;
  	}
  	
 		$this->members[$memberKey] = new PAttendee($this->eventKey, $memberKey, $params);

    // Sort the members by active status and last name
    uasort($this->members, array('PAttendees', 'sortByActiveAndLastName'));
    return $this->members[$memberKey];
  }
  
  public static function sortByActiveAndLastName(PAttendee $a, PAttendee $b) {
    if ($a == $b) {
      return 0;
    }
    $ma = $a->getMember();
    $mb = $b->getMember();
    if (!$ma && !$mb) {
      return 0;
    }
    if (!$ma) {
      return -1;
    }
    if (!$mb) {
      return 1;
    }
    return PMember::sortByActiveAndLastName($ma, $mb);
  }
  
  public function getAttendees() {
    return $this->members;
  }

  /**
   * Save changes to all attendees of an event.
   */  
  public function save() {
    $filename = self::getFilename($this->eventKey);
  
  	$fh = fopen("$filename.tmp", "w");
  	fwrite($fh, "<" . "?\n");
  	fwrite($fh, "\$attendees['" . $this->eventKey . "'] = array(\n");
  	
  	foreach ($this->members as $memberKey => $attendee) {
  		if (empty($memberKey)) {
  			continue;
  		}
  		$comma = true;
  		fwrite($fh, "    '$memberKey' => array(\n");
  		
  		foreach ($attendee as $k => $v) {
  		  if (!in_array($k, array('eventKey', 'memberKey'))) {
          fwrite($fh, "      '$k' => '$v',\n");
        }
      }
      fwrite($fh, "    ),\n");
  	}
  	fwrite($fh, ")\n?" . ">\n");
  	fclose($fh);
  	rename("$filename.tmp", $filename);
  }
  
  public function getStatusCounts() {
    global $statuses;
    
    $counts = array();
    $guests = 0;
    
    foreach ($statuses as $status) {
      $counts[$status] = 0;
    }
    
    foreach ($this->members as $memberKey => $attendee) {
      // If the attendee is not a valid member, don't include them in the count
      if (!$attendee->getMember()) {
        continue;
      }
      // If the attendee does not have a valid status, don't include them in the count
      if (!$attendee->isValidStatus()) {
        continue;
      }
      $counts[$attendee->status]++;
      $guests += $attendee->guests;
    }
    return array('statuses' => $counts, 'guests' => $guests);
  }
  
  public function getEvent() {
    return PCalender::getEvent($this->eventKey);
  }
}

class PAttendee {

  public $eventKey;
  public $memberKey;
  public $status = 'Unknown';
  public $guests = 0;  

  public function __construct($eventKey, $memberKey, $array = array()) {
    $this->eventKey = $eventKey;
    $this->memberKey = $memberKey;
    foreach ($array as $k => $v) {
      $this->$k = $v;
    }
  }
  
  public function getAttendees() {
    return PAttendees::getEvent($this->eventKey);
  }
    
  public function getMember() {
    return PMember::getMember($this->memberKey);
  }
  
  public function isValidStatus() {
    global $statuses;
    return in_array($this->status, $statuses);
  }
  
  public function insertAttendeeRow($editable, $added) {
  	global $BASE, $statuses, $user;
  	
  	$unknown = !$this->isValidStatus();
  	$member = $this->getMember();
  	if (!$member) {
  	  return;
    }
  ?>
  	<td><? echo $member->name; ?></td>
    <td class="status">
    <? if ($editable) : ?>
  	  <select name="status[<? echo $this->memberKey; ?>]"
  		  dojoType="dijit.form.FilteringSelect" 
  		  autocomplete="true"
  		  value="<? echo $unknown ? "Unknown" : $this->status ?>">
      <? if ($unknown) : ?>
  	    <option value="Unknown" selected="true">---</option>
      <? endif; ?>
  		<? foreach ($statuses as $s) : ?>
  	    <option value="<? echo $s ?>" <? echo $this->status == $s ? "selected='true'" : "" ?>><? echo $s ?></option>
      <? endforeach; ?>
  	  </select>
  	  <input type="hidden" name="remove[<? echo $this->memberKey ?>]" value="_remove" disabled="true"></input>
    <? else: ?>
      <? echo $unknown ? "---" : $this->status ?>
    <? endif; ?>
  	</td>
  	<td class="guests">
    <? if ($editable) : ?>
  	  <select name="guests[<? echo $this->memberKey; ?>]"
  	  		dojoType="dijit.form.FilteringSelect"
  			autocomplete="true"
  			value="<? echo $this->guests; ?>">
      <? for ($i = 0; $i < 10; $i++) : ?>
  			<option value="<? echo $i; ?>" <? echo $i == $this->guests ? "selected='true'" : "" ?>><? echo $i; ?></option>
      <? endfor; ?>
  	  </select> guests
    <? else : ?>
      <? echo ($this->guests > 0) ? "$this->guests guest" . ($this->guests != 1 ? 's' : '') : '&nbsp;'; ?>
    <? endif; ?>
  	</td>

  <? if ($editable && !$added) : ?>
  	<td><a class="action edit" href="javascript:events.editAttendee('<? echo $this->memberKey ?>', false)">Cancel</a> &nbsp;
  	    <a class="action remove" href="javascript:events.removeAttendee('<? echo $this->memberKey ?>')">Remove</a></td>
  	    
  <? elseif ($editable && $added) : ?>
  	<td><a class="action remove" href="javascript:events.removeAttendee('<? echo $this->memberKey ?>')">Remove</a></td>
  	
  <? elseif ($user->isAdmin() || $this->memberKey == $user->getUserKey()) : ?>
  	<td><a class="action edit" href="javascript:events.editAttendee('<? echo $this->memberKey ?>', true)">Edit</a></td>
  <? endif; ?>
  <?
  }
  
  public function insertMyEventRow($editable = false, $added = false) {
  	global $BASE, $statuses;
  	
  	$unknown = !$this->isValidStatus();	
  ?>
    <td class="status">
    <? if ($editable) : ?>
  	  <select name="status[<? echo $this->eventKey; ?>]"
  		  dojoType="dijit.form.FilteringSelect" 
  		  autocomplete="true"
  		  value="<? echo $unknown ? 'Unknown' : $this->status; ?>">
        <? if ($unknown) : ?>
    	    <option value="Unknown" selected="true">---</option>
        <? endif; ?>
        <? foreach ($statuses as $s) : ?>
  	      <option value="<? echo $s ?>" <? echo $status == $s ? "selected='true'" : ""; ?>><? echo $s ?></option>
        <? endforeach; ?>
  	  </select>
  	  <input type="hidden" name="remove[<? echo $this->eventKey; ?>]" value="_remove" disabled="true"></input>
    <? else : ?>
      <? echo $unknown ? "---" : $this->status ?>
    <? endif; ?>
  	</td>
  	<td class="guests">
    <? if ($editable) : ?>
  	  <select name="guests[<? echo $this->eventKey; ?>]"
        dojoType="dijit.form.FilteringSelect"
  			autocomplete="true"
  			value="<? echo $this->guests; ?>">
        <? for ($i = 0; $i < 10; $i++) : ?>
          <option value="<? echo $i; ?>" <? echo $i == $this->guests ? "selected='true'" : "" ?>><? echo $i; ?></option>
        <? endfor; ?>
  	  </select> guests
    <? else : ?>
      <? echo ($this->guests > 0) ? "$this->guests guest" . ($this->guests != 1 ? 's' : '') : '&nbsp;'; ?>
    <? endif; ?>
  	</td>
  	
  <? if ($editable && !$added) : ?>
  	<td><a class="action edit" href="javascript:myevents.editAttendee('<? echo $this->eventKey ?>', '<? echo $this->memberKey ?>', false, false)">Cancel</a> &nbsp;
  	    <a class="action remove" href="javascript:myevents.removeAttendee('<? echo $this->eventKey ?>')">Remove</a></td>
  
  <? elseif ($editable && $added) : ?>
  	<td><a class="action remove" href="javascript:myevents.removeAttendee('<? echo $this->eventKey ?>')">Remove</a></td>
  
  <? elseif ($unknown) : ?>
  	<td><a class="action edit" href="javascript:myevents.editAttendee('<? echo $this->eventKey ?>', '<? echo $this->memberKey ?>', true, true)">Add</a></td>
  
  <? else : ?>
  	<td><a class="action edit" href="javascript:myevents.editAttendee('<? echo $this->eventKey ?>', '<? echo $this->memberKey ?>', true, false)">Edit</a></td>
  <? endif; ?>
  <?
  }
}
?>
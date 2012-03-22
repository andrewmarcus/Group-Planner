<?
set_include_path(get_include_path() . PATH_SEPARATOR . "$BASE/../php/ZendGdata-1.0.3/library");

require_once 'Zend/Loader.php';
Zend_Loader::loadClass('Zend_Gdata');
Zend_Loader::loadClass('Zend_Gdata_AuthSub');
Zend_Loader::loadClass('Zend_Gdata_Calendar');
Zend_Loader::loadClass('Zend_Http_Client');
	
/**
 * A class for connecting to Google Calendar.
 * An instance of this class will be placed in the session to improve performance.
 */
class PCalendar {
  const DATA_DIR = '_data';

	// The GData calendar service
	private $service = NULL;
	private $sessionToken = NULL;
	
	// The ID of the calendar to use
	public $calendar = NULL;
	
	// An array mapping event IDs to unique events, even recurring ones
	public $eventTimeMap = array();

	// An array mapping event start times to their corresponding IDs.  
	// This is also stored in the session for the benefit of the Previous/Next functionality.
	public $eventIdMap = array();
	
	/**
	 * Get the singleton instance from the session
	 */
	public static function instance() {
		global $_SESSION;
		
		if (isset($_SESSION['pcalendar'])) {
			return unserialize($_SESSION['pcalendar']);
		}
		$calendar = new PCalendar();
		$calendar->store();
		return $calendar;
	}
	
	/**
	 * Save changes to this object to the session
	 */
	public function store() {
		global $_SESSION;
		$_SESSION['pcalendar'] = serialize($this);
	}

	/**
	 * Singleton constructor
	 */
	private function PCalendar() {
		global $BASE;
		include($BASE . '/' . self::DATA_DIR . '/calendar.inc');
		$this->calendar = $cal;
		
		// Create an instance of the Calendar service, redirecting the user to the AuthSub server if necessary.
		$this->service = serialize(new Zend_Gdata_Calendar($this->getAuthSubHttpClient()));
	}
	
	/**
	 * @return Zend_Gdata_Calendar
	 */
	private function getService() {
		return unserialize($this->service);
	}
	
	/** 
	 * Retrieve the current URL so that the AuthSub server knows where to
	 * redirect the user after authentication is complete.
	 *
	 * @return The URL of the current servlet, with any user hacks filtered out.
	 */
	public function getCurrentUrl() {
		global $_SERVER;
	
		// Filter php_self to avoid a security vulnerability.
		$php_request_uri = htmlentities(substr($_SERVER['REQUEST_URI'], 0, strcspn($_SERVER['REQUEST_URI'], "\n\r")), ENT_QUOTES);
	
		if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
			$protocol = 'https://';
		} else {
			$protocol = 'http://';
		}
		$host = $_SERVER['HTTP_HOST'];
		if ($_SERVER['HTTP_PORT'] != '' &&
			(($protocol == 'http://' && $_SERVER['HTTP_PORT'] != '80') ||
			($protocol == 'https://' && $_SERVER['HTTP_PORT'] != '443'))) {
			$port = ':' . $_SERVER['HTTP_PORT'];
		} 
		else {
			$port = '';
		}
		return $protocol . $host . $port . $php_request_uri;
	}
	
	/** 
	 * Obtain an AuthSub authenticated HTTP client, redirecting the user to the AuthSub server to login if necessary.
	 *
	 * @return The GData client to use for queries.  This should be wrapped as a Zend_GData_Calendar.
	 */
	function getAuthSubHttpClient() {
		global $BASE;
		
		// If there is a token file, use it to populate the session token
		if (!isset($this->token) && is_file("$BASE/" . self::DATA_DIR . '/token.inc')) {
			include_once("$BASE/" . self::DATA_DIR . '/token.inc');			
			$this->token = $sessionToken;
		}
		
		// If there is no AuthSub session or one-time token waiting for us,
		// redirect the user to the AuthSub server to get one.
		if (!isset($this->token) && !isset($_GET['token'])) {
			// Parameters to give to AuthSub server
			$next = $this->getCurrentUrl();
			$scope = "http://www.google.com/calendar/feeds/";
			$secure = false;
			$session = true;
	
			// Redirect the user to the AuthSub server to sign in
	
			$authSubUrl = Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure, $session);
			header("HTTP/1.0 307 Temporary redirect");
			header("Location: " . $authSubUrl);
			
			exit();
		}
	
		// Convert an AuthSub one-time token into a session token if needed
		if (!isset($this->token) && isset($_GET['token'])) {
			$this->token = Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
				
			// Create a file that stores the sessionToken for use every time
			$fh = fopen("$BASE/" . self::DATA_DIR . '/token.inc', "w+");
			fwrite($fh, "<" . "? \$sessionToken = " . $this->token . "; ?" . ">");
			fclose($fh);
		}
	
		// At this point we are authenticated via AuthSub and can obtain an
		// authenticated HTTP client instance
	
		// Create an authenticated HTTP client
		$client = Zend_Gdata_AuthSub::getHttpClient($this->token);
		return $client;
	}
	
	/** 
	 * Obtain the event list from GData, sort it, and place it in the session.
	 * Recurring events for the next two months are unrolled, so each event will have exactly 
	 * one start time and a unique ID.
	 *
	 * @param $practices boolean True if the weekly practices should be included in the results
	 * @param $reverse boolean True for past events, false for future events
	 */
	public function getEventMaps($practices = false, $reverse = false, $canceled = true) {
		global $BASE;
		
		$this->eventTimeMap = array();
		$this->eventIdMap = array();
	
		try {
			$query = $this->getService()->newEventQuery();
			$query->setUser($this->calendar);
			$query->setVisibility('private');
			$query->setProjection('full');
			$query->setOrderby('starttime');
			$query->setRecurrenceExpansionStart(date('Y-m-d'));
			$query->setRecurrenceExpansionEnd(date('Y-m-d', strtotime("+2 months")));
			$query->setSingleEvents(true);
			
			if ($reverse) {
				$query->setStartMin(date('Y-m-d', strtotime("-1 year")));
				$query->setStartMax(date('Y-m-d', strtotime("-1 day")));
				$query->setSortOrder('descending');
			} 
			else {
				$query->setStartMin(date('Y-m-d'));
				$query->setStartMax(date('Y-m-d', strtotime("+2 years")));
				$query->setSortOrder('ascending');
			}
			$query->setMaxResults(100);
		
			// Retrieve the event list from the calendar server
			$eventFeed = $this->getService()->getCalendarEventFeed($query);
			
			// Iterate through the list of events and add each one to a hashmap
			foreach ($eventFeed as $event) {
			
				// Include only events that have not been cancelled
				if ($event->eventStatus != "http://schemas.google.com/g/2005#event.canceled") {
				
				  // Create a wrapper around this event.
				  $e = new PEvent($event);
				
					// Include weekly practices only if $practices is true
					if ($practices == true || !$e->isWeeklyPractice()) {
						if ($canceled == true || !$e->isCanceled()) {
              // Store the event by ID
              $id = $e->getKey();
              $this->eventIdMap[$id] = $e;
							
							// Store the event ID by its start time (there should only be one, because recurring events were unrolled).
							$startTime = $event->when[0]->startTime;
							$this->eventTimeMap[$startTime][] = $id;
						}
					}
				}
			}
			if ($reverse) {
				krsort($this->eventTimeMap);
			} 
			else {
				ksort($this->eventTimeMap);
			}
			
			// If practices are being shown, go through all the events on disk and remove the ones that have already past.
			/*
			if ($practices == true) {
				if ($dh = opendir("$BASE/$this->attendeesDir")) {
					while (($file = readdir($dh)) !== false) {
						if (is_file("$BASE/$this->attendeesDir/$file")) {
							// Get the name without the .inc extension
							$name = substr($file, 0, -4);
							
							// If the filename does not appear as a key in the $eventIdMap, remove this entry
							if (!isset($this->eventIdMap[$name])) {
								unlink ("$BASE/$this->attendeesDir/$file");
							}
						}
					}
					closedir($dh);
				}
			}
			*/
		} 
		catch (Zend_Gdata_App_Exception $e) {
			echo "Error connecting to Google Calendar: " . $e->getMessage();
		}
	}
	
	/** 
	 * Get a single GData calendar event by its ID.
	 *
	 * @global $service The GData calendar service
	 * @param $id The unique identifier of the event.  Recurring events will have the unique start time attached to the end.
	 * @return The event corresponding to the identifier, or NULL if none could be found.
	 */
	public function getEvent($id, $refresh = false) {
	  if (!isset($this->eventIdMap[$id]) || $refresh) {
  		try {
  			$query = $this->getService()->newEventQuery();
  			$query->setUser($this->calendar);
  			$query->setVisibility('private');
  			$query->setProjection('full');
  			$query->setSingleEvents(true);
  			$query->setEvent($id);
  		
  			// Retrieve the event list from the calendar server
  			$event = $this->getService()->getCalendarEventEntry($query);
  			$this->eventIdMap[$id] = new PEvent($event);
  		} 
  		catch (Zend_Gdata_App_Exception $e) {
  			echo "Error: " . $e->getMessage();
  			return NULL;
  		}
    }
    if (isset($this->eventIdMap[$id])) {
      return $this->eventIdMap[$id];
    }
    return false;
	}
	
	/** 
	 * Get the index of the next event in the series, based on start time.
	 *
	 * @global $eventTimeMap An array mapping event start times to their corresponding IDs. 
	 * @param $event The current event
	 * @return The unique ID of the next event
	 */
	public function getNextId(PEvent $event) {
		$currId = $event->getKey();
		$startTime = $event->getStartTime();
		
		$prevId = NULL;
		foreach ($this->eventTimeMap as $time => $ids) {
			foreach ($ids as $id) {
				if ($prevId == $currId) {
					return $id;
				}
				$prevId = $id;
			}
		}
		return NULL;
	}
	
	/** 
	 * Get the index of the previous event in the series, based on start time.
	 *
	 * @global $eventTimeMap An array mapping event start times to their corresponding IDs. 
	 * @param $event The current event
	 * @return The unique ID of the previous event
	 */
	public function getPrevId(PEvent $event) {
		$currId = $event->getKey();
		$startTime = $event->getStartTime();
				
		$prevId = NULL;
		foreach ($this->eventTimeMap as $time => $ids) {
			foreach ($ids as $id) {
				if ($id == $currId) {
					return $prevId;
				}
				$prevId = $id;
			}
		}
		return NULL;
	}
	
	/** 
	 * Parse the given ISO Date/Time into a timestamp.
	 * 
	 * @param An ISO-8601 formatted string.
	 * @return An array of all the date and time parts.
	 */
	public static function parseISODateTime($datetime) {
		$currentTime = time();
		$matches = array();
		
		$output = array();
		$output["year"] = 2000;
		$output["month"] = 1;
		$output["day"] = 1;
		$output["hour"] = 0;
		$output["minute"] = 0;
		$output["second"] = 0;
		$output["offset"] = date("Z", $currentTime);
	
		if (preg_match("/^(\d{4})-(\d{2})-(\d{2})(?:T(\d{2}):(\d{2}):(\d{2})(?:[.]\d{3})?(([+-])(\d{2})\:(\d{2}))|Z)?/", $datetime, $matches) === 1) {
			$output["year"] = $matches[1];
			$output["month"] = $matches[2];
			$output["day"] = $matches[3];
			
			if (count($matches) > 4) {
				$output["hour"] = $matches[4];
				$output["minute"] = $matches[5];
				$output["second"] = $matches[6];
				
				if (count($matches) > 7) {
					// Calculate the custom offset.
					$customOffset = $matches[8] * 60 * 60;
					$customOffset += $matches[9] * 60;
				}
				if($matches[7] == "+") {
					$customOffset = -1 * $customOffset;
				}
				// Add the custom offset to the UTC offset to get the offset from the local timezone.
				$output["offset"] += $customOffset;
			}
		}
		
		
		// Generate the UNIX time with the wrong timezone, then add the timezone offset.
		$time = mktime($output["hour"], $output["minute"], $output["second"], $output["month"], $output["day"], $output["year"]);
		$time += $offset;
		
		$output["timestamp"] = $time;
		
		// Format the date and time and output them separately
		$output["datePart"] = date("D M d, Y", $output["timestamp"]);
		$output["timePart"] = "";
		if ($output["hour"] != 0) {
			$output["timePart"] = date("g:i a", $output["timestamp"]);
		}
		
		// Return an array of date parts
		return $output;
	}
}

class PEvent {
  protected $key;
  protected $event;
  
  
  public function __construct(Zend_Gdata_Calendar_EventEntry $event) {
    $this->event = $event;
    
    // The event key is the part of the ID after the last '/'
		$id = $event->id;
		$idx = strrpos($id, '/');
		$this->key = substr($id, $idx+1);
  }
  
	/** 
	 * Get a short id for the event.  This is the portion of the id at the end, past all the / marks.
	 *
	 * @param $event The event for which to get the unique ID.
	 * @return The unique short ID of the event. For recurring events, this will have the start time appended to the end.
	 */
	public function getKey() {
	  return $this->key;
	}
	
	/** 
	 * Get the start time of the event.  There will only ever be one start time, because all recurring events are unrolled.
	 *
	 * @param $event The event for which to get the start time.
	 * @return The start time of the event, as an ISO-8601 formatted string.
	 */
	public function getStartTime() {
		return PCalendar::parseISODateTime($this->event->when[0]->startTime);
	}
	
	public function getEndTime() {
    return PCalendar::parseISODateTime($this->event->when[0]->endTime);
	}
	
	public function getModifiedTime() {
	  return PCalendar::parseISODateTime($this->event->updated);
	}
	
	public function processDateAndTime() {
	  $startDate = $this->getStartTime();
    $endDate = $this->getEndTime();

	  $day = date("D, d", $startDate["timestamp"]);
    $day = preg_replace("/ /", "&nbsp;", $day);
			
    $time = $startDate["timePart"];
    if ($time == "") {
    	$time = "<i>All Day</i>";
    }
    
    // See if this is a multi-day event
    if ($endDate["timestamp"] > $startDate["timestamp"] + 86400) {
    	$time = "<i>Multi Day</i>";
    }
    $time = preg_replace("/ /", "&nbsp;", $time);
    
    return array('date' => $day, 'time' => $time);
	}
	
  public function getTitle() {
    return $this->event->title;
  }
  
  public function isTentative() {
    return preg_match('/^(.*)\?$/', $this->event->title);
  }
  
  public function isCanceled() {
    return preg_match('/^(.*) cancel(l?)ed$/', $this->event->title);
  }
  
  public function isWeeklyPractice() {
    return $this->event->title == "Weekly Practice";
  }
  
  public function getLocation() {
    if (isset($this->event->where[0]) && !empty($this->event->where[0]->valueString)) {
      return $this->event->where[0]->valueString;
    }
    return false;
  }
  
  public function getDetails() {
    if (isset($this->event->content) && !empty($this->event->content->text)) {
      return $this->event->content->text;
    }
    return false;
  }

  public function getPrivateDetails() {
    return PDetails::load($this->getKey());
  }
  
  public function savePrivateDetails($details) {
    PDetails::save($this->getKey(), $details);
  }
}
?>
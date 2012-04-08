<?
class PMember {
  const MEMBERS_DIR = '_members';
  
  protected static $members = false;
  
  public $key;
  public $name;
  public $email = '';
  public $phone = '';
  public $cell = '';
  public $address = '';
  public $admin = false;
  public $inactive = false;
  public $old_key = false;
  
  public function __construct($key = NULL, $array = array()) {
    if (!empty($key)) {
      $this->key = $key;
    }
    foreach ($array as $key => $val) {
      if (!empty($val)) {
        $this->$key = $val;
      }
    }
  }
  
  /**
   * Load all the members of this team into the $members array
   */
  public static function getMembers($refresh = false) {
    global $BASE;
    
    if (!self::$members || $refresh) {
    
      // Read every file in the members directory
      if ($dh = opendir($BASE . '/' . self::MEMBERS_DIR)) {
        while (($file = readdir($dh)) !== false) {
          $memberKey = substr($file, 0, -4);
          self::getMember($memberKey);
        }
        closedir($dh);
      }
    }
    return self::$members;
  }
  
  /** 
   * Load just one member into the global members array
   */
  public static function getMember($memberKey, $refresh = false) {
    if (!self::$members) {
      self::$members = array();
    }
    if (!isset(self::$members[$memberKey]) || $refresh) {
    
      // Load the member from the file
      $filename = self::getFilename($memberKey);
    	if (is_file($filename)) {
    		include_once($filename);
    		self::$members[$memberKey] = new PMember($memberKey, $members[$memberKey]);
        
        // Sort the list of members
        uasort(self::$members, array('PMember', 'sortByActiveAndLastName'));
    	}
    }
    if (isset(self::$members[$memberKey])) {
      return self::$members[$memberKey];
    }
    return false;
  }
  
  public static function getMemberKey($name) {
  	return preg_replace("/ /", "_", $name);
  }
  
  protected static function getFilename($memberKey) {
    global $BASE;
    return $BASE . '/' . self::MEMBERS_DIR . '/' . $memberKey . '.inc';
  }
  
  /**
   * Change the member's name.  The key will also be changed.
   */
  public function setName($name) {
    if ($name != $this->name) {
      // Save the old key so we can delete it later
      $this->old_key = $this->key;
      
      $this->key = self::getMemberKey($name);
      $this->name = $name;
      
      unset(self::$members[$this->old_key]);
      self::$members[$this->key] = $this;
    }
  }
  
  /** 
   * Saves changes to a member.
   */
  public function save() {
    $filename = self::getFilename($this->key);
    
    $fh = fopen($filename, "w");
    fwrite($fh, "<" . "?\n" . "\$members['" . $this->key . "'] = array(");
    foreach ($this as $k => $v) {
      if ($k != 'old_key') {
        $v = preg_replace('#\r?\n#', '\\n', $v);
        $v = str_replace("'", "\\'", $v);
      
        fwrite($fh, "  '$k' => \"$v\",\n");
      }
    }
    fwrite($fh, ");\n" . "?" . ">");
    fclose($fh);
    
    // Delete the old member, if necessary
    if (!empty($this->old_key)) {
      $filename = self::getFilename($this->old_key);
      if (is_file($filename)) {
        unlink($filename);
      }
    }
  }
  
  /**
   * Completely deletes a member record
   */
  public function delete() {
    $filename = self::getFilename($this->key);
    
  	if (is_file($filename)) {
  		unlink($filename);
  	}
  	unset(self::$members[$this->key]);
  }
  
  public function asJson() {
  	$arr = array();
  	foreach ($this as $k => $v) {
  	  $v = preg_replace('#\r?\n#', '\\n', $v);
  	  $v = str_replace("'", "\\'", $v);
  		$arr[] = "    $k : '$v'";
    }
  	return "{\n" . implode(",\n", $arr) . "}";
  }


  /** Sort member keys by their last name */
  public static function sortKeysByLastName($a, $b) {
  	if ($a == $b) {
  		return 0;
  	}
  
  	$p1 = strrpos($a, "_");
  	$p2 = strrpos($b, "_");
  	
  	$lastName1 = substr($a, $p1);
  	$lastName2 = substr($b, $p2);
  	
      if ($lastName1 == $lastName2) {
          return ($a < $b) ? -1 : 1;
      }
      return ($lastName1 < $lastName2) ? -1 : 1;
  }

  /** Sort members by their last name and active status */
  public static function sortByActiveAndLastName(PMember $a, PMember $b) {
    if ($a == $b) {
    	return 0;
    }
  
    if ($a->inactive != $b->inactive) {
      return $a->inactive - $b->inactive;  
    }
    
    $p1 = strrpos($a->name, " ");
    $p2 = strrpos($b->name, " ");
  	
    $lastName1 = substr($a->name, $p1);
    $lastName2 = substr($b->name, $p2);
  	
    if ($lastName1 == $lastName2) {
      return ($a < $b) ? -1 : 1;
    }
    return ($lastName1 < $lastName2) ? -1 : 1;
  }

}
?>
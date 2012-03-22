<?
class PDetails {
  const DETAILS_DIR = '_details';
  
  protected static function getFilename($eventKey) {
    global $BASE;
    return "$BASE/" . self::DETAILS_DIR . "/$eventKey.inc";
  }
  
  public static function load($eventKey) {
  	$filename = self::getFilename($eventKey);
  	
  	if (is_file($filename)) {
  		$fh = fopen($filename, 'r');
  		if (flock($fh, LOCK_SH)) {
  			include($filename);
  			return stripslashes($details);
  		}
  	}
  	return false;
  }
  
  public static function save($eventKey, $details) {
  	$status = NULL;
  	$details = addslashes($details);
  	$details = strip_tags($details, '<p><a><ul><li><b><strong><i><em>');
  	
  	$filename = self::getFilename($eventKey);
  	
  	$fh = fopen($filename, "w");
  	if (flock($fh, LOCK_EX)) {
  		fwrite($fh, "<" . "?\n");
  		fwrite($fh, "\$details = \"$details\"");
  		fwrite($fh, "\n?" . ">\n");
  		$status = 'Success';
  	}
  	else {
  		$status = 'File is being edited';
  	}
  	fclose($fh);
  	
  	return $status;
  }
}
?>
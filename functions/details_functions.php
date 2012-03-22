<?
$detailsDir = "$BASE/_details";

function loadPrivateDetails($eventKey) {
	global $detailsDir;
	$filename = "$detailsDir/$eventKey.inc";
	
	if (is_file($filename)) {
		$fh = fopen($filename, 'r');
		if (flock($fh, LOCK_SH)) {
			include($filename);
			return stripslashes($details);
		}
	}
	return NULL;
}

function savePrivateDetails($eventKey, $details) {
	global $detailsDir;
	
	$status = NULL;
	$details = addslashes($details);
	$details = strip_tags($details, '<p><a><ul><li><b><strong><i><em>');
	
	$filename = "$detailsDir/$eventKey.inc";
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
?>
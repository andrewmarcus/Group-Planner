<?
$acceptHeader = $_SERVER["HTTP_ACCEPT"];

if (stristr($acceptHeader, "application/vnd.wap.xhtml+xml")) {
	header("Content-Type: application/vnd.wap.xhtml+xml");
}
else if (stristr($acceptHeader, "application/xhtml+xml")) {
	header("Content-Type: application/xhtml+xml");
}
else {
	header("Content-Type: text/html");
}

echo "<" . "?xml version=\"1.0\"?" . ">";
?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">

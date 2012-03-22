<?
session_start();

if (!is_dir(dirname(__FILE__) . '/_data')) {
  header('Location: install.php');
  exit;
}

include_once("$BASE/_data/styles.inc");
include_once("$BASE/_data/status.inc");
include_once("$BASE/classes/PUserAuth.inc");
include_once("$BASE/classes/PMember.inc");
include_once("$BASE/classes/PDetails.inc");
include_once("$BASE/classes/PCalendar.inc");
include_once("$BASE/classes/PAttendee.inc");
?>
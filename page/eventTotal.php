<?
$BASE = "..";
include_once("$BASE/autoload.inc");

$eventKey = $_REQUEST["event"];

$attendees = PAttendees::getEventAttendees($eventKey);
if ($attendees) {
  $totals = $attendees->getStatusCounts();
?>
  <h2>Summary</h2>
  <table cellpadding="3" cellspacing="0" border="0">
  <? foreach ($totals['statuses'] as $s => $c) : ?>
    <tr>
      <td><? echo $s ?>:</td>
      <td><b><? echo $c ?></b></td>
    </tr>
  <? endforeach; ?>
    <tr>
      <td colspan=2><hr/></td>
    </tr>
    <tr>
      <td>Guests:</td>
      <td><b><? echo $totals['guests']; ?></b></td>
    </tr>
  </table>
<?
}
?>

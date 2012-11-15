<?
	$BASE = "..";
	include_once("$BASE/autoload.inc");
	
	header("Content-Type: text/json"); 

	$fields = array( 'name', 'email', 'phone', 'cell', 'address', 'admin', 'inactive');
	
	$values = array();
	foreach ($fields as $field) {
	  $values[$field] = '';
	  if (isset($_REQUEST[$field])) {
    	$values[$field] = $_REQUEST[$field];
    }
    if (empty($values[$field]) || $values[$field] == 'undefined') {
      $values[$field] = '';
    }
  }
  if (empty($values['name'])) {
    return;
  }
	
	if (isset($_REQUEST['memberKey'])) {
    $memberKey = $_REQUEST["memberKey"];
   	$member = PMember::getMember($memberKey);	
  }
  if (empty($member)) {
    $member = new PMember();
  }

	$member->setName($values['name']);
	$member->email = $values['email'];
	$member->phone = $values['phone'];
	$member->cell = $values['cell'];
	$member->address = $values['address'];
	
	if (isset($values['admin'])) {
  	$member->admin = !empty($values['admin']);
  }
  if (isset($values['inactive'])) {
  	$member->inactive = !empty($values['inactive']);
  }	
	$member->save();
	
	echo $member->asJson();
?>

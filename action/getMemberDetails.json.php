<?
	$BASE = "..";
	include_once("$BASE/autoload.inc");
	
	header("Content-Type: text/json"); 
	
	$memberName = $_REQUEST["name"];
	$memberKey = PMember::getMemberKey($memberName);
	
	$member = PMember::getMember($memberKey);
	if ($member) {
  	echo $member->asJson();
  }
?>
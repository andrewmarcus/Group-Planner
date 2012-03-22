<?
$BASE = "..";
include_once("$BASE/autoload.inc");

session_write_close();

$members = PMember::getMembers();
?>
<h2>Team Roster</h2>
<table cellpadding="5" cellspacing="0" border="0" width="100%">
  <tr>
    <td width="33%"><a href="javascript:members.loadMemberList()">Refresh List</a></td>
    <td>
  	  Select: 
      <select id="find_member" name="find_member"
        dojoType="dijit.form.ComboBox" 
        autocomplete="true"
        value=""
        onChange="members.highlightMember(this)">
        <option selected="true"></option>
        <? foreach ($members as $memberKey => $member) : ?>
          <option><? echo $member->name; ?></option>
        <? endforeach; ?>
      </select> &nbsp;
  	  <a href="javascript:members.highlightMe('<? echo $user->getUserName(); ?>')">Me</a>
  	</td>
  	<td width="33%" align="right">
    <? if ($user->isAdmin()) : ?>
  	  <button dojoType="dijit.form.Button" type="submit" onclick="members.showAddDialog(null, members.loadMemberList)">Add New Member</button>
    <? endif; ?>
  	</td>
  </tr>
</table>
<hr width="100%"/>

<? $inactive = false; ?>
<? foreach ($members as $key => $member) : ?>
<? 
  $classes = array( 'member-listing' );
  if ($user->getUserKey() == $key) {
    $classes[] = 'selected';
  }
?>
<? if ($member->inactive && !$inactive) : ?>
  <hr width="100%"/>
  <h2>Inactive Members</h2>
  <? $inactive = true; ?>
<? endif; ?>
 
<div class="<? echo implode(' ', $classes); ?>">
  <div class="name-line">
    <span class="name"><? echo $member->name; ?></span>
<?
		// Only allow a user to edit their own info, unless they are an administrator
		if ($user->isAdmin() || $user->getUserKey() == $key) {
?>
    &nbsp; 
    <a class="action" href="javascript:members.showEditDialog('<? echo $member->name; ?>', members.loadMemberList)">Edit</a>
<?		} ?>
  </div>
  <? if (!$user->isPublic()) : ?>
    <div class="email"><? echo $member->email; ?></div>
    <? if (!empty($member->phone)) : ?>
      <div class="phone"><? echo $member->phone; ?> (h)</div>
    <? endif; ?>
    <? if (!empty($member->cell)) : ?>
      <div class="phone"><? echo $member->cell; ?> (c)</div>
    <? endif; ?>
    <? if (!empty($member->address)) : ?>
      <div class="address"><? echo str_replace("\n", '<br>', htmlspecialchars($member->address)); ?></div>
    <? endif; ?>
  <? else : ?>
    <div class="not-available">Log in to see details</div>
  <? endif; ?>
</div>
<? endforeach; ?>

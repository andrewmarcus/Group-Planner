<?
$BASE = ".";

include_once("$BASE/version.inc");
include_once("$BASE/autoload.inc");
include_once("$BASE/_data/styles.inc");

session_write_close();

$self = $_SERVER['PHP_SELF'];

// If no user is logged in, show the login page
if (!$user->isLoggedIn()) {
	// See if there is a valid cookie set
	if (!$user->loginWithCookies()) {
		include("$BASE/page/login.php");
		exit;
	}
}
?>
<!DOCTYPE html>
<html>
<head>
  <title><? echo $styles['title']; ?> | Event Planner (<? echo $planner_version; ?>)</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <link href="styles.css.php" rel="stylesheet" type="text/css">
  <style type="text/css">
    @import "<? echo $dojo_path; ?>dijit/themes/tundra/tundra.css";
    @import "<? echo $dojo_path; ?>dojo/resources/dojo.css"
  </style>
  <script type="text/javascript" src="<? echo $dojo_path; ?>dojo/dojo.xd.js"
    djConfig="parseOnLoad: true, usePlainJson: true"></script>
  <style>
    /* NOTE: for a full screen layout, must set body size equal to the viewport. */
    html, body { height: 100%; width: 100%; margin: 0; padding: 0; }
  </style>
  <script type="text/javascript">
    dojo.require("dojo.parser");
    dojo.require("dijit.layout.ContentPane");
    dojo.require("dijit.layout.LayoutContainer");
    dojo.require("dijit.layout.TabContainer");
    dojo.require("dijit.form.ComboBox");
    dojo.require("dijit.form.FilteringSelect");
    dojo.require("dijit.Dialog");
    dojo.require("dijit.form.TextBox");
    dojo.require("dijit.form.Button");
    dojo.require("dijit.form.CheckBox");
    dojo.require("dijit.Tooltip");
    dojo.require("dijit.TitlePane");
  </script>
  <script type="text/javascript" src="js/page.js"></script>
  <script type="text/javascript" src="js/events.js"></script>
  <script type="text/javascript" src="js/members.js"></script>
  <script type="text/javascript" src="js/myevents.js"></script>
  <? if ($user->isAdmin()) { ?>
  <!--script type="text/javascript" src="js/config.js"></script-->
  <? } ?>
  
  <script type="text/javascript">
<?
	include_once("$BASE/_data/status.inc");
	echo "members.statuses = [";
	foreach ($statuses as $status) {
		echo ($status == $statuses[0] ? "" : ", ") . "'$status'";
	}
	echo "];";
?>
  </script>
</head>
<body class="tundra">
<div id="loading">Loading...</div>
<div dojoType="dijit.layout.LayoutContainer" style="width: 100%; height: 100%; padding: 0; margin: 0; border: 0;">

  <!-- Header -->
  <div dojoType="dijit.layout.ContentPane" layoutAlign="top" id="banner">
    <a href="<? echo $styles['main_site']; ?>"><img src="<? echo "$BASE/../${styles['main_logo']}" ?>" border="0"></a><br>
	<i><font size="-1">Please bookmark this page, but do not link to it directly from a public webpage.  Thanks!</font></i>
  </div>
  
  <!-- Tab Bar -->
  <div id="mainTabContainer" dojoType="dijit.layout.TabContainer" layoutAlign="client">
	
	<!-- My Events Tab -->
	<div dojoType="dijit.layout.ContentPane" title="My Events" selected="true">
	  <div id="myevents"></div>
	</div>

    <!-- Events Tab -->
    <div id="eventTab" dojoType="dijit.layout.LayoutContainer" 
	        style="width: 100%; height: 100%; padding: 0; margin: 0; border: 0; overflow: auto" 
	        title="Event Details">
      <div dojoType="dijit.layout.ContentPane" layoutAlign="left" style="width: 400px; height: 100%; overflow: auto">
        <div id="event-list"></div>
      </div>
      <div dojoType="dijit.layout.ContentPane" layoutAlign="client">
        <div id="event"></div>
      </div>
	  <div dojoType="dijit.layout.ContentPane" layoutAlign="right" style="width: 130px; height: 100%px; overflow: auto">
	  	<div id="event-total"></div>
	  </div>
    </div>
	
    <!-- Members Tab -->
    <div dojoType="dijit.layout.ContentPane" title="Members">
	  <div id="member-list"></div>
    </div>
		
  </div>
</div>
<div dojoType="dijit.Dialog" id="member_dialog" title="Add a new member" execute="members.addNewMember();" open="false">
  <form name="member_add_form" action="javascript:members.addNewMember()">
  <table>
    <tr>
      <td><label for="name">Name: </label></td>
      <td><input dojoType="dijit.form.TextBox" type="text" name="name"></td>
    </tr>
    <tr>
      <td><label for="email">Email: </label></td>
      <td><input dojoType="dijit.form.TextBox" type="text" name="email"></td>
    </tr>
    <tr>
      <td><label for="desc">Home Phone: </label></td>
      <td><input dojoType="dijit.form.TextBox" type="text" name="phone"></td>
    </tr>
    <tr>
      <td><label for="desc">Cell Phone: </label></td>
      <td><input dojoType="dijit.form.TextBox" type="text" name="cell"></td>
    </tr>
    <tr>
      <td><label for="desc">Address: </label></td>
      <td><textarea name="address" rows="2" style="width: 100%;" class="dijit dijitReset dijitTextArea"></textarea></td>
    </tr>
	<? if ($user->isAdmin()) { ?>
  	<tr>
  	  <td><label for="admin">Admin: </label></td>
  	  <td><input type="checkbox" name="admin"></td>
  	</tr>
  	<tr>
  	  <td><label for="inactive">Inactive: </label></td>
  	  <td><input type="checkbox" name="inactive"></td>
  	</tr>	
	<? } ?>
    <tr>
      <td colspan="2" align="center">
        <button dojoType="dijit.form.Button" type="submit">Add</button>
        <button dojoType="dijit.form.Button" type="cancel" onclick="members.hideAddDialog">Cancel</button>
      </td>
    </tr>
  </table>
  </form>
</div>
<div dojoType="dijit.Dialog" id="member_edit_dialog" title="Edit member" execute="members.editMember();" open="false">
  <form name="member_edit_form" action="javascript:members.editMember()">
  <table>
    <tr>
      <td><label for="name">Name: </label></td>
      <td><input dojoType="dijit.form.TextBox" type="text" name="name"></td>
    </tr>
    <tr>
      <td><label for="email">Email: </label></td>
      <td><input dojoType="dijit.form.TextBox" type="text" name="email"></td>
    </tr>
    <tr>
      <td><label for="desc">Home Phone: </label></td>
      <td><input dojoType="dijit.form.TextBox" type="text" name="phone"></td>
    </tr>
    <tr>
      <td><label for="desc">Cell Phone: </label></td>
      <td><input dojoType="dijit.form.TextBox" type="text" name="cell"></td>
    </tr>
    <tr>
      <td><label for="desc">Address: </label></td>
      <td><textarea name="address" rows="2" style="width: 100%;" class="dijit dijitReset dijitTextArea"></textarea></td>
    </tr>
	<? if ($user->isAdmin()) { ?>
  	<tr>
  	  <td><label for="admin">Admin: </label></td>
  	  <td><input type="checkbox" name="admin"></td>
  	</tr>
  	<tr>
  	  <td><label for="inactive">Inactive: </label></td>
  	  <td><input type="checkbox" name="inactive"></td>
  	</tr>	
	<? } ?>
    <tr>
      <td colspan="2" align="center">
	      <input type="hidden" name="memberKey" value=""></input>
        <button dojoType="dijit.form.Button" type="submit">Edit</button>
        <button dojoType="dijit.form.Button" type="cancel" onclick="members.hideEditDialog">Cancel</button>
      </td>
    </tr>
  </table>
  </form>
</div>
<div style="position: absolute; top: 0px; left: 0px; padding: 5px; font-size: 0.9em;">
  <a href="http://www.google.com/calendar/embed?src=<? echo $cal; ?>&ctz=America/New_York">View Google Calendar</a>
</div>
<div style="position: absolute; top: 0px; right: 0px; padding: 5px; font-size: 0.9em; z-index: 0">
<?
	$name = $user->getUserName();
	if (isset($name)) {
		echo "$name - ";
	}
	if ($user->isPublic()) {
		echo "Public - ";
	}
?>
  <a href="action/logout.php">Logout</a>
</div>
</body>
</html>

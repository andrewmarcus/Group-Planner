<?
$BASE = ".";
session_start();

include_once("$BASE/_data/styles.inc");
include_once("$BASE/autoload.inc");

session_write_close();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><? echo $styles['title']; ?> | Event Planner | Login</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <link href="styles.css.php" rel="stylesheet" type="text/css">
  <style type="text/css">
    @import "<? echo $dojo_path; ?>dijit/themes/tundra/tundra.css";
    @import "<? echo $dojo_path; ?>dojo/resources/dojo.css"
  </style>
</head>
<body class="tundra">
  <div style="text-align: center">
    <a href="<? echo $styles['main_site']; ?>"><img src="<? echo "$BASE/../${styles['main_logo']}" ?>" border="0"></a><br>
	<h2 style="text-align: center"><? echo $styles['name']; ?> Event Planner</h2>
    <p>This site requires Javascript to be enabled.  
	For a simpler version, view the <a href="<? echo "$BASE/mobile/" ?>">mobile site</a>.</p>
	
	<table cellpadding="0" cellspacing="0" border="0" align="center">
	  <tr>
	    <td>
          <form method="post" action="action/login.php" id="loginForm">
      <table cellpadding="5" cellspacing="0" border="0">
        <tr>
          <td colspan="2" class="titleBar">Login</td>
        </tr>
<?
	$reason = $user->getLoginFailedReason();
	if (!empty($reason)) {
?>
		<tr>
		  <td colspan="2" align="center" class="failed-reason">* <? echo $reason ?></td>
		</tr>
<?
	}
?>
        <tr>
          <td><label for="email">Email: </label></td>
          <td><input class="dojoTextBox dijitTextBox" type="text" name="login-email"/></td>
        </tr>
        <tr>
          <td><label for="password">Password: </label></td>
          <td><input class="dojoTextBox dijitTextBox" type="password" name="password"></td>
        </tr>
		<tr>
		  <td colspan="2">Remember Me: <input type="checkbox" class="dojoCheckBox" name="remember" value="true"></input></td>
		</tr>
        <tr>
          <td align="center" colspan="2">
            <input type="submit" class="button" value="Submit"></input>
          </td>
        </tr>
      </table>
	  </form>

		</td>
	  </tr>
	</table>
  </div>
</body>
</html>

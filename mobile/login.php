<h2><? echo $styles['name']; ?> Event Planner</h2>
<form method="post" action="doLogin.php" id="loginForm">
  <table cellpadding="5" cellspacing="0" border="0">
    <tr>
      <td colspan="2" align="center"><b>Login</b></td>
    </tr>
<?
	$reason = $user->getLoginFailedReason();
	if (!empty($reason)) {
?>
	<tr>
	  <td colspan="2" align="center"><font color="red">* <? echo $reason ?></font></td>
	</tr>
<?
	}
?>
    <tr>
      <td><label for="login-email">Email: </label></td>
      <td><input type="text" name="login-email"/></td>
    </tr>
    <tr>
      <td><label for="password">Password: </label></td>
      <td><input type="password" name="password"/></td>
    </tr>
	<tr>
	  <td colspan="2">
	  	Remember Me: <input type="checkbox" name="remember"/>
	  </td>
	</tr>
    <tr>
      <td align="center" colspan="2">
        <input type="submit" class="button" value="Submit"></input>
      </td>
    </tr>
  </table>
</form>

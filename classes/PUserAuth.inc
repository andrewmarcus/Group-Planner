<?
$user = PUserAuth::instance();

class PUserAuth {
	protected $key;
	protected $name;
	protected $admin;
	protected $public;
	protected $loggedIn;
	protected $error;
	
	/**
	 * Get the singleton instance from the session
	 */
	public static function instance() {
		global $_SESSION;
		
		if (isset($_SESSION['puserauth'])) {
			return unserialize($_SESSION['puserauth']);
		}
		$user = new PUserAuth();
		$user->store();
		return $user;
	}
	
	/**
	 * Save changes to this object to the session
	 */
	public function store() {
		$_SESSION['puserauth'] = serialize($this);
	}

	/**
	 * Singleton constructor
	 */
	public function PUserAuth() {
		$this->logout();
	}
	
	public function getUserKey() {
		return $this->key;
	}
	
	public function getUserName() {
		return $this->name;
	}
	
	/**
	 * Returns true if the logged in user is an administrator
	 */
	public function isAdmin() {
		return $this->admin;
	}
	
	/**
	 * Returns true if the logged in user is anonymous
	 */
	public function isPublic() {
		return $this->public;
	}
	
		/**
	 * Get the reason the previous login failed.  If it succeeded, this should be empty.
	 */
	public function getLoginFailedReason() {
		return $this->error;
	}
	
	/**
	 * Returns true if a user has logged in successfully (either a public user, or by email address)
	 */
	public function isLoggedIn() {
		return $this->loggedIn;
	}

	/**
	 * Store the login information in a cookie
	 */
	public function saveLogin($email) {
		setcookie("login-email", $email, strtotime('+90 days'), "/");
	}
	
	/**
	 * Remove the login informatino from the cookie
	 */
	public function clearLogin() {
		setcookie("login-email", "", time(), "/");
	}

	/**
	 * Attempt to login using cookies
	 */
	public function loginWithCookies() {
		if (isset($_COOKIE["login-email"])) {
			$success = $this->login();
			return $success;
		}
		return false;
	}
	
	/**
	 * Attempt to logon the user, with information submitted by the form.
	 * If the user is found in the system, sets some session variables and returns true.
	 */
	public function login() {
		global $_REQUEST;
		
		if (isset($_COOKIE['login-email'])) {
		  $email = $_COOKIE['login-email'];
		  $cookie = TRUE;
		}
		
		// Reset all the fields
		$this->logout();
	
		// If public access is being granted, return true (access ok)	
		if (isset($_REQUEST["public"])) {
			$this->public = true;
			$this->loggedIn = true;
			$this->store();
			return true;
		}
		
		// Passwords are not used, but for security the password field should be empty
		$password = $_REQUEST["password"];
		if (empty($password)) {
		  if (!empty($_REQUEST['login-email'])) {
  			$email = $_REQUEST["login-email"];
  		}
			
			// The email address must be set
			if (empty($email)) {
				$this->logout("Email address must be specified");
				return false;
			}
			
			// Lookup the email address in the list of members
			$members = PMember::getMembers();
			
			foreach ($members as $memberKey => $member) {
				if (strcasecmp($member->email, $email) == 0) {
					// Save the member in the session
					$this->loggedIn = true;
					$this->key = $memberKey;
					$this->name = $member->name;

					// If the member is an administrator, make a note of it
					$this->admin = !empty($member->admin);
					
					// Save the email address in a cookie, if requested to do so
					if (isset($_REQUEST["remember"]) || $cookie) {
						$this->saveLogin($email);
					}
					$this->store();
					return true;
				}
			}
			// No member with the given email address could be found, so the request is invalid.
			$this->logout("No member could be found with the given email address");
			return false;
		}
		$this->logout("Invalid password");
		return false;
	}
	
	public function logout($reason = '') {
		$this->key = NULL;
		$this->name = NULL;
		$this->admin = false;
		$this->public = false;
		$this->loggedIn = false;
		
		$this->clearLogin();
		
		if (!empty($reason)) {
			$this->error = $reason;
		} else {
			$this->error = '';
		}
		$this->store();
	}
}
?>
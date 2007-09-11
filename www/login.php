#!/usr/bin/php-cgi
<?php

require_once("../lib/page.php");
require_once("../lib/user.php");

define('STYLESHEET', 'login.xsl');

class Login {
	function loginform($failed, $pe = NULL) {
		$page = new Page(STYLESHEET);
		$dom =& $page->result;
		$rootnode = $dom->createElement("page");
		$rootnode = $dom->appendChild($rootnode);
		$rootnode->setAttribute("title", "Login page");
		$form = $rootnode->appendChild($dom->createElement("loginform"));
		if(PEAR::isError($pe)) {
			$node = $form->appendChild($dom->createElement("exception"));
			$node->appendChild($dom->createTextNode($pe->getMessage()));
		}
		if($failed)
			$form->setAttribute("failed", "true");
		if(isset($_REQUEST['redirect']))
			$form->setAttribute("redirect", $_REQUEST['redirect']);
		$page->send();
		return;
	}

	function logoutform() {
		$page = new Page(STYLESHEET);
		$dom =& $page->result;
		$rootnode = $dom->appendChild($dom->createElement("page"));
		$rootnode->setAttribute("title", "Logged out");
		$rootnode->appendChild($dom->createElement("loggedoutpage"));
		$page->send();
		return;
	}

	function loggedin() {
		global $config;

		if(isset($_REQUEST['redirect'])) {
			header("Location: ".$_REQUEST['redirect']);
			return;
		}
		
		header("Location: " . $config->base_url);
		return;
	}

	function main() {
		global $config;

		/* Logout? */
		if(isset($_REQUEST['logout'])) {
                        // Clear session
			session_unset();
			Login::logoutform();
			return;
		}
                
                // Already logged in? Show loggedin form
                if (isset($_SESSION['user']) && is_a ($_SESSION['user'], 'User'))
                    Login::loggedin();

		if($_REQUEST['action'] == "login") {
			if(!isset($_POST['login']) || !isset($_POST['password'])) {
				Login::loginform(false, $pe);
				return;
			}

			// Get posted login details
			$login = $_POST['login'];
			$password = $_POST['password'];
			
			// Test bind authenticates password
			$ldap = Login::connectToLDAP($login, $password);
			if(PEAR::isError($ldap)) {
				sleep(5);
			        Login::loginform(true, $ldap);
				return;
			}

			// If that failed to return a connection, password was wrong
			if(!$ldap) {
				//error_log("User '$login' - wrong password (LDAP bind failed).");
				sleep(5);
			        Login::loginform(true, new PEAR_Error("Authentication failed"));
				return;
			}

			// Use the connection to grab a copy of our own details
			$ldapcriteria = "(&(objectClass=posixAccount)(uid=".LDAPUtil::ldap_quote($login)."))";
			$result = ldap_search($ldap, $config->ldap_users_basedn, $ldapcriteria);
			if($result == false) {
				Login::loginform(true, new PEAR_Error("LDAP search failed: ".ldap_error($ldap)));
				return;
			}
			$entries = ldap_get_entries($ldap, $result);
			if($entries['count'] < 1) {
				// No LDAP entry for this user...
				Login::loginform(true, new PEAR_Error("Authentication failed."));
				return;
			}
			// Unlikely, but...
			if($entries['count'] > 1) {
				Login::loginform(true, new PEAR_Error("Multiple LDAP posixAccount records found for '".$login."'"));
				return;
			}
			// The presence of a 'user' entry in the session marks them as logged in
			$_SESSION['user'] = User::absorb($entries[0]);

			// What groups are we in?
			$ldapcriteria = "(&(objectClass=posixGroup)(memberUid=".LDAPUtil::ldap_quote($login)."))";
			$result = ldap_search($ldap, $config->ldap_groups_basedn, $ldapcriteria, array('cn'));
			if(!$result) {
				Login::loginform(true, new PEAR_Error("LDAP search failed: ".ldap_error($ldap)));
				return;
			}
			$entries = ldap_get_entries($ldap, $result);
			$groups = array();
			for($i = 0; $i < $entries['count']; $i++) {
				$groups[] = $entries[$i]['cn'][0];
			}
			$_SESSION['groups'] = $groups;

			// Finish up
			ldap_close($ldap);
			// If we get this far, user is authenticated
			Login::loggedin();
			return;
		}

		// No login attempt yet
		Login::loginform(false);
	}

	function connectToLDAP($uid, $password) {
		global $config;

		/* Extract the hostname */
		$url_parts = parse_url($config->ldap_url);
		$hostname = $url_parts['host'];
		
		/* Determine the DN */
		$binddn = "uid=".$uid.",".$config->ldap_users_basedn;
			
		/* Connect to the LDAP server */
		$ldap = ldap_connect($hostname);
		if(!$ldap) {
			return new PEAR_Error("Unable to connect to LDAP server");
		}
		
		/* Set protocol v3 */
		if(!ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3)) {
			return new PEAR_Error("Could not switch LDAP connection to protocol v3");
		}

		/* Bind to the LDAP server */
		$bind_result = ldap_bind($ldap, $binddn, $password);
		if($bind_result) {
			return $ldap;
		}

		/* Close and report failure */
		ldap_close($ldap);
		return false;
	}
}

require_once("common.php");

Login::main();

?>

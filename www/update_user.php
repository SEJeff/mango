#!/usr/bin/php-cgi
<?php

require_once("../lib/page.php");
require_once("../lib/user.php");
require_once("../lib/authtoken.php");

require_once("Mail.php");
require_once("Mail/mime.php");

define('STYLESHEET', 'update_user.xsl');
define('SESSIONID', 'update_user');
define('GROUP', 'accounts');

$AFFECTEDGROUPS = array(
	"gnomecvs",
	"ftpadmin",
	"gnomeweb",
	"bugzilla",
	"artweb",
	"mailusers",
	"accounts"
);

class UpdateUser {
	// Details for the user being created
	var $user;

	// Remember existing SSH keys between requests
	var $savedKeys;
	
	// Groups the user belongs to that we're not responsible for
	var $othergroups;
	
	// Tab being displayed
	var $tab;

	// An initialisation error message
	var $error;
		
	function UpdateUser($uid) {
		global $AFFECTEDGROUPS;

		$user = User::fetchUser($uid);
		if(!is_a($user, "User")) {
			$this->error = $user;
			return;
		}

		$this->user = $user;
		$this->savedKeys = $user->authorizedKeys;
		$this->othergroups = array_diff($user->groups, $AFFECTEDGROUPS);
		$this->tab = "general";
	}
		
	function main() {
		global $config;

		// Check session for previous instance
		$container = $_SESSION[SESSIONID];
		if(!is_a($container, "UpdateUser") || isset($_REQUEST['uid'])) {
			$uid = $_REQUEST['uid'];
			$container = new UpdateUser($uid);
			if($container->error) {
				Page::redirect("/list_users.php?errmsg=".urlencode($container->error));
				return;
			}
			$_SESSION[SESSIONID] = $container;
		}

		// Set up a page for tracking the response for this request
		$page = new Page(STYLESHEET);
		
		// Service the request, tracking results and output on the given DOM
		$container->service($page->result);
		
		// Send the page for post-processing and output
		$page->send();
		
		// Save anything changed in the session
		$_SESSION[SESSIONID] = $container;
	}
	
	function service(&$dom) {
		// A page node is mandatory
		$dom->append_child($pagenode = $dom->create_element("page"));
		$pagenode->set_attribute("title", "Update User '".$this->user->uid."'");

		// Security check
		if(!check_permissions($dom, $pagenode, GROUP)) return;

		// Check for a change of tab
		if(isset($_GET['tab'])) {
			$this->tab = $_GET['tab'];
		}

		// Start the page off
		$formnode = $pagenode->append_child($dom->create_element("updateuser"));
		$formnode->set_attribute("tab", $this->tab);

		// If posting details, attempt to add the new details
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$this->process($dom, $formnode);
		}
		
		// Add current details to form
		$this->user->add_to_node($dom, $formnode);
		
		// Add SSH keys with indices
		if($this->tab == "sshkeys") {
			$savedkeysnode = $formnode->append_child($dom->create_element("savedkeys"));
			foreach($this->savedKeys as $ref => $key) {
				$keynode = $savedkeysnode->append_child($dom->create_element("key"));
				$keynode->set_attribute("ref", $ref);
				$keynode->append_child($dom->create_text_node($key));
			}
		}
		
		// Add initialisation error, if any
		if(PEAR::isError($this->error)) {
			$node = $formnode->append_child($dom->create_element("error"));
			$node->append_child($dom->create_text_node($this->error->getMessage()));
		}

		return;
	}

	function process(&$dom, &$formnode) {	
		// Check ref (in case of multiple open pages)
		$uidcheck = $_POST['uidcheck'];
		if($this->user->uid != $uidcheck) {
			$user = User::fetchUser($uid);
			if(!is_a($user, "User")) {
				$this->error = $user;
				return;
			}
			$this->user = $user;
		}

		// Individual tab form handlers
		$result = null;
		if($this->tab == "general")
			$result = $this->process_general_tab($dom, $formnode);
		elseif($this->tab == "sshkeys")
			$result = $this->process_sshkeys_tab($dom, $formnode);
		elseif($this->tab == "groups")
			$result = $this->process_groups_tab($dom, $formnode);
		elseif($this->tab == "actions")
			$result = $this->process_actions_tab($dom, $formnode);

		// Report an exception
		if(PEAR::isError($result)) {
			$node = $formnode->append_child($dom->create_element("error"));
			$node->append_child($dom->create_text_node($result->getMessage()));
			return;
		}

		// Report successes
		if(is_array($result)) {
			foreach($result as $change) {
				$node = $formnode->append_child($dom->create_element("change"));
				$node->set_attribute("id", $change);
			}
		}

	}

	function process_general_tab(&$dom, &$formnode) {	
		// Read form and validate
		$this->user->cn = $_POST['cn'];
		$this->user->mail = $_POST['mail'];
		$this->user->description = $_POST['description'];
		$formerrors = $this->user->validate();
		if(count($formerrors) > 0) {
			foreach($formerrors as $error) {
				$node = $formnode->append_child($dom->create_element("formerror"));
				$node->set_attribute("type", $error);
			}
			return;
		}

		// Attempt LDAP update
		$result = $this->user->update();
		if(PEAR::isError($result))
			return $result;

		// Mark success
		$updatednode = $formnode->append_child($dom->create_element("updated"));

		return $result;
	}

	function process_sshkeys_tab(&$dom, &$formnode) {	
		// Read form and validate
		$this->user->authorizedKeys = array();
		if($_FILES['keyfile']['tmp_name']) {
			$keyfile = file_get_contents($_FILES['keyfile']['tmp_name']);
		}
		$newkeylist = $keyfile."\n".$_POST['newkeys'];
		$newkeys = split("\n", $newkeylist);
		foreach($newkeys as $key) {
			if(empty($key) || substr($key, 0, 3) != "ssh") continue;
			$this->user->authorizedKeys[] = $key;
		}

		// Deduplicate keys
		$this->user->authorizedKeys = array_unique($this->user->authorizedKeys);

		// Remember keys for next hit	
		$this->savedKeys = $this->user->authorizedKeys;

		// Attempt LDAP update
		$result = $this->user->update();
		if(PEAR::isError($result))
			return $result;

		// Mark success
		$updatednode = $formnode->append_child($dom->create_element("updated"));

		return $result;
	}
	
	function process_groups_tab(&$dom, &$formnode) {	
		global $checkforgroups;

		// Read form and validate
		$this->user->groups = array();
		foreach($_POST as $key => $value) {
			if(substr($key, 0, 6) == "group-") {
				$this->user->groups[] = substr($key, 6);
			}
			if(substr($key, 0, 14) == "authorizedKey-") {
				$i = substr($key, 14);
				if(!empty($this->savedKeys[$i]))
					$this->user->authorizedKeys[] = $this->savedKeys[$i];
			}
		}

		// Mix other groups back in
		if(is_array($this->othergroups))
			$this->user->groups = array_merge($this->user->groups, $this->othergroups);
		
		// Attempt LDAP update
		$result = $this->user->update();
		if(PEAR::isError($result))
			return $result;

		// Mark success
		$updatednode = $formnode->append_child($dom->create_element("updated"));

		return $result;
	}

	function process_actions_tab(&$dom, &$formnode) {
		global $config;

		// Was this confirmation to send?
		if(isset($_POST['confirmemail'])) {
			$to = stripslashes($_POST['to']);
			$cc = stripslashes($_POST['cc']);
			$subject = stripslashes($_POST['subject']);
			$body = stripslashes($_POST['body']);
			if($body == "") {
				return PEAR::raiseError("No mail body supplied");
			}

			// Use PEAR Mail to send the mail
			$recipients = array($to);
			if(!empty($cc)) {
				$recipients[] = $cc;
			}
			if($config->mode != "live") {
				$recipients = array($config->support_email);
			}
			$mime = new Mail_Mime();
			$mime->setTXTBody($body);
			$headers = array(
				"Reply-To" => "Mango <accounts@gnome.org>",
				"From" => "Mango <accounts@gnome.org>",
				"To" => $to,
				"Cc" => $cc,
				"Subject" => $subject,
			);
			$content = $mime->get();
			$headers = $mime->headers($headers);
			$mail = &Mail::factory('smtp');
			$error = $mail->send($recipients, $headers, $content);
			if(PEAR::isError($error))
				return $error;

			// Trigger an 'e-mail sent' page
			$formnode->append_child($dom->create_element("emailsent"));

			// No changes made
			return;
		}

		// Prepare an e-mail
		$to = $this->user->cn." <".$this->user->mail.">";
		$cc = "";
		$subject = "";

		// Was an RT number supplied?
		if(isset($_POST['rt_number'])) {
			$rt_number = $_POST['rt_number'];
			if(intval($rt_number) > 0) {
				$cc = "Accounts RT Queue <accounts@gnome.org>";
				$subject = "Re: [gnome.org #".$rt_number."] ";
			}
		}

		// Check for 'auth token' action trigger
		if(isset($_POST['sendauthtoken'])) {
			// Send a copy to the RT ticket
			$subject .= "Authentication request";

			// Generate an authentication token
			$authtoken = AuthToken::generate();

			// Prepare mail body template variables
			$maildom = domxml_new_doc("1.0");
			$mailnode = $maildom->append_child($maildom->create_element("authtokenmail"));
			$usernode = $mailnode->append_child($maildom->create_element("user"));
			$this->user->add_to_node($maildom, $usernode);
			$authtokennode = $mailnode->append_child($maildom->create_element("authtoken"));
			$authtokennode->append_child($maildom->create_text_node($authtoken));

			// Process the mail body template
			$stylesheet = domxml_open_file("../templates/authtoken_mail.xsl");
			$xsltprocessor = domxml_xslt_stylesheet_doc($stylesheet);
			$body = $xsltprocessor->process($maildom);
			$body = $xsltprocessor->result_dump_mem($body);
		
			// Put it in a confirmation form
			$formnode->append_child($dom->create_element("authorisemail"));
			$fieldnode = $formnode->append_child($dom->create_element("to"));
			$fieldnode->append_child($dom->create_text_node($to));
			$fieldnode = $formnode->append_child($dom->create_element("cc"));
			$fieldnode->append_child($dom->create_text_node($cc));
			$fieldnode = $formnode->append_child($dom->create_element("subject"));
			$fieldnode->append_child($dom->create_text_node($subject));
			$fieldnode = $formnode->append_child($dom->create_element("body"));
			$fieldnode->append_child($dom->create_text_node($body));

			return;
		}

		// Check for 'auth token' action trigger
		if(isset($_POST['sendwelcome'])) {
			// Send a copy to the RT ticket
			$subject .= "Your GNOME account is ready";

			// Prepare mail body template variables
			$maildom = domxml_new_doc("1.0");
			$mailnode = $maildom->append_child($maildom->create_element("welcomemail"));
			$usernode = $mailnode->append_child($maildom->create_element("user"));
			$this->user->add_to_node($maildom, $usernode);

			// Process the mail body template
			$stylesheet = domxml_open_file("../templates/welcome_mail.xsl");
			$xsltprocessor = domxml_xslt_stylesheet_doc($stylesheet);
			$body = $xsltprocessor->process($maildom);
			$body = $xsltprocessor->result_dump_mem($body);

			// Put it in a confirmation form
			$formnode->append_child($dom->create_element("authorisemail"));
			$fieldnode = $formnode->append_child($dom->create_element("to"));
			$fieldnode->append_child($dom->create_text_node($to));
			$fieldnode = $formnode->append_child($dom->create_element("cc"));
			$fieldnode->append_child($dom->create_text_node($cc));
			$fieldnode = $formnode->append_child($dom->create_element("subject"));
			$fieldnode->append_child($dom->create_text_node($subject));
			$fieldnode = $formnode->append_child($dom->create_element("body"));
			$fieldnode->append_child($dom->create_text_node($body));

			return;
		}
	}
}

require_once("common.php");

UpdateUser::main();

?>

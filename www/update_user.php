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
	"accounts",
	"buildmaster",
	"buildslave",
        "foundation",
	"membctte",
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

		$user = User::fetchuser($uid);
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
		// Check session for previous instance
		$container = $_SESSION[SESSIONID];
		if(!is_a($container, "UpdateUser") || isset($_REQUEST['uid'])) {
			$uid = $_REQUEST['uid'];
			$container = new UpdateUser($uid);
			if($container->error) {
				Page::sendRedirect("/list_users.php?errmsg=".urlencode($container->error));
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
		$dom->appendChild($pagenode = $dom->createElement("page"));
		$pagenode->setAttribute("title", "Update User '".$this->user->uid."'");

		// Security check
		if(!check_permissions($dom, $pagenode, GROUP)) return;

		// Check for a change of tab
		if(isset($_GET['tab'])) {
			$this->tab = $_GET['tab'];
		}

		// Start the page off
		$formnode = $pagenode->appendChild($dom->createElement("updateuser"));
		$formnode->setAttribute("tab", $this->tab);

		// If posting details, attempt to add the new details
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$this->process($dom, $formnode);
		}
		
		// Add current details to form
		$this->user->add_to_node($dom, $formnode);
		
		// Add SSH keys with indices
		if($this->tab == "sshkeys") {
			$savedkeysnode = $formnode->appendChild($dom->createElement("savedkeys"));
			foreach($this->savedKeys as $ref => $key) {
				$keynode = $savedkeysnode->appendChild($dom->createElement("key"));
                                $fingerprint = is_valid_ssh_pub_key($key, False, True);
                                if ($fingerprint !== false) {
                                    $keynode->setAttribute("fingerprint", $fingerprint);
                                }
				$keynode->setAttribute("ref", $ref);
				$keynode->appendChild($dom->createTextNode($key));
			}
		}
		
		// Add initialisation error, if any
		if(PEAR::isError($this->error)) {
			$node = $formnode->appendChild($dom->createElement("error"));
			$node->appendChild($dom->createTextNode($this->error->getMessage()));
		}

		return;
	}

	function process(&$dom, &$formnode) {	
		// Check ref (in case of multiple open pages)
		$uidcheck = $_POST['uidcheck'];
		if($this->user->uid != $uidcheck) {
			$user = User::fetchuser($uidcheck);
			if(!is_a($user, "User")) {
				$this->error = $user;
				return;
			}
			$this->user = $user;
		}

		// Individual tab form handlers
		$result = null;
                $inform_changes = false;
		if($this->tab == "general")
			$result = $this->process_general_tab($dom, $formnode);
		elseif($this->tab == "sshkeys") {
			$result = $this->process_sshkeys_tab($dom, $formnode);
                        $inform_changes = true;
                }
		elseif($this->tab == "groups") {
                        $result = $this->process_groups_tab($dom, $formnode);
                        $inform_changes = true;
                }
		elseif($this->tab == "actions")
			$result = $this->process_actions_tab($dom, $formnode);

                if ($result === true) {
                    $formerrors = $this->user->validate();
                    if(count($formerrors) > 0) {
                            foreach($formerrors as $error) {
                                    $node = $formnode->appendChild($dom->createElement("formerror"));
                                    $node->setAttribute("type", $error);
                            }
                    } else {
                        // Attempt LDAP update
                        $result = $this->user->update();
                        if(!PEAR::isError($result))
                            $formnode->appendChild($dom->createElement("updated")); // Mark success
                    }
                }

		// Report an exception
		if(PEAR::isError($result)) {
			$node = $formnode->appendChild($dom->createElement("error"));
			$node->appendChild($dom->createTextNode($result->getMessage()));
			return;
		}

		// Report successes
		if(is_array($result)) {
                    if ($inform_changes) $this->user->inform_user($result);

                    foreach($result as $change) {
                        $node = $formnode->appendChild($dom->createElement("change"));
                        foreach ($change as $key=>$val) {
                            $node->setAttribute($key, $val);
                        }
                    }
		}
	}

	function process_general_tab(&$dom, &$formnode) {	
		// Read form and validate
		$this->user->cn = $_POST['cn'];
		$this->user->mail = $_POST['mail'];
		$this->user->description = $_POST['description'];

		return true;
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
		foreach($_POST as $key => $value) {
			if(substr($key, 0, 14) == "authorizedKey-") {
				$i = substr($key, 14);
				if(!empty($this->savedKeys[$i]))
					$this->user->authorizedKeys[] = $this->savedKeys[$i];
			}
		}

		// Deduplicate keys
		$this->user->authorizedKeys = array_unique($this->user->authorizedKeys);

		// Remember keys for next hit	
		$this->savedKeys = $this->user->authorizedKeys;

		return true;
	}
	
	function process_groups_tab(&$dom, &$formnode) {	
		global $AFFECTEDGROUPS;

		// Read form and validate
		$this->user->groups = array();
		foreach($_POST as $key => $value) {
			if(substr($key, 0, 6) == "group-") {
				$this->user->groups[] = substr($key, 6);
			}
		}

		// SECURITY: Make sure the FORM submission only contained the groups allowed to be changed
		$this->user->groups = array_intersect($this->user->groups, $AFFECTEDGROUPS);

		// Mix other groups back in
		if(is_array($this->othergroups))
			$this->user->groups = array_merge($this->user->groups, $this->othergroups);
		
		return true;
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
			$params = array(
				'head_charset' => 'UTF-8',
				'head_encoding' => 'quoted-printable',
				'text_charset' => 'UTF-8',
			);
			$content = $mime->get($params);
			$headers = $mime->headers($headers);
			$mail = &Mail::factory('smtp');
			$error = $mail->send($recipients, $headers, $content);
			if(PEAR::isError($error))
				return $error;

			// Trigger an 'e-mail sent' page
			$formnode->appendChild($dom->createElement("emailsent"));

			// No changes made
			return false;
		}

		// Prepare an e-mail
		$to = '"' . $this->user->cn .'" <' . $this->user->mail . '>';
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

			return $this->_create_email_dom($dom, $formnode, 'authtokenmail', 'authtoken_mail',
							$to, $cc, $subject, array('authtoken' => $authtoken));
		}
	}


	function _create_email_dom(&$dom, &$formnode, $mailnodename, $template,
				   $to, $cc, $subject, $extra_mailnodes = NULL) {

		// Prepare mail body template variables
		$maildom = new DOMDocument('1.0','UTF-8');
		$mailnode = $maildom->appendChild($maildom->createElement($mailnodename));
		$usernode = $mailnode->appendChild($maildom->createElement("user"));
		$this->user->add_to_node($maildom, $usernode);
		
		if (!is_null($extra_mailnodes)) {
			foreach ($extra_mailnodes as $key=>$value) {
				$node = $mailnode->appendChild($maildom->createElement($key));
				$node->appendChild($maildom->createTextNode($value));
			}
		}


		// Process the mail body template
		$stylesheet = new DOMDocument('1.0','UTF-8');
		$stylesheet->loadXML(file_get_contents("../templates/$template.xsl"));
		$xsltprocessor = new XSLTProcessor();
		$xsltprocessor->importStylesheet($stylesheet);
		$body = $xsltprocessor->transformToXML($maildom);

		// Put it in a confirmation form
		$formnode->appendChild($dom->createElement("authorisemail"));
		$fieldnode = $formnode->appendChild($dom->createElement("to"));
		$fieldnode->appendChild($dom->createTextNode($to));
		$fieldnode = $formnode->appendChild($dom->createElement("cc"));
		$fieldnode->appendChild($dom->createTextNode($cc));
		$fieldnode = $formnode->appendChild($dom->createElement("subject"));
		$fieldnode->appendChild($dom->createTextNode($subject));
		$fieldnode = $formnode->appendChild($dom->createElement("body"));
		$fieldnode->appendChild($dom->createTextNode($body));

		return false;
	}

}

require_once("common.php");

UpdateUser::main();

?>

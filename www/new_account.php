#!/usr/bin/php-cgi
<?php

require_once("../lib/page.php");
require_once("../lib/module.php");
require_once("../lib/account.php");

define('STYLESHEET', 'new_account.xsl');
define('SESSIONID', 'new_account');

class NewAccount { 
	
	// Details for the account being created
	var $account;

	// Boolean to state account is added
	var $added;
	
	function NewAccount() {
		$this->account = new Account();
		$this->added = false;
	}

	
	function main() {
		global $config;

		// Check session for previous instance
		$container = $_SESSION[SESSIONID];
		if(!is_a($container, "NewAccount") || isset($_REQUEST['reload'])) {
			$container = new NewAccount();
			$_SESSION[SESSIONID] = $container;
		}
		// Set up a page for tracking the response for this request
		$page = new Page(STYLESHEET);

		$container->service ($page->result);
		// Send the page for post-processing and output
		$page->send();
		// Save anything changed in the session
		$_SESSION[SESSIONID] = $container;
	}
	
	function service(&$dom) {
		// A page node is mandatory
		$dom->appendChild($pagenode = $dom->createElement("page"));
		$pagenode->setAttribute("title", "New Account");

                // Start the page off
                $formnode = $pagenode->appendChild($dom->createElement("newaccount"));

		// If posting details, attempt to add the new details
		if($_SERVER['REQUEST_METHOD'] == 'POST' && !$this->added) {
			if ($this->process($dom, $formnode)) {
				$formnode->appendChild($dom->createElement('account_added'));
				$this->added = true;
				return;
			}
		}
		// Start the page off		
		$result = array ();
		if ($this->added) 
		    $formnode->appendChild ($dom->createElement('alreadyadded'));

		$gnomemodules = Module::listmodule($result, "devmodule");
		$translationmodules = Module::listmodule($result, "translationmodule");
		$selectednode = '';
		if ($gnomemodules['count'] > 0) { 
			for ($i = 0; $i < $gnomemodules['count']; $i++) { 
				$usernode = $formnode->appendChild($dom->createElement("gnomemodule"));
				$usernode->appendChild($node = $dom->createElement("key"));
				$node->appendChild($dom->createTextNode($gnomemodules[$i]['cn'][0]));
				$usernode->appendChild($node = $dom->createElement("value"));
				$node->appendChild($dom->createTextNode($gnomemodules[$i]['cn'][0]));
				if ($gnomemodules[$i]['cn'][0] == $this->account->gnomemodule) { 
					$usernode->appendChild($selectednode = $dom->createElement('selected'));
				}
			}
			if (!is_a($selectednode, 'DOMElement')) { 
				$formnode->appendChild ($node = $dom->createElement('disabled'));
				$node->setAttribute ('input', 'gnome');
			}
		}

		$selectednode = '';
		if ($translationmodules['count'] > 0) { 
			for ($i = 0; $i < $translationmodules['count']; $i++) { 
				$usernode = $formnode->appendChild($dom->createElement("translation"));
				$usernode->appendChild($node = $dom->createElement("key"));
				$node->appendChild($dom->createTextNode($translationmodules[$i]['cn'][0]));
				$usernode->appendChild($node = $dom->createElement("value"));
				$node->appendChild($dom->createTextNode($translationmodules[$i]['cn'][0]));
				if ($translationmodules[$i]['cn'][0] == $this->account->translation) { 
					$usernode->appendChild($selectednode = $dom->createElement('selected'));
				}
			}
			// if there's a selected translation module, don't disable the menu
			if (!is_a($selectednode, 'DOMElement')) { 
				$formnode->appendChild ($node = $dom->createElement('disabled'));
				$node->setAttribute ('input', 'translation');
			}
		}
		
		$this->account->add_to_node ($dom, $formnode);
		
		return;
	}	
	
	function readform() {
		$this->account->uid = $_POST['uid'];
		$this->account->cn = $_POST['cn'];
		$this->account->email = $_POST['email'];
		$this->account->comment = $_POST['comment'];
		$this->account->translationmodule = '';
		if (isset ($_POST['gnomesvn'])) { 
			$this->account->gnomemodule = $_POST['gnomemodule'];
			$this->account->svn_access = 'Y';
		}  
		if (isset ($_POST['translationsvn'])) {
			$this->account->translation = $_POST['translation'];
			$this->account->svn_access = 'Y';	
		}
		$this->account->absorb_input ('ftp_access');
		$this->account->absorb_input ('web_access');
		$this->account->absorb_input ('bugzilla_access');
		$this->account->absorb_input ('membctte');
		$this->account->absorb_input ('art_access');
		$this->account->absorb_input ('mail_alias');
		
                # TODO: Should show checkboxes instead of dumping this into a textbox
                $this->account->authorizationkeys = array();
		$keyfile = '';
		if(is_uploaded_file($_FILES['keyfile']['tmp_name'])) {
			$keyfile = file_get_contents($_FILES['keyfile']['tmp_name']);
		}
		$newkeylist = $keyfile."\n".$_POST['newkeys'];
		$newkeys = split("\n", $newkeylist);
		foreach($newkeys as $key) {
			if(empty($key) || substr($key, 0, 3) != "ssh") continue;
			$this->account->authorizationkeys[] = $key;
		}

		// Save any keys for the next form
		$this->account->authorizationkeys = array_unique($this->account->authorizationkeys);

		// Validate details
		$errors = $this->account->validate();

		return $errors;
	}
	
	function process (&$dom, &$formnode) { 
		$formerrors = $this->readform();
		if(count($formerrors) > 0) {
			foreach($formerrors as $error) {
				$node = $formnode->appendChild($dom->createElement("formerror"));
				$node->setAttribute("type", $error);
			}
			return false;
		}
		
		$result = $this->account->add_account();
		if (PEAR::isError ($result)) { 
			echo $result->message;
			return false;
		} else { 
			return true;
		}
	}
}

require_once("common.php");

NewAccount::main();

?>

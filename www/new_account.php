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
		$this->vouch_dev  = '';
		$this->vouch_i18n = '';
	}

	
	function main() {
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
		if ($gnomemodules['count'] > 0) { 
			for ($i = 0; $i < $gnomemodules['count']; $i++) { 
				$usernode = $formnode->appendChild($dom->createElement("gnomemodule"));
				$usernode->setAttribute('cn', $gnomemodules[$i]['cn'][0]);
				if ($this->vouch_dev == $gnomemodules[$i]['cn'][0]) { 
					$usernode->setAttribute('selected', '1');
				}
			}
		}

		if ($translationmodules['count'] > 0) { 
			for ($i = 0; $i < $translationmodules['count']; $i++) { 
				$usernode = $formnode->appendChild($dom->createElement("translation"));
				$usernode->setAttribute('cn', $translationmodules[$i]['cn'][0]);
				if ($this->vouch_i18n == $translationmodules[$i]['cn'][0]) { 
					$usernode->setAttribute('selected', '1');
				}
			}
		}
		
		$this->account->add_to_node ($dom, $formnode);
		
		return;
	}	
	
	function readform() {
		$this->account->uid = $_POST['uid'];
		$this->account->cn = $_POST['cn'];
		$this->account->mail = $_POST['mail'];
		$this->account->comment = $_POST['comment'];
		$this->vouch_dev  = isset ($_POST['vouch_dev'])  ? $_POST['vouch_dev']  : '';
		$this->vouch_i18n = isset ($_POST['vouch_i18n']) ? $_POST['vouch_i18n'] : '';

                $vouch_group = ($this->vouch_dev != '') ? $this->vouch_dev : $this->vouch_i18n;

                # groupname/inputboxname, vouchgroup
                # NOTE: If a group requires a vouch group, make sure to add it 
                # as well to the validation below!!!
                $this->account->abilities = array();
		$this->account->ability_from_form('gnomecvs', $vouch_group);
		$this->account->ability_from_form('ftpadmin', $vouch_group);
		$this->account->ability_from_form('mailusers');
		$this->account->ability_from_form('gnomeweb');
		$this->account->ability_from_form('bugzilla', 'bugzilla.gnome.org');
		$this->account->ability_from_form('artweb', 'art-web');
		$this->account->ability_from_form('membctte');
		
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
                $abilities = array_keys($this->account->abilities);
                if (count(array_intersect(array('ftpadmin', 'gnomecvs'), $abilities)) != 0
                    && (($this->vouch_dev == '' && $this->vouch_i18n == '')
                        || ($this->vouch_dev != '' && $this->vouch_i18n != '')))
                {
                    $errors[] = 'vouchers';
                }

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

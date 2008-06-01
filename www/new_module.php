#!/usr/bin/php-cgi
<?php

require_once("../lib/page.php");
require_once("../lib/module.php");

define('STYLESHEET', 'new_module.xsl');
define('SESSIONID', 'new_module');
define('GROUP', 'sysadmin');

class NewModule {
	// Details for the moule being created
	var $module;
	
	function NewModule() {
		$this->module = new Module();
	}
		
       static function main() {
		// Check session for previous instance
		$container = isset($_SESSION[SESSIONID]) ? $_SESSION[SESSIONID] : null;
		if(!$container instanceof NewModule || isset($_REQUEST['reload'])) {
			$container = new NewModule();
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
		$pagenode->setAttribute("title", "New GNOME Module");

		// Security check
		if(!check_permissions($dom, $pagenode, GROUP)) return;

		// Start the page off		
		$formnode = $pagenode->appendChild($dom->createElement("newmodule"));

		// If posting details, attempt to add the new details
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$this->process($dom, $formnode);
		}
		
		// Add users to select
		$results = array ();
		$entries = User::listusers($results);
		for($i = 0; $i < $entries['count']; $i++) {
			$usernode = $formnode->appendChild($dom->createElement("user"));
			$usernode->appendChild($node = $dom->createElement("uid"));
			$node->appendChild($dom->createTextNode($entries[$i]['uid'][0]));
			$usernode->appendChild($node = $dom->createElement("name"));
			$node->appendChild($dom->createTextNode($entries[$i]['cn'][0]));
			$usernode->appendChild($node = $dom->createElement("email"));
			$node->appendChild($dom->createTextNode($entries[$i]['mail'][0]));
		}
		// Add current details to form
		$this->module->add_to_node($dom, $formnode);
		
		return;
	}

	function process(&$dom, &$formnode) {	
		// Read form and validate
		$formerrors = $this->readform();
		if(count($formerrors) > 0) {
			foreach($formerrors as $error) {
				$node = $formnode->appendChild($dom->createElement("formerror"));
				$node->setAttribute("type", $error);
			}
			return;
		}

		// Attempt LDAP add
		$result = $this->module->addmodule();
		if(PEAR::isError($result)) {
			$node = $formnode->appendChild($dom->createElement("error"));
			$node->appendChild($dom->createTextNode($result->getMessage()));
			return;
		}

		
		
		// Report success
		if($result) {
			$node = $formnode->appendChild($dom->createElement("added"));
			$this->module->add_to_node($dom, $node);
			$this->module = new Module();
		}

		return;
	}
	
	function readform() {
		// Read details from form
		$this->module->cn = $_POST['cn'];
		$this->module->description = $_POST['description'];
		if (isset ($_POST['localizationModule']) && $_POST['localizationModule']) {
			$this->module->localizationModule = true;
			$this->module->localizationTeam = $_POST['localizationTeam'];
			$this->module->mailingList = $_POST['mailingList'];
		}
		$this->module->maintainerUids = $_POST['maintainerUids'];
		    
		// Validate details
		$errors = $this->module->validate();

		return $errors;
	}
}

require_once("common.php");

NewModule::main();

?>

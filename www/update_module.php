#!/usr/bin/php-cgi
<?php

require_once("../lib/page.php");
require_once("../lib/module.php");

define('STYLESHEET', 'update_module.xsl');
define('SESSIONID', 'update_module');
define('GROUP', 'sysadmin');


class UpdateModule {
	var $module;

	// An initialisation error message
	var $error;
		
	function UpdateModule($cn) {

		$module = Module::fetchmodule($cn);
		if(!$module instanceof Module) {
			$this->error = $module;
			return;
		}

		$this->module = $module;
	}
		
       static function main() {
		// Check session for previous instance
		$container = isset($_SESSION[SESSIONID]) ? $_SESSION[SESSIONID] : null;
		if(!$container instanceof UpdateModule || isset($_REQUEST['cn'])) {
			$cn = $_REQUEST['cn'];
			$container = new UpdateModule($cn);
			if($container->error) {
				Page::redirect("/list_moduls.php?errmsg=".urlencode($container->error));
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
		$pagenode->setAttribute("title", "Update Module '".$this->module->cn."'");

		// Security check
		if(!check_permissions($dom, $pagenode, GROUP)) return;
		
		// Start the page off
		$formnode = $pagenode->appendChild($dom->createElement("updatemodule"));

		// If posting details, attempt to add the new details
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$this->process($dom, $formnode);
		}
		
		// Add current details to form
		$this->module->add_to_node($dom, $formnode);
		
		// Add initialisation error, if any
		if(PEAR::isError($this->error)) {
			$node = $formnode->appendChild($dom->createElement("error"));
			$node->appendChild($dom->createTextNode($this->error->getMessage()));
		}

		return;
	}

	function process(&$dom, &$formnode) {	
		// Check ref (in case of multiple open pages)
/*		$cncheck = $_POST['cncheck'];
		if($this->module->cn != $cncheck) {
			$module = Module::fetchmodule($cn);
			if(!is_a($module, "Module")) {
				$this->error = $module;
				return;
			}
			$this->module = $module;
		}
*/
		// Individual tab form handlers
		$result = null;

		// Report an exception
		if(PEAR::isError($result)) {
			$node = $formnode->appendChild($dom->createElement("error"));
			$node->appendChild($dom->createTextNode($result->getMessage()));
			return;
		}
				// Read form and validate
		$this->module->cn = $_POST['cn'];
		$this->module->sn = $_POST['cn'];
		$this->module->maintainerUids = $_POST['maintainerUids'];
		$this->module->description = $_POST['description'];
		$this->module->localizationModule = $_POST['localizationModule'];
		if ($this->module->localizationModule) { 
			$this->module->localizationTeam = $_POST['localizationTeam'];
			$this->module->mailingList = $_POST['mailingList'];
		} else {
			$this->module->localizationModule = '';
			$this->module->localizationTeam = '';
		}
		$formerrors = $this->module->validate();
		if(count($formerrors) > 0) {
			foreach($formerrors as $error) {
				$node = $formnode->appendChild($dom->createElement("formerror"));
				$node->setAttribute("type", $error);
			}
			return;
		}

		// Attempt LDAP update
		$result = $this->module->update();
		if(PEAR::isError($result))
			return $result;

		// Mark success
		$updatednode = $formnode->appendChild($dom->createElement("updated"));
		$updatednode->appendChild($node = $dom->createElement('cn'));
		$node->appendChild($dom->createTextNode($this->module->cn));
		// Report successes
		if(is_array($result)) {
			foreach($result as $change) {
				$node = $formnode->appendChild($dom->createElement("changed"));
				$node->setAttribute("cn", $change);
			}
		}

	}
}

require_once("common.php");

UpdateModule::main();

?>

#!/usr/bin/php-cgi
<?php

require_once("../lib/page.php");
require_once("../lib/ftpmirror.php");

define('STYLESHEET', 'update_ftpmirror.xsl');
define('SESSIONID', 'update_ftpmirror');
define('GROUP', 'sysadmin');

class UpdateFTPMirror {
	// Details for the mirror being updated
	var $ftpmirror;

	var $error;
		
	function UpdateFTPMirror($id) {
		global $affectedgroups;

		$ftpmirror = FTPMirror::fetchmirror($id);
		if(!is_a($ftpmirror, "FTPMirror")) {
			$this->error = $ftpmirror;
			return;
		}

		$this->ftpmirror = $ftpmirror;
	}
		
	function main() {
		global $config;

		// Check session for previous instance
		$container = $_SESSION[SESSIONID];
		if(!is_a($container, "UpdateFTPMirror") || isset($_REQUEST['id'])) {
			$id = $_REQUEST['id'];
			$container = new UpdateFTPMirror($id);
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
		$pagenode->set_attribute("title", "Update FTP Mirror '".$this->ftpmirror->id."'");

		// Security check
		if(!check_permissions($dom, $pagenode, GROUP)) return;

		// Start the page off		
		$formnode = $pagenode->append_child($dom->create_element("updateftpmirror"));

		// If posting details, attempt to add the new details
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$this->process($dom, $formnode);
		}
		
		// Add current details to form
		$this->ftpmirror->add_to_node($dom, $formnode);
		
		// Add initialisation error, if any
		if(PEAR::isError($this->error)) {
			$node = $formnode->append_child($dom->create_element("error"));
			$node->append_child($dom->create_text_node($this->error->getMessage()));
		}

		return;
	}

	function process(&$dom, &$formnode) {	
		// Check ref (in case of multiple open pages)
		$idcheck = $_POST['idcheck'];
		if($this->ftpmirror->id != $idcheck) {
			$ftpmirror = FTPMirror::fetchmirror($id);
			if(!is_a($ftpmirror, "FTPMirror")) {
				$this->error = $ftpmirror;
				return;
			}
			$this->ftpmirror = $ftpmirror;
		}
		
		// Read form and validate
		$formerrors = $this->readform();
		if(count($formerrors) > 0) {
			foreach($formerrors as $error) {
				$node = $formnode->append_child($dom->create_element("formerror"));
				$node->set_attribute("type", $error);
			}
			return;
		}

		// Attempt MySQL update
		$result = $this->ftpmirror->update();
		if(PEAR::isError($result)) {
			$node = $formnode->append_child($dom->create_element("error"));
			$node->append_child($dom->create_text_node($result->getMessage()));
			return;
		}
		
		// Report success
		if(is_array($result)) {
			$updatednode = $formnode->append_child($dom->create_element("updated"));
			foreach($result as $change) {
				$node = $updatednode->append_child($dom->create_element("change"));
				$node->set_attribute("id", $change);
			}
		}

		return;
	}
	
	function readform() {
		// Read details from form
		$this->ftpmirror->name = $_POST['name'];
		$this->ftpmirror->location = $_POST['location'];
		$this->ftpmirror->url = $_POST['url'];
		$this->ftpmirror->email = $_POST['email'];
		$this->ftpmirror->description = $_POST['description'];
		$this->ftpmirror->comments = $_POST['comments'];
		$this->ftpmirror->active = isset($_POST['active']);

		// Validate details
		$errors = $this->ftpmirror->validate();

		return $errors;
	}
}

require_once("common.php");

UpdateFTPMirror::main();

?>

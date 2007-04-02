#!/usr/bin/php-cgi
<?php

require_once("../lib/page.php");
require_once("../lib/ftpmirror.php");

define('STYLESHEET', 'new_ftpmirror.xsl');
define('SESSIONID', 'new_ftpmirror');
define('GROUP', 'sysadmin');

class NewFTPMirror {
	// Details for the ftpmirror being created
	var $ftpmirror;
	
	function NewFTPMirror() {
		$this->ftpmirror = new FTPMirror();
	}
		
	function main() {
		global $config;

		// Check session for previous instance
		$container = $_SESSION[SESSIONID];
		if(!is_a($container, "NewFTPMirror") || isset($_REQUEST['reload'])) {
			$container = new NewFTPMirror();
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
		$pagenode->set_attribute("title", "New FTP Mirror");

		// Security check
		if(!check_permissions($dom, $pagenode, GROUP)) return;

		// Start the page off		
		$formnode = $pagenode->append_child($dom->create_element("newftpmirror"));

		// If posting details, attempt to add the new details
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$this->process($dom, $formnode);
		}
		
		// Add current details to form
		$this->ftpmirror->add_to_node($dom, $formnode);
		
		return;
	}

	function process(&$dom, &$formnode) {	
		// Read form and validate
		$formerrors = $this->readform();
		if(count($formerrors) > 0) {
			foreach($formerrors as $error) {
				$node = $formnode->append_child($dom->create_element("formerror"));
				$node->set_attribute("type", $error);
			}
			return;
		}

		// Attempt MySQL add
		$result = $this->ftpmirror->addmirror();
		if(PEAR::isError($result)) {
			$node = $formnode->append_child($dom->create_element("error"));
			$node->append_child($dom->create_text_node($result->getMessage()));
			return;
		}
		
		// Report success
		if($result) {
			$node = $formnode->append_child($dom->create_element("added"));
			$this->ftpmirror->add_to_node($dom, $node);
			$this->ftpmirror = new FTPMirror();
		}

		return;
	}
	
	function readform() {
		global $checkforgroups;
		
		// Read details from form
		$this->ftpmirror->name = $_POST['name'];
		$this->ftpmirror->url = $_POST['url'];
		$this->ftpmirror->location = $_POST['location'];
		$this->ftpmirror->email = $_POST['email'];
		$this->ftpmirror->description = $_POST['description'];
		$this->ftpmirror->comments = $_POST['comments'];
		
		// Validate details
		$errors = $this->ftpmirror->validate();

		return $errors;
	}
}

require_once("common.php");

NewFTPMirror::main();

?>

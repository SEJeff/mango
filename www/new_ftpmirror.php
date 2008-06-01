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
		
       static function main() {
		// Check session for previous instance
		$container = isset($_SESSION[SESSIONID]) ? $_SESSION[SESSIONID] : null;
		if(!$container instanceof NewFTPMirror || isset($_REQUEST['reload'])) {
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
		$dom->appendChild($pagenode = $dom->createElement("page"));
		$pagenode->setAttribute("title", "New FTP Mirror");

		// Security check
		if(!check_permissions($dom, $pagenode, GROUP)) return;

		// Start the page off		
		$formnode = $pagenode->appendChild($dom->createElement("newftpmirror"));

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
				$node = $formnode->appendChild($dom->createElement("formerror"));
				$node->setAttribute("type", $error);
			}
			return;
		}

		// Attempt MySQL add
		$result = $this->ftpmirror->addmirror();
		if(PEAR::isError($result)) {
			$node = $formnode->appendChild($dom->createElement("error"));
			$node->appendChild($dom->createTextNode($result->getMessage()));
			return;
		}
		
		// Report success
		if($result) {
			$node = $formnode->appendChild($dom->createElement("added"));
			$this->ftpmirror->add_to_node($dom, $node);
			$this->ftpmirror = new FTPMirror();
		}

		return;
	}
	
	function readform() {
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

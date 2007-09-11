#!/usr/bin/php-cgi
<?php

require_once("../lib/page.php");
require_once("../lib/paged_results.php");
require_once("../lib/account.php");

define('STYLESHEET', 'list_accounts.xsl');
define('SESSIONID', 'list_accounts');
define('GROUP', 'accounts');

class ListAccounts { 
	var $accounts;
	
	var $error;
	
	function reload() {
		global $config;
		
		unset($this->error);

		// Create an empty resultset in case of problems
		$results = array();
		$this->accounts = new PagedResults($results);
		
		$results = Account::get_pending_actions('accountsteam');
		$this->users = new PagedResults($results);
	}
	
	
	function main() {
		global $config;

		// Check session for previous instance
		$container = $_SESSION[SESSIONID];
		if(!is_a($container, "ListAccounts") || isset($_REQUEST['reload'])) {
			$container = new ListAccounts();
			$container->reload();
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
		// Page node is mandatory
		$dom->appendChild($pagenode = $dom->createElement("page"));
		$pagenode->setAttribute("title", "List Accounts");

		// Security check
		if(!check_permissions($dom, $pagenode, GROUP)) return;

		// Start the page off		
		$listnode = $pagenode->appendChild($dom->createElement("listaccounts"));

		// Check for page change
		if(isset($_REQUEST['page'])) {
			$this->accounts->goto_page($_REQUEST['page']);
		}

		// Gather results for this page
		$results = $this->accounts->for_page();
		if(PEAR::isError($results)) {
			$node = $listnode->appendChild($dom->createElement("error"));
			$node->appendChild($dom->createTextNode($results->getMessage()));
			return;
		}

		// Display results for this page
		$result = $this->add_entries($dom, $listnode, $results);
		if(PEAR::isError($result)) {
			$node = $listnode->appendChild($dom->createElement("error"));
			$node->appendChild($dom->createTextNode($result->getMessage()));
			return;
		}

		// Display navigation information
		$this->users->add_navinfo_to($dom, $listnode);
		
		// Display the initialisation error (to explain a possible lack of results)
		if(isset($this->error)) {
			$node = $listnode->appendChild($dom->createElement("error"));
			$node->appendChild($dom->createTextNode((PEAR::isError($this->error) ? $this->error->getMessage() : $this->error)));
		}

		// Display a passed-in error message
		if(isset($_REQUEST['errmsg'])) {
			$errmsg = $_REQUEST['errmsg'];
			$node = $listnode->appendChild($dom->createElement("error"));
			$node->appendChild($dom->createTextNode($errmsg));
		}
	}
	
	function add_entries(&$dom, &$listnode, &$results) {
		global $config;

		// Get entries from database server
		$entries = Account::get_pending_actions('accountsteam');
		// Add entries to page
		foreach ($entries as $entry) {
			// TODO: get all the other maintaiers for ftp_access etc. 
			$modules = array (
			                 'svn_access' => (($entry->gnomemodule == '') ? $entry->translation : $entry->gnomemodule), 
			                 'ftp_access' => 'Ftp Access', 
			                 'web_access' => 'Web Access', 
			                 'bugzilla_access' => 'Bugzilla Access',
			                 'art_access' => 'Web Art Access',
			                 'mail_alias' => 'Mail Alias');
			$users = array ();
			foreach ($entry->abilities as $ability) { 
			    $users[$ability] = User::fetchuser($entry->$ability);
			} 
			$usernode = $listnode->appendChild($dom->createElement("account"));
			$usernode->appendChild($node = $dom->createElement("uid"));
			$node->appendChild($dom->createTextNode($entry->uid));
			$usernode->appendChild($node = $dom->createElement("name"));
			$node->appendChild($dom->createTextNode($entry->cn));
			$usernode->appendChild($node = $dom->createElement("email"));
			$node->appendChild($dom->createTextNode($entry->email));
			$usernode->appendChild($node = $dom->createElement("createdon"));
			$node->appendChild($dom->createTextNode($entry->timestamp));
			// list all the results
		    foreach ($users as $key=>$user) { 
				$usernode->appendChild($cnode = $dom->createElement("approvedby"));
				$cnode->appendChild($node = $dom->createElement('name'));
				$node->appendChild($dom->createTextNode($user->cn));
				$cnode->appendChild($node = $dom->createElement('email'));
				$node->appendChild($dom->createTextNode($user->mail));
				$cnode->appendChild($node = $dom->createElement('module'));
				$node->appendChild($dom->createTextNode($modules[$key]));
			}
			
		}
	}
}

require_once("common.php");

ListAccounts::main();

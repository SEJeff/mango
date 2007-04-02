#!/usr/bin/php
<?php

require_once("../lib/page.php");
require_once("../lib/paged_results.php");
require_once("../lib/user.php");

define('STYLESHEET', 'list_users.xsl');
define('SESSIONID', 'list_users');
define('GROUP', 'accounts');

class ListUsers {
	// A PagedResults containing the keys of the users currently selected
	var $users;

	// Filter keyword for narrowing down results
	var $filter_keyword;
	
	// An initialisation error message
	var $error;
	
	function reload() {
		global $config;
		
		unset($this->error);

		// Create an empty resultset in case of problems
		$results = array();
		$this->users = new PagedResults($results);
		
		// Get relevant entries from LDAP server
		$ldapcriteria = "";
		if(!empty($this->filter_keyword)) {
			$keyword = $this->filter_keyword;
			$ldapcriteria .= "(|".
				"(uid=*".$keyword."*)".
				"(cn=*".$keyword."*)".
				"(mail=*".$keyword."*)".
				")";
		}
		if(!empty($ldapcriteria)) {
			$ldapcriteria = "(&(objectClass=posixAccount)".$ldapcriteria.")";
		}
		else {
			$ldapcriteria = "(objectClass=posixAccount)";
		}

		// Connect to LDAP server
		$ldap = LDAPUtil::connectToLDAP();
		if(PEAR::isError($ldap)) {
			$this->error = $ldap;
			return;
		}
		if(!$ldap) {
			$this->error = "LDAP authentication failed";
			return;
		}
		$result = ldap_search($ldap, $config->ldap_users_basedn, $ldapcriteria, array('uid'));
		if(!$result) {
			$this->error = "LDAP search failed: ".ldap_error($ldap);
			return;
		}
		$entries = ldap_get_entries($ldap, $result);
		ldap_close($ldap);
		
		// Gather uids
		for($i = 0; $i < $entries['count']; $i++) {
			$results[] = $entries[$i]['uid'][0];
		}
		
		sort($results);
		$this->users = new PagedResults($results);
	}
	
	function main() {
		global $config;

		// Check session for previous instance
		$container = $_SESSION[SESSIONID];
		if(!is_a($container, "ListUsers") || isset($_REQUEST['reload'])) {
			$container = new ListUsers();
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
		$dom->append_child($pagenode = $dom->create_element("page"));
		$pagenode->set_attribute("title", "List Users");

		// Security check
		if(!check_permissions($dom, $pagenode, GROUP)) return;

		// Start the page off		
		$listnode = $pagenode->append_child($dom->create_element("listusers"));

		// Check for page change
		if(isset($_REQUEST['page'])) {
			$this->users->goto_page($_REQUEST['page']);
		}

		// If filter changes specified...			
		if(isset($_REQUEST['filter_keyword'])) {
			$this->filter_keyword = $_REQUEST['filter_keyword'];
			$this->reload();
		}

		// Gather results for this page
		$results = $this->users->for_page();
		if(PEAR::isError($results)) {
			$node = $listnode->append_child($dom->create_element("error"));
			$node->append_child($dom->create_text_node($results->getMessage()));
			return;
		}

		// Display results for this page
		$result = $this->add_entries($dom, $listnode, $results);
		if(PEAR::isError($result)) {
			$node = $listnode->append_child($dom->create_element("error"));
			$node->append_child($dom->create_text_node($result->getMessage()));
			return;
		}

		// Display filter settings
		$filternode = $listnode->append_child($dom->create_element("filter"));
		$subnode = $filternode->append_child($dom->create_element("keyword"));
		$subnode->append_child($dom->create_text_node($this->filter_keyword));

		// Display navigation information
		$this->users->add_navinfo_to($dom, $listnode);
		
		// Display the initialisation error (to explain a possible lack of results)
		if(isset($this->error)) {
			$node = $listnode->append_child($dom->create_element("error"));
			$node->append_child($dom->create_text_node((PEAR::isError($this->error) ? $this->error->getMessage() : $this->error)));
		}

		// Display a passed-in error message
		if(isset($_REQUEST['errmsg'])) {
			$errmsg = $_REQUEST['errmsg'];
			$node = $listnode->append_child($dom->create_element("error"));
			$node->append_child($dom->create_text_node($errmsg));
		}
	}

	function add_entries(&$dom, &$listnode, &$results) {
		global $config;

		// Get entries from LDAP server
		$entries = User::listUsers($results);
				
		// Add entries to page
		for($i = 0; $i < $entries['count']; $i++) {
			$usernode = $listnode->append_child($dom->create_element("user"));
			$usernode->append_child($node = $dom->create_element("uid"));
			$node->append_child($dom->create_text_node($entries[$i]['uid'][0]));
			$usernode->append_child($node = $dom->create_element("name"));
			$node->append_child($dom->create_text_node($entries[$i]['cn'][0]));
			$usernode->append_child($node = $dom->create_element("email"));
			$node->append_child($dom->create_text_node($entries[$i]['mail'][0]));
		}
	}
}

require_once("common.php");

ListUsers::main();

?>

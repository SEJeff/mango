#!/usr/bin/php
<?php

require_once("../lib/page.php");
require_once("../lib/paged_results.php");
require_once("../lib/ftpmirror.php");

define('STYLESHEET', 'list_ftpmirrors.xsl');
define('SESSIONID', 'list_ftpmirrors');
define('GROUP', 'sysadmin');

class ListFTPMirrors {
	// A PagedResults containing the keys of the ftpmirrors currently selected
	var $ftpmirrors;

	// Filters for narrowing down results
	var $filter_keyword;
	
	// An initialisation error message
	var $error;
	
	function reload() {
		global $config;
		
		// Create an empty resultset in case of problems
		$results = array();
		$this->ftpmirrors = new PagedResults($results);
		
		// Get database connection
		$db = MySQLUtil::connectToMySQL($config->mirrors_db_url);
		if(PEAR::isError($db)) {
			$this->error = $db;
			return;
		}
		if(!$db) {
			$this->error = PEAR::raiseError("MySQL connection failed unexpectedly");
			return;
		}

		// Perform query
		$criteria = "";
		if(!empty($this->filter_keyword)) {
			$keyword = $this->filter_keyword;
			$criteria = " WHERE (name LIKE \"%".$keyword."%\"";
			$criteria .= " OR url LIKE \"%".$keyword."%\")";
		}
		$query = "SELECT id FROM ftpmirrors ".$criteria." ORDER BY location";
		$result = mysql_query($query, $db);
		if(!$result) {
			$this->error = PEAR::raiseError("Database error: ".mysql_error());
			return;
		}

		// Gather results
		$results = array();
		while($record = mysql_fetch_object($result)) {
			$results[] = $record->id;
		}
		
		$this->ftpmirrors = new PagedResults($results);
	}
	
	function main() {
		global $config;

		// Check session for previous instance
		$container = $_SESSION[SESSIONID];
		if(!is_a($container, "ListFTPMirrors") || isset($_REQUEST['reload'])) {
			$container = new ListFTPMirrors();
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
		$pagenode->set_attribute("title", "List FTP Mirrors");

		// Security check
		if(!check_permissions($dom, $pagenode, GROUP)) return;

		// Start the page off		
		$listnode = $pagenode->append_child($dom->create_element("listftpmirrors"));

		// Check for page change
		if(isset($_REQUEST['page'])) {
			$this->ftpmirrors->goto_page($_REQUEST['page']);
		}

		// If filter changes specified...			
		if(isset($_REQUEST['filter_keyword'])) {
			$this->filter_keyword = $_REQUEST['filter_keyword'];
			$this->reload();
		}
		
		// Gather results for this page
		$results = $this->ftpmirrors->for_page();
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
		$keywordnode = $filternode->append_child($dom->create_element("keyword"));
		$keywordnode->append_child($dom->create_text_node($this->filter_keyword));

		// Display navigation information
		$this->ftpmirrors->add_navinfo_to($dom, $listnode);
		
		// Display the initialisation error (to explain a possible lack of results)
		if(isset($this->error)) {
			$node = $listnode->append_child($dom->create_element("error"));
			$node->append_child($dom->create_text_node((PEAR::isError($this->error) ? $this->error->getMessage() : $this->error)));
			unset($this->error);
		}		
	}

	function add_entries(&$dom, &$listnode, &$results) {
		global $config;

		// Get entries from LDAP server
		$entries = FTPMirror::listmirrors($results);
		if(PEAR::isError($entries)) return $entries;
		if(!is_array($entries)) return;
		
		// Add entries to page
		foreach($entries as $entry) {
			$ftpmirrornode = $listnode->append_child($dom->create_element("ftpmirror"));
			$entry->add_to_node($dom, $ftpmirrornode);
		}
	}
}

require_once("common.php");

ListFTPMirrors::main();

?>

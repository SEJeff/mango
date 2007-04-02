#!/usr/bin/php-cgi
<?php

require_once("../lib/page.php");
require_once("../lib/paged_results.php");
require_once("../lib/foundationmember.php");

define('STYLESHEET', 'list_foundationmembers.xsl');
define('SESSIONID', 'list_foundationmembers');
define('GROUP', 'membctte');

class ListFoundationMembers {
	// A PagedResults containing the keys of the foundationmembers currently selected
	var $foundationmembers;

	// Filters for narrowing down results
	var $filter_name;
	var $filter_old;
	
	// An initialisation error message
	var $error;
	
	function reload() {
		global $config;
		
		unset($this->error);

		// Create an empty resultset in case of problems
		$results = array();
		$this->foundationmembers = new PagedResults($results);
		
		// Get database connection
		$db = MySQLUtil::connectToMySQL($config->membership_db_url);
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
		if(!empty($this->filter_name)) {
			$criteria = " WHERE (firstname LIKE \"%".$this->filter_name."%\"";
			$criteria .= " OR lastname LIKE \"%".$this->filter_name."%\"";
			$criteria .= " OR email LIKE \"%".$this->filter_name."%\")";
		}
		if (!empty($this->filter_old) &&
		    ($this->filter_old == "current" || $this->filter_old == "needrenewal")) {
			if(!empty($this->filter_name)) {
				$criteria .= " AND ";
			} else {
				$criteria = " WHERE ";
			}
			if ($this->filter_old == "current") {
				$op = "<=";
			} else if ($this->filter_old == "needrenewal") {
				$op = ">";
			}
			$criteria .= "DATE_SUB(CURDATE(), INTERVAL 2 YEAR) ".$op." last_renewed_on";
		}
		$query = "SELECT id FROM foundationmembers ".$criteria." ORDER BY lastname, firstname";
		error_log("$query");
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
		
		$this->foundationmembers = new PagedResults($results);
	}
	
	function main() {
		global $config;

		// Check session for previous instance
		$container = $_SESSION[SESSIONID];
		if(!is_a($container, "ListFoundationMembers") || isset($_REQUEST['reload'])) {
			$container = new ListFoundationMembers();
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
		$pagenode->set_attribute("title", "List Foundation Members");

		// Security check
		if(!check_permissions($dom, $pagenode, GROUP)) return;

		// Start the page off		
		$listnode = $pagenode->append_child($dom->create_element("listfoundationmembers"));

		// Check for page change
		if(isset($_REQUEST['page'])) {
			$this->foundationmembers->goto_page($_REQUEST['page']);
		}

		// If filter changes specified...			
		$reload = false;
		if(isset($_REQUEST['filter_name'])) {
			$this->filter_name = $_REQUEST['filter_name'];
			$reload = true;
		}
		if(isset($_REQUEST['filter_old'])) {
			$this->filter_old = $_REQUEST['filter_old'];
			$reload = true;
		}
		// ...other filters...
		if (isset($_REQUEST['renew'])) {
			$foundationmember = FoundationMember::fetchmember($_REQUEST['renew']);
			if(!is_a($foundationmember, "FoundationMember")) {
				$this->error = $foundationmember;
			} else {
				$buffer = $foundationmember->renew();
				if (isset($buffer) && $buffer != "")
					//TODO is not shown???
					$this->error = $buffer;
				else {
        	$membername = $foundationmember->firstname.' '. $foundationmember->lastname;
        	$to =  $foundationmember->email;
        	$cc = 'membership-committee@gnome.org';
        	$subject = "GNOME Foundation Membership - Renewal accepted";
        	$body = file_get_contents('renewal-template.txt');
        	// Replacing member name to template-mail body
        	$body = str_replace('<member>', $membername, $body);
        	$recipients = array ($to, $cc);
        	$mime = new Mail_Mime();
        	$mime->setTXTBody($body);
        	$headers = array(
          	"Reply-To" => "<membership-committee@gnome.org>",
          	"From" => "GNOME Foundation Membership Committee <membership-committee@gnome.org>",
          	"To" => $to,
          	"Cc" => $cc,
          	"Subject" => $subject,
       	 	);
       		$content = $mime->get();
        	$headers = $mime->headers($headers);
					$mail = &Mail::factory('smtp');
		      $error = $mail->send($recipients, $headers, $content);

				  if(PEAR::isError($error))
    	      return $error;
					$node = $listnode->append_child($dom->create_element("emailsent"));
				  $node = $listnode->append_child($dom->create_element("renewed"));
				  $listnode->set_attribute("name", $foundationmember->firstname.' '.$foundationmember->lastname);
					$reload = true;
				}
			}
		}

		if($reload) {
			$this->reload();
		}
			
		// Gather results for this page
		$results = $this->foundationmembers->for_page();
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
		$filternamenode = $listnode->append_child($dom->create_element("filter_name"));
		$filternamenode->append_child($dom->create_text_node($this->filter_name));
		//TODO select the right one
		$filteroldnode = $listnode->append_child($dom->create_element("filter_old"));

		// Display navigation information
		$this->foundationmembers->add_navinfo_to($dom, $listnode);
		
		// Display the initialisation error (to explain a possible lack of results)
		if(isset($this->error)) {
			$node = $listnode->append_child($dom->create_element("error"));
			$node->append_child($dom->create_text_node((PEAR::isError($this->error) ? $this->error->getMessage() : $this->error)));
		}		
	}

	function add_entries(&$dom, &$listnode, &$results) {
		global $config;

		// Get entries from LDAP server
		$entries = FoundationMember::listmembers($results);
		if(PEAR::isError($entries)) return $entries;
		if(!is_array($entries)) return;
		
		// Add entries to page
		foreach($entries as $entry) {
			$foundationmembernode = $listnode->append_child($dom->create_element("foundationmember"));
			$foundationmembernode->set_attribute("member", ($entry->resigned_on == null));
			$entry->add_to_node($dom, $foundationmembernode);
		}
	}
}

require_once("common.php");

ListFoundationMembers::main();

?>

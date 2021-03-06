#!/usr/bin/php-cgi
<?php

require_once("../lib/page.php");
require_once("../lib/paged_results.php");
require_once("../lib/foundationmember.php");
require_once('../lib/util.php');

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
        $db = MySQLUtil::singleton($config->membership_db_url)->dbh();

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
            $sql_filter_name = MySQLUtil::escape_string("%".$this->filter_name."%");
            $criteria = " WHERE (firstname LIKE $sql_filter_name
                                 OR lastname LIKE $sql_filter_name
                                 OR email LIKE $sql_filter_name)";
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
        $query = "SELECT id FROM foundationmembers $criteria ORDER BY lastname, firstname";
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
    
   static function main() {
        global $config;

        // Check session for previous instance
        $container = isset($_SESSION[SESSIONID]) ? $_SESSION[SESSIONID] : null;
        if(!$container instanceof ListFoundationMembers || isset($_REQUEST['reload'])) {
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
        $dom->appendChild($pagenode = $dom->createElement("page"));
        $pagenode->setAttribute("title", "List Foundation Members");

        // Security check
        if(!check_permissions($dom, $pagenode, GROUP)) return;

        // Start the page off       
        $listnode = $pagenode->appendChild($dom->createElement("listfoundationmembers"));

        // Check for page change
        if(isset($_REQUEST['page']) && ctype_digit($_REQUEST['page'])) {
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
        if (isset($_POST['renew'])) {
            $foundationmember = FoundationMember::fetchmember($_POST['renew']);
            if(!$foundationmember instanceof FoundationMember) {
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
                    $headers = array(
                        'Reply-To' => '<membership-committee@gnome.org>',
                        'From' => 'GNOME Foundation Membership Committee <membership-committee@gnome.org>',
                        'To' => $membername.' <'.$to.'>',
                        'Cc' => '<'.$cc.'>',
                        'Subject' => $subject
                    );
                    $error = send_mail($recipients, $subject, $headers, $body);
                    
                    if(PEAR::isError($error))
                        return $error;
                    
                    $node = $listnode->appendChild($dom->createElement("emailsent"));
                    $node = $listnode->appendChild($dom->createElement("renewed"));
                    $listnode->setAttribute("name", $foundationmember->firstname.' '.$foundationmember->lastname);
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

        // Display filter settings
        $filternamenode = $listnode->appendChild($dom->createElement("filter_name"));
        $filternamenode->appendChild($dom->createTextNode($this->filter_name));
        //TODO select the right one
        $filteroldnode = $listnode->appendChild($dom->createElement("filter_old"));

        // Display navigation information
        $this->foundationmembers->add_navinfo_to($dom, $listnode);
        
        // Display the initialisation error (to explain a possible lack of results) 
        if(isset($this->error)) {
            $node = $listnode->appendChild($dom->createElement("error"));
            $node->appendChild($dom->createTextNode((PEAR::isError($this->error) ? $this->error->getMessage() : $this->error)));
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
            $foundationmembernode = $listnode->appendChild($dom->createElement("foundationmember"));
            $foundationmembernode->setAttribute("member", ($entry->resigned_on == null));
            $entry->add_to_node($dom, $foundationmembernode);
        }
    }
}

require_once("common.php");

ListFoundationMembers::main();

?>

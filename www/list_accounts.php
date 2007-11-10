#!/usr/bin/php-cgi
<?php

require_once("../lib/page.php");
require_once("../lib/paged_results.php");
require_once("../lib/account.php");

define('STYLESHEET', 'list_accounts.xsl');
define('SESSIONID', 'list_accounts');
define('GROUP', 'accounts');

class ListAccounts { 
    public
        $accounts,
        $filter_status,
        $filter_keyword,
        $error;
    
    function reload() {
        unset($this->error);

        // Create an empty resultset in case of problems
        $results = array();
        $this->accounts = new PagedResults($results);
        
        $results = Account::get_accountsteam_actions($this->filter_keyword, $this->filter_status);
        $this->accounts = new PagedResults($results);
    }
    
    
    function main() {
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
        if(isset($_REQUEST['page']) && ctype_digit($_REQUEST['page'])) {
            $this->accounts->goto_page($_REQUEST['page']);
        }

        // If filter changes specified...                       
        if(isset($_REQUEST['filter_keyword']) && isset($_REQUEST['filter_status'])) {
                $this->filter_keyword = $_REQUEST['filter_keyword'];
                $this->filter_status = $_REQUEST['filter_status'];
                $this->reload();
        }

        // If posting details, attempt to add the new details
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->process($dom, $formnode);
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
        $this->accounts->add_navinfo_to($dom, $listnode);
        
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

    function process(&$dom, &$formnode) {
        if(isset($_POST['reject']) && ctype_digit($_REQUEST['reject'])) {
            $account = new Account($_REQUEST['reject']);
            if (!PEAR::isError($account)) {
                $account->update_status('R');
            }
        }
    }
    
    function add_entries(&$dom, &$listnode, &$entries) {
        // Add entries to page
        foreach ($entries as $uid) {
            $account = new Account($uid);

            $listnode->appendChild($accountnode = $dom->createElement('account'));
            $accountnode->setAttribute('cn', $account->cn);
            $accountnode->setAttribute('uid', $account->uid);
            $accountnode->setAttribute('mail', $account->mail);
            $accountnode->setAttribute('comment', $account->comment);
            $accountnode->setAttribute('db_id', $account->db_id);
            $accountnode->setAttribute('createdon', $account->timestamp);
            $accountnode->setAttribute('status', $account->status);
            $accountnode->appendChild ($groupsnode = $dom->createElement('groups'));
            foreach($account->abilities as $group => $ability) {
                if ($ability['verdict'] == 'A') {
                    $groupnode = $dom->createElement('group');
                    $groupnode->setAttribute('cn', $group);

                    if (!is_null($ability['voucher'])) {
                        $groupnode->setAttribute('approvedby', $ability['voucher']);
                        $groupnode->setAttribute('module', $ability['voucher_group']);
                    }
                    $groupsnode->appendChild($groupnode);
                }
            }
        }
    }
}

require_once("common.php");

ListAccounts::main();

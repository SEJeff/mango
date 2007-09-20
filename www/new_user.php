#!/usr/bin/php-cgi
<?php

require_once("../lib/page.php");
require_once("../lib/user.php");

define('STYLESHEET', 'new_user.xsl');
define('SESSIONID', 'new_user');
define('GROUP', 'accounts');

$AFFECTEDGROUPS = array(
    "gnomecvs",
    "ftpadmin",
    "gnomeweb",
    "bugzilla",
    "artweb",
    "mailusers",
    "accounts",
    "buildmaster",
    "buildslave"
);

class NewUser {
    // Stage

    // Details for the user being created
    var $user;
    
    // Remember existing SSH keys between requests
    var $savedKeys;
    
    // Mail template to use
    var $mailbody;

    function NewUser() {
        $this->user = new User();
        $this->savedKeys = array();
        $this->sendemail = true;
        $this->mailbody = "";
    }

    function init_mailbody() {
        
    }
    
    function main() {
        global $config;

        // Check session for previous instance
        $container = $_SESSION[SESSIONID];
        if(!is_a($container, "NewUser") || isset($_REQUEST['reload'])) {
            $container = new NewUser();
            $_SESSION[SESSIONID] = $container;
        }

        // Set up a page for tracking the response for this request
        $page = new Page(STYLESHEET);

        // Initialise the mail body
        if($container->mailbody == "")
            $container->init_mailbody();
        
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
        $pagenode->setAttribute("title", "New User");

        // Security check
        if(!check_permissions($dom, $pagenode, GROUP)) return;

        // Start the page off       
        $formnode = $pagenode->appendChild($dom->createElement("newuser"));

        // If posting details, attempt to add the new details
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->process($dom, $formnode);
        }
        
        // Add current details to form
        $this->user->add_to_node($dom, $formnode);
        
        // Add SSH keys with indices
        $savedkeysnode = $formnode->appendChild($dom->createElement("savedkeys"));
        foreach($this->user->authorizedKeys as $ref => $key) {
            $keynode = $savedkeysnode->appendChild($dom->createElement("key"));
            $keynode->setAttribute("ref", $ref);
            $keynode->appendChild($dom->createTextNode($key));
        }
        
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

        // Attempt LDAP add
        $result = $this->user->adduser();
        if(PEAR::isError($result)) {
            $node = $formnode->appendChild($dom->createElement("error"));
            $node->appendChild($dom->createTextNode($result->getMessage()));
            return;
        }
        
        // Report success
        if($result) {
            $node = $formnode->appendChild($dom->createElement("added"));
            $this->user->add_to_node($dom, $node);
            $this->user = new User();
        }

        return;
    }
    
    function readform() {
        global $checkforgroups;
        global $AFFECTEDGROUPS;

        // Save any keys from the last form     
        $this->savedKeys = $user->authorizedKeys;
        
        // Read details from form
        $this->user->uid = $_POST['uid'];
        $this->user->cn = $_POST['cn'];
        $this->user->mail = $_POST['mail'];
        $this->user->description = $_POST['description'];
        $this->user->groups = array();
        $this->user->authorizedKeys = array();
        foreach($_POST as $key => $value) {
            if(substr($key, 0, 6) == "group-") {
                $this->user->groups[] = substr($key, 6);
            }
                    // SECURITY: Make sure the FORM submission only contained the groups allowed to be changed
            $this->user->groups = array_intersect($this->user->groups, $AFFECTEDGROUPS);
            if(substr($key, 0, 14) == "authorizedKey-") {
                $i = substr($key, 14);
                if(!empty($this->savedKeys[$i]))
                    $this->user->authorizedKeys[] = $this->savedKeys[$i];
            }
        }
        if($_FILES['keyfile']['tmp_name']) {
            $keyfile = file_get_contents($_FILES['keyfile']['tmp_name']);
        }
        $newkeylist = $keyfile."\n".$_POST['newkeys'];
        $newkeys = split("\n", $newkeylist);
        foreach($newkeys as $key) {
            if(empty($key) || substr($key, 0, 3) != "ssh") continue;
            $this->user->authorizedKeys[] = $key;
        }

        // Save any keys for the next form
        $this->savedKeys = array_unique($this->user->authorizedKeys);
        $this->user->authorizedKeys = $this->savedKeys;

        // Validate details
        $errors = $this->user->validate();

        return $errors;
    }
}

require_once("common.php");

NewUser::main();

?>

#!/usr/bin/php-cgi
<?php

require_once("../lib/page.php");
require_once("../lib/foundationmember.php");
require_once('../lib/util.php');

define('STYLESHEET', 'update_foundationmember.xsl');
define('SESSIONID', 'update_foundationmember');
define('GROUP', 'membctte');

class UpdateFoundationMember {
    // Details for the mirror being updated
    var $foundationmember;

    var $error;
        
    function UpdateFoundationMember($id) {
        $foundationmember = FoundationMember::fetchmember($id);
        if(!is_a($foundationmember, "FoundationMember")) {
            $this->error = $foundationmember;
            return;
        }

        $this->foundationmember = $foundationmember;
    }
        
    function main() {
        // Check session for previous instance
        $container = $_SESSION[SESSIONID];
        if(!is_a($container, "UpdateFoundationMember") || isset($_REQUEST['id'])) {
            $id = $_REQUEST['id'];
            $container = new UpdateFoundationMember($id);
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
        $pagenode->setAttribute("title", "Update Foundation Member '".$this->foundationmember->id."'");

        // Security check
        if(!check_permissions($dom, $pagenode, GROUP)) return;

        // Start the page off       
        $formnode = $pagenode->appendChild($dom->createElement("updatefoundationmember"));

        // If posting details, attempt to add the new details
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->process($dom, $formnode);
        }
        
        // Add current details to form
        $this->foundationmember->add_to_node($dom, $formnode);
        
        // Add initialisation error, if any
        if(PEAR::isError($this->error)) {
            $node = $formnode->appendChild($dom->createElement("error"));
            $node->appendChild($dom->createTextNode($this->error->getMessage()));
        }

        return;
    }

    function process(&$dom, &$formnode) {   
        // Check ref (in case of multiple open pages)
        $idcheck = $_POST['idcheck'];
        if($this->foundationmember->id != $idcheck) {
            $foundationmember = FoundationMember::fetchmember($idcheck);
            if(!is_a($foundationmember, "FoundationMember")) {
                $this->error = $foundationmember;
                return;
            }
            $this->foundationmember = $foundationmember;
        }
        
        // Read form and validate
        $formerrors = $this->readform();
        if(count($formerrors) > 0) {
            foreach($formerrors as $error) {
                $node = $formnode->appendChild($dom->createElement("formerror"));
                $node->setAttribute("type", $error);
            }
            return;
        }

        // Attempt MySQL update
        $result = $this->foundationmember->update();
        if(PEAR::isError($result)) {
            $node = $formnode->appendChild($dom->createElement("error"));
            $node->appendChild($dom->createTextNode($result->getMessage()));
            return;
        }
        
        // Report success
        if(is_array($result)) {
            if ($this->foundationmember->renew) {  
                $membername = $this->foundationmember->firstname.' '. $this->foundationmember->lastname;
                $to =  $this->foundationmember->email;
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
                
                $formnode->appendChild($dom->createElement('emailsent'));
                
            }

            $updatednode = $formnode->appendChild($dom->createElement("updated"));
            foreach($result as $change) {
                $node = $updatednode->appendChild($dom->createElement("change"));
                $node->setAttribute("id", $change);
            }
        }

        return;
    }
    
    function readform() {
        // Read details from form
        $this->foundationmember->firstname = $_POST['firstname'];
        $this->foundationmember->lastname = $_POST['lastname'];
        $this->foundationmember->email = $_POST['email'];
        $this->foundationmember->comments = $_POST['comments'];
        $this->foundationmember->userid = $_POST['userid'];
        if (isset($_POST['renew']) && $_POST['renew'] == "on") {
            $this->foundationmember->renew = true;
            $this->foundationmember->last_renewed_on = time();
        }

        // Validate details
        $errors = $this->foundationmember->validate();

        return $errors;
    }
}

require_once("common.php");

UpdateFoundationMember::main();

?>

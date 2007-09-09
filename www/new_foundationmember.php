#!/usr/bin/php-cgi
<?php

require_once("../lib/page.php");
require_once("../lib/foundationmember.php");

define('STYLESHEET', 'new_foundationmember.xsl');
define('SESSIONID', 'new_foundationmember');
define('GROUP', 'membctte');

class NewFoundationMember {
    // Details for the foundationmember being created
    var $foundationmember;
    
    function NewFoundationMember() {
        $this->foundationmember = new FoundationMember();
    }
        
    function main() {
        global $config;

        // Check session for previous instance
        $container = $_SESSION[SESSIONID];
        if(!is_a($container, "NewFoundationMember") || isset($_REQUEST['reload'])) {
            $container = new NewFoundationMember();
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
        $pagenode->setAttribute("title", "New Foundation Member");

        // Security check
        if(!check_permissions($dom, $pagenode, GROUP)) return;

        // Start the page off       
        $formnode = $pagenode->appendChild($dom->createElement("newfoundationmember"));

        // If posting details, attempt to add the new details
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->process($dom, $formnode);
        }
        
        // Add current details to form
        $this->foundationmember->add_to_node($dom, $formnode);
        
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
        $result = $this->foundationmember->addmember();
        if(PEAR::isError($result)) {
            $node = $formnode->appendChild($dom->createElement("error"));
            $node->appendChild($dom->createTextNode($result->getMessage()));
            return;
        }
        
        // Report success
        if($result) {
            $membername = $this->foundationmember->firstname.' '. $this->foundationmember->lastname;
            $to =  $this->foundationmember->email;
            $cc = 'membership-committee@gnome.org';
            $subject = "GNOME Foundation Membership - Accepted";
            $body = file_get_contents('approval-template.txt');
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
            $params = array(
                'head_charset' => 'UTF-8',
                'head_encoding' => 'quoted-printable',
                'text_charset' => 'UTF-8',
            );
            $content = $mime->get($params);
            $headers = $mime->headers($headers);
            $mail = &Mail::factory('smtp');
            $error = $mail->send($recipients, $headers, $content);
            if(PEAR::isError($error))
                return $error;

            $node = $formnode->appendChild($dom->createElement("added"));
            $this->foundationmember->add_to_node($dom, $node);
            $node = $formnode->appendChild($dom->createElement("emailsent"));
            $this->foundationmember = new FoundationMember();
        }

        return;
    }
    
    function readform() {
        global $checkforgroups;
        
        // Read details from form
        $this->foundationmember->firstname = $_POST['firstname'];
        $this->foundationmember->lastname = $_POST['lastname'];
        $this->foundationmember->email = $_POST['email'];
        $this->foundationmember->comments = $_POST['comments'];
        $this->foundationmember->last_renewed_on = time();
        $this->foundationmember->need_to_renew = false;
    
        // Validate details
        $errors = $this->foundationmember->validate();

        return $errors;
    }
}

require_once("common.php");

NewFoundationMember::main();

?>

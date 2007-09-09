#!/usr/bin/php-cgi
<?php

require_once ("../lib/page.php");
require_once ("../lib/account.php");
require_once ("common.php");

$page = new Page("verify_mail.xsl");
//$rootnode = $page->result->add_root("page");
$rootnode = $page->result->createElement('page');
$page->result->appendChild($rootnode);
$rootnode->setAttribute("title", "Mail verification");
$element = $page->result->createElement('verify_mail');
$rootnode->appendChild($element);
$account = new Account();
$verified = $account->verify_email_token();

if (!PEAR::isError($verified)) { 
	$node = $rootnode->appendChild ($node = $page->result->createElement("verified"));
	$node->appendChild($node = $page->result->createElement("email"));
	$node->appendChild($page->result->createTextNode($_REQUEST['email']));
} else {
	if ($verified->message == "Already verified") { 
		$node = $rootnode->appendChild ($node = $page->result->createElement("alreadyverified"));
	}
}

$page->send();

?>
	
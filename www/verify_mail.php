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

$error = null;
$account = new Account($_REQUEST['uid'], 'uid');
if (!PEAR::isError($account)) {
    $val = $account->validate_mail_token($_REQUEST['token']);
    if (!PEAR::isError($val)) {
        $account->approve_mail_token();
    } else {
        $error = $val;
    }
} else {
    $error = $account;
}

if (!PEAR::isError($error)) { 
	$node = $rootnode->appendChild ($node = $page->result->createElement("verified"));
	$node->appendChild($node = $page->result->createElement("mail"));
	$node->appendChild($page->result->createTextNode($account->mail));
} else {
	if ($error->message == "Already verified") { 
		$node = $rootnode->appendChild ($node = $page->result->createElement("alreadyverified"));
	}
}

$page->send();

?>
	

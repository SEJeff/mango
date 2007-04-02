#!/usr/bin/php
<?php

require("../lib/page.php");
require("common.php");

$page = new Page("index.xsl");
//$rootnode = $page->result->add_root("page");
$rootnode = $page->result->createElement('page');
$page->result->appendChild($rootnode);
$rootnode->setAttribute("title", "Login page");
$element = $page->result->createElement('homepage');
$rootnode->appendChild($element);
$page->send();

?>

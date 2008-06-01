<?php

/* Stuff to do every hit */
require_once("../lib/user.php");

/* Check permissions */
function check_permissions(&$dom, &$pagenode, $group) {
	$user = $_SESSION['user'];
	if(!$user || !$user instanceof User) {
		$pagenode->appendChild($dom->createElement("notloggedin"));
                $pagenode->setAttribute("title", "Login required");
		return false;
	}
	$groups = $_SESSION['groups'];
	if(isset($groups) && is_array($groups) && in_array($group, $groups)) {
		return true;
	}
	
	$node = $pagenode->appendChild($dom->createElement("notauthorised"));
	$node->setAttribute("group", $group);
        $pagenode->setAttribute("title", "Not authorised");
	return false;
}

/* Start the session */
ini_set('session.bug_compat_42', false);
ini_set('session.bug_compat_warn', true);
ini_set('session.hash_function', 1);
ini_set('session.hash_bits_per_character', 6);
session_cache_limiter('nocache');

session_start();

Page::validate_post();

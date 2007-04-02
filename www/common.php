<?php

/* Stuff to do every hit */
require_once("../lib/user.php");

/* Check permissions */
function check_permissions(&$dom, &$pagenode, $group) {
	$user = $_SESSION['user'];
	if(!$user || !is_a($user, "User")) {
		$pagenode->append_child($dom->create_element("notloggedin"));
		return false;
	}
	
	$groups = $_SESSION['groups'];
	if(isset($groups) && is_array($groups) && in_array($group, $groups)) {
		return true;
	}
	
	$node = $pagenode->append_child($dom->create_element("notauthorised"));
	$node->set_attribute("group", $group);
	return false;
}

/* Start the session */
session_start();

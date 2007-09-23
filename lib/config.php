<?php

//error_reporting(E_ALL);

require_once("PEAR.php");

/*
 * Site-wide configuration object
 */
class SiteConfig {
	// Date config last read from disk
	var $cached_date;

	// Runtime mode (Live/Preview/Development)
	var $mode;

	// Base URL
	var $base_url;

	// Mirrors MySQL database URL
	var $accounts_db_url;
	
	// Mirrors MySQL database URL
	var $mirrors_db_url;

	// Foundation membership MySQL database URL
	var $membership_db_url;

	// SMTP URL
	var $smtp_url;

	// LDAP URL
	var $ldap_url;

	// LDAP bind DN
	var $ldap_binddn;

	// LDAP bind PW
	var $ldap_bindpw;

	// LDAP base DN
	var $ldap_basedn;

	// LDAP users base DN
	var $ldap_users_basedn;

	// LDAP groups base DN
	var $ldap_groups_basedn;

	// LDAP modules base DN
	var $ldap_modules_basedn;
	
	// LDAP aliases base DN
	var $ldap_aliases_basedn;

	// Salt to be used in e-mail tokens
	var $token_salt;
	
	// Support e-mail
	var $support_email;

	// Session save path
	var $session_path;

	/*
	 * Constructor. Modify configuration stuff here.
	 */
	function SiteConfig() {
		$this->cached_date = time();
	}

	/*
	 * Read from the configuration file
	 */
	function read() {
		// Identify location of config file
		$basedir = dirname($_SERVER['DOCUMENT_ROOT']);
		$configfile = $basedir."/config.xml";
		if(is_readable("/var/www/mango/config.xml"))
			$configfile = "/var/www/mango/config.xml";
		if(is_readable("/etc/mango/config.xml"))
			$configfile = "/etc/mango/config.xml";

		// Check file exists
		if(!is_readable($configfile)) {
			return PEAR::raiseError("Could not find configuration file at '".$configfile."'.");
		}

		// Parse into DOM into member variables
		$dom = new DOMDocument();
		if(!$dom->load($configfile)) {
			return PEAR::raiseError("Trouble parsing config. Please check it's valid XML (e.g. 'xmllint config.xml').");
		}

		// Work through the elements
		$root_node = $dom->firstChild;
		$nodes = $root_node->childNodes;
		foreach($nodes as $node)
			$this->read_from($node);

                return true;
	}

	/*
	 * Read configuration from given node
	 */
	function read_from($node) {
		$children = $node->childNodes;
		
		// Running mode
		if($node->nodeType == XML_ELEMENT_NODE && $node->tagName == "mode")
			$this->mode = $children->item(0)->textContent;

		// Base URL
		if($node->nodeType == XML_ELEMENT_NODE && $node->tagName == "base_url")
			$this->base_url = $children->item(0)->textContent;

		// Accounts database URL
		if($node->nodeType == XML_ELEMENT_NODE && $node->tagName == "accounts_db_url")
			$this->accounts_db_url = $children->item(0)->textContent;
			
		// Mirors database URL
		if($node->nodeType == XML_ELEMENT_NODE && $node->tagName == "mirrors_db_url")
			$this->mirrors_db_url = $children->item(0)->textContent;

		// Membership database URL
		if($node->nodeType == XML_ELEMENT_NODE && $node->tagName == "membership_db_url")
			$this->membership_db_url= $children->item(0)->textContent;

		// SMTP e-mail server information
		if($node->nodeType == XML_ELEMENT_NODE && $node->tagName == "smtp_url")
			$this->smtp_url = $children->item(0)->textContent;

		// LDAP server URL
		if($node->nodeType == XML_ELEMENT_NODE && $node->tagName == "ldap_url")
			$this->ldap_url = $children->item(0)->textContent;

		// LDAP bind DN
		if($node->nodeType == XML_ELEMENT_NODE && $node->tagName == "ldap_binddn")
			$this->ldap_binddn = $children->item(0)->textContent;

		// LDAP bind password
		if($node->nodeType == XML_ELEMENT_NODE && $node->tagName == "ldap_bindpw")
			$this->ldap_bindpw = $children->item(0)->textContent;

		// LDAP base DN
		if($node->nodeType == XML_ELEMENT_NODE && $node->tagName == "ldap_basedn")
			$this->ldap_basedn = $children->item(0)->textContent;

		// LDAP users base DN
		if($node->nodeType == XML_ELEMENT_NODE && $node->tagName == "ldap_users_basedn")
			$this->ldap_users_basedn = $children->item(0)->textContent;

		// LDAP groups base DN
		if($node->nodeType == XML_ELEMENT_NODE && $node->tagName == "ldap_groups_basedn")
			$this->ldap_groups_basedn = $children->item(0)->textContent;

		// LDAP modules base DN
		if($node->nodeType == XML_ELEMENT_NODE && $node->tagName == "ldap_modules_basedn")
			$this->ldap_modules_basedn = $children->item(0)->textContent;

		// LDAP aliases base DN
		if($node->nodeType == XML_ELEMENT_NODE && $node->tagName == "ldap_aliases_basedn")
			$this->ldap_aliases_basedn = $children->item(0)->textContent;
				
		// Token salt
		if($node->nodeType == XML_ELEMENT_NODE && $node->tagName == "token_salt")
			$this->token_salt = $children->item(0)->textContent;
				
		// Support e-mail
		if($node->nodeType == XML_ELEMENT_NODE && $node->tagName == "support_email")
			$this->support_email = $children->item(0)->textContent;

		// Session save path
		if($node->nodeType == XML_ELEMENT_NODE && $node->tagName == "session_path")
			$this->session_path = $children->item(0)->textContent;
	}
}

// Set global config variable
$config = new SiteConfig();
$result = $config->read();
if(PEAR::isError($result)) {
	exit("Error: ".$result->getMessage());
}

// Set session save path
session_save_path($config->session_path);

?>

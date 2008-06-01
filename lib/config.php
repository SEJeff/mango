<?php

//error_reporting(E_ALL);

require_once("PEAR.php");

/*
 * Site-wide configuration object
 */
class SiteConfig {
    public
        $cached_date,       // Date config last read from disk
        $mode,              // Runtime mode (Live/Preview/Development)
        $base_url,          // Base URL

        $accounts_db_url,   // Mirrors MySQL database URL
        $mirrors_db_url,    // Mirrors MySQL database URL
        $membership_db_url, // Foundation membership MySQL database URL

        $mail_backend,       // Mail backend
        $mail_sendmail_path, // Path to sendmail (sendmail backend)
        $mail_sendmail_args, // Additional options for sendmail (sendmail backend)
        $mail_smtp_host,     // SMTP server hostname (smtp backend)
        $mail_smtp_port,     // SMTP server port (smtp backend)
        $mail_smtp_auth,     // Whether or not to use smtp authentication (smtp backend)
        $mail_smtp_username, // Username to use for SMTP authentication (smtp backend)
        $mail_smtp_password, // Password to use for SMTP authentication (smtp backend)
        $mail_smtp_localhost, // Value to give when sending EHLO or HELO (smtp backend)
        $mail_smtp_timeout,  // SMTP connection timeout
        $mail_smtp_persist,  // Whether or not to use persistent SMTP connections (smtp backend)

        $ldap_url,            // LDAP URL
        $ldap_binddn,         // LDAP bind DN
        $ldap_bindpw,         // LDAP bind PW
        $ldap_basedn,         // LDAP base DN
        $ldap_users_basedn,   // LDAP users base DN
        $ldap_groups_basedn,  // LDAP groups base DN
        $ldap_modules_basedn, // LDAP modules base DN
        $ldap_aliases_basedn, // LDAP aliases base DN

        $token_salt,          // Salt to be used in e-mail tokens

        $support_email,       // Support e-mail
        $account_email,       // Email address of person(s) who handles account management -->

        $session_path;        // Session save path

    /*
     * Constructor. Modify configuration stuff here.
     */
    function __construct() {
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

        if ($node->nodeType == XML_ELEMENT_NODE) {
            $tagname = $node->tagName;

            if (property_exists($this, $tagname))
                $this->$tagname = $children->item(0)->textContent;
        }

        foreach (array('mail_smtp_auth', 'mail_smtp_persist') as $tagname)
            $this->$tagname = (boolean) $this->$tagname;

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

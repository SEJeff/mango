<?php

require_once("PEAR.php");

class LDAPUtil {
	function connectToLDAP() {
		global $config;

		/* Extract the hostname */
		$url_parts = parse_url($config->ldap_url);
		$hostname = $url_parts['host'];
	
			
		/* Connect to the LDAP server */
		$ldap = ldap_connect($hostname);
		if(!$ldap) {
			return PEAR::raiseError("Unable to connect to LDAP server");
		}
		
		/* Set protocol v3 */
		if(!ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3)) {
			return PEAR::raiseError("Could not switch LDAP connection to protocol v3");
		}

		/* Bind to the LDAP server */
		$bind_result = ldap_bind($ldap, $config->ldap_binddn, $config->ldap_bindpw);
		if(!$bind_result) {
			ldap_close($ldap);
			return false;
		}

		/* Return connection handle */
		return $ldap;
	}
}

?>

<?php

require_once("PEAR.php");

class MySQLUtil {
	function connectToMySQL($db_url) {
		/* Extract the hostname */
		$url_parts = parse_url($db_url);
		$hostname = $url_parts['host'];
		$scheme = $url_parts['scheme'];
		$login = $url_parts['user'];
		$passwd = $url_parts['pass'];
		$dbname = substr($url_parts['path'], 1);

		/* Connect to the LDAP server */
		$dbh = mysql_connect($hostname, $login, $passwd);
		if(!$dbh) {
			return PEAR::raiseError("Unable to connect to mySQL server: ".mysql_error());
		}

		/* Select the appropriate database */
		$result = mysql_select_db($dbname, $dbh);
		if(!$result) {
			return PEAR::raiseError("Could not select '$dbname': ".mysql_error($dbh));
		}
		
		/* Return connection handle */
		return $dbh;
	}
	
	function escape_string($string) {
		return '"'.addslashes($string).'"';
	}

	function escape_date($date) {
		if($date > 0)
			return '"'.strftime("%Y-%m-%d", $date).'"';
		else
			return "NULL";
	}

	function escape_datetime($date) {
		if($date > 0)
			return '"'.strftime("%Y-%m-%d %H:%M", $date).'"';
		else
			return "NULL";
	}

	function escape_boolean($boolean) {
		return '"'.($boolean ? 'Y' : 'N').'"';
	}
}

?>

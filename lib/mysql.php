<?php

require_once("PEAR.php");
require_once("config.php");

class MySQLUtil {
	
	var $link;
	
	function MySQLUtil($db_url = '') { 
		$this->link = $this->connectToMySQL($db_url);
	}
	
	function connectToMySQL($db_url = '') {
		global $config;
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
			die ("Unable to connect to mySQL server: ".mysql_error());
		}

		/* Select the appropriate database */
		$result = mysql_select_db($dbname, $dbh);
		if(!$result) {
			die ("Could not select '$dbname': ".mysql_error($dbh));
		}
		
		/* Return connection handle */
		return $dbh;
	}
	
	function escape_string($string) {
		return "'" . addslashes($string) . "'";
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
	
	function escape_enum($enum, $values) { 
		return '"'.(in_array($enum, $values) ? $enum : '').'"';
	}
	
	function query ($query) {
	    global $config;
	    
	    if ($config->debug == 'enabled') { 
	        echo "MySQL Query: ".$query."\n";
	    }
		$result = mysql_query ($query, $this->link);
		if (!$result) {
			die ("Unable to run query: ".mysql_error ($this->link));
		} else { 
			return $result;
		}
	}
}

?>

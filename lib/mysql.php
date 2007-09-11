<?php

require_once("PEAR.php");
require_once("config.php");

class MySQLUtil {
    // Hold an instance of the class
    private static $instance = array();
    public $handle = null;
   
    // A private constructor; prevents direct creation of object
    private function __construct($db_url)
    {
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

        $this->handle = $dbh;
        
        return $this;
    }

    /* not needed as db connections are closed automatically
    function __destruct() {
        if (!is_null($this->handle)) {
            mysql_close($this->handle);
            $this->handle = null;
        }
    } */

    // The singleton method
    public static function singleton($db_url)
    {
        if (!isset(self::$instance[$db_url])) {
            $c = __CLASS__;
            self::$instance[$db_url] = new $c($db_url);
        }

        return self::$instance[$db_url];
    }
   
    // Prevent users to clone the instance
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

//    function MySQLUtil($db_url = '') { 
//        $this->handle = $this->connectToMySQL($db_url);
//    }
    /*
    function connectToMySQL($db_url = '') {

    } */
    
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
        $result = mysql_query ($query, $this->handle);
        if (!$result) {
            die ("Unable to run query: ".mysql_error ($this->handle));
        } else { 
            return $result;
        }
    }
    
    public function dbh() {
        return $this->handle;
    }
}

?>

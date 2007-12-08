<?php

require_once("PEAR.php");

class LDAPUtil {
    // Hold an instance of the class
    private static $instance;
    private $handle = null;
   
    // A private constructor; prevents direct creation of object
    private function __construct()
    {
         global $config;

        /* Connect to the LDAP server */
        $ldap = ldap_connect($config->ldap_url);
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

        $this->handle = $ldap;

        /* Return connection handle */
        return $ldap;
    }

    function __destruct() {
        if (!is_null($this->handle)) {
            ldap_close($this->handle);
            $this->handle = null;
        }
    }

    // The singleton method
    public static function singleton()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance->handle;
    }
   
    // Prevent users to clone the instance
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    function ldap_quote($str) {
        return str_replace(
            array( '\\', ' ', '*', '(', ')' ),
            array( '\\5c', '\\20', '\\2a', '\\28', '\\29' ),
            $str
        );
    }
}

?>

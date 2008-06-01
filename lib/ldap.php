<?php

require_once("PEAR.php");

class LDAPUtil {
    // Hold an instance of the class
    private static $instance;
    private $handle = null;
    private $error = '';
   
    // A private constructor; prevents direct creation of object
    private function __construct()
    {
         global $config;

        /* Connect to the LDAP server */
        $ldap = ldap_connect($config->ldap_url);
        if(!$ldap) {
            $this->error = PEAR::raiseError("Unable to connect to LDAP server");
            return false;
        }
        
        /* Set protocol v3 */
        if(!ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3)) {
            $this->error = PEAR::raiseError("Could not switch LDAP connection to protocol v3");
            return false;
        }

        /* Bind to the LDAP server */
        $bind_result = @ldap_bind($ldap, $config->ldap_binddn, $config->ldap_bindpw);
        if(!$bind_result) {
            $bind_error = ldap_error($ldap);
            ldap_close($ldap);
            $this->error = PEAR::raiseError('Unable to bind to LDAP server: '.$bind_error.'.');
            return false;
        }

        /* Everything went well,
         * assign LDAP ressource handle */
        $this->handle = $ldap;

        /* Return connection handle */
        return true;
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
        
        /* If no error was raised, return LDAP ressource handle.
         * If an error was raised, return the PEAR error object instead. */
        if (!PEAR::isError(self::$instance->error)) {
            return self::$instance->handle;
        } else {
            return self::$instance->error;
        }
    }
   
    // Prevent users to clone the instance
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    static function ldap_quote($str) {
        return str_replace(
            array( '\\', ' ', '*', '(', ')' ),
            array( '\\5c', '\\20', '\\2a', '\\28', '\\29' ),
            $str
        );
    }
}

?>

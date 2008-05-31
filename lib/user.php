<?php

require_once("ldap.php");
require_once("util.php");

class User {
    // Main attributes
    var $uid;
    var $cn;
    var $mail;
    var $description;
    var $authorizedKeys;
        
    // Details of the groups the user is in
    var $groups;
    var $uid_from_ldap;

    // Has 'pubkeyauthenticationuser' objectclass set?
    var $pubkeyauthenticationuser;
    
    function User() {
        $this->authorizedKeys = array();
        $this->groups = array();
        $this->uid_from_ldap = false;
        $this->pubkeyauthenticationuser = false;
    }

    function absorb($entry) {
        $user = new User();
        $user->uid = $entry['uid'][0];
        $user->cn = $entry['cn'][0];
        $user->mail = $entry['mail'][0];
        $user->description = $entry['description'][0];
        if(count($entry['authorizedkey']) > 0) {
            for($i = 0; $i < $entry['authorizedkey']['count']; $i++) {
                $user->authorizedKeys[] = $entry['authorizedkey'][$i];
            }
        }
        $user->pubkeyauthenticationuser = false;
        if(count($entry['objectclass']) > 0) {
            for($i = 0; $i < $entry['objectclass']['count']; $i++) {
                $objectclass = $entry['objectclass'][$i];
                if($objectclass == "pubkeyAuthenticationUser")
                    $user->pubkeyauthenticationuser = true;
            }
        }
        return $user;
    }
    
    function listusers(&$results) {
        global $config;
        
        // Process list of UIDs into an LDAP search criteria
        $ldapcriteria = "";
        foreach($results as $key => $result) {
            $ldapcriteria .= "(uid=".LDAPUtil::ldap_quote($result).")";
        }
        if($ldapcriteria) {
            $ldapcriteria = "(&(objectClass=posixAccount)(|".$ldapcriteria."))";
        } else {
            $ldapcriteria = "(objectClass=posixAccount)";
        }

        // Connect to LDAP server
        $ldap = LDAPUtil::singleton();
        if(PEAR::isError($ldap)) return $ldap;
        if(!$ldap) {
            return PEAR::raiseError("LDAP authentication failed");
        }
        
        $result = ldap_search($ldap, $config->ldap_users_basedn, $ldapcriteria, array('uid', 'cn', 'mail'));
        if(!$result) {
            return PEAR::raiseError("LDAP search failed: ".ldap_error($ldap));
        }
        $entries = ldap_get_entries($ldap, $result);
        
        return $entries;
    }           
    
    function adduser() {
        global $config;
        
        // Connect to LDAP server
        $ldap = LDAPUtil::singleton();
        if(PEAR::isError($ldap)) return $ldap;
        if(!$ldap) {
            return PEAR::raiseError("LDAP authentication failed");
        }
        
        // Identify next UID/GID number
        $uidNumber = $this->_next_uidnumber($ldap);
        if(PEAR::isError($uidNumber)) return $uidNumber;
        if($uidNumber < 1000 || $uidNumber > 10000) {
            return PEAR::raiseError("Dodgy UID number ($uidNumber) found!");
        }
        
        // Add user entry
        $dn = "uid=".$this->uid.",".$config->ldap_users_basedn;
        $entry = array();
        $entry['objectclass'][] = "posixAccount";
        $entry['objectclass'][] = "inetOrgPerson";
        $entry['uid'][] = $this->uid;
        $entry['uidNumber'][] = $uidNumber;
        $entry['gidNumber'][] = $uidNumber;
        $entry['sn'][] = $this->cn;
        $entry['cn'][] = $this->cn;
        $entry['mail'][] = $this->mail;
        if(!empty($this->description))
            $entry['description'][] = $this->description;
        if(count($this->authorizedKeys) > 0) {
            $entry['objectclass'][] = "pubkeyAuthenticationUser";
            foreach($this->authorizedKeys as $key) {
                $entry['authorizedKey'][] = $key;
            }
        }
        $entry['loginShell'][] = $this->which_shell();
        $entry['homeDirectory'][] = $this->which_homedir();
        $result = ldap_add($ldap, $dn, $entry);
        if(!$result) {
            $pe = PEAR::raiseError("LDAP (user) add failed: ".ldap_error($ldap));
            return $pe;
        }

        // Add group entry
        $dn = "cn=".$this->uid.",".$config->ldap_groups_basedn;
        $entry = array();
        $entry['objectclass'][] = "posixGroup";
        $entry['cn'][] = $this->uid;
        $entry['gidNumber'][] = $uidNumber;
        $result = ldap_add($ldap, $dn, $entry);
        if(!$result) {
            $pe = PEAR::raiseError("LDAP (group) add failed: ".ldap_error($ldap));
            return $pe;
        }
        
        // Add to groups
        foreach($this->groups as $group) {
            $dn = "cn=".$group.",".$config->ldap_groups_basedn;
            $entry = array();
            $entry['memberUid'][] = $this->uid;
            $result = ldap_mod_add($ldap, $dn, $entry);
            if(!$result) {
                $pe = PEAR::raiseError("LDAP (groupmember) add failed: ".ldap_error($ldap));
                return $pe;
            }
        }
        
        // Tidy up      
        return true;
    }

    // by default this searches for 'uid', but can handle other things as well
    // NOTE: will always pick the first user returned!
    function fetchuser($search_for, $attribute = "uid") {
        global $config;
        
        // Connect to LDAP server
        $ldap = LDAPUtil::singleton();
        if(PEAR::isError($ldap)) return $ldap;
        if(!$ldap) {
            return PEAR::raiseError("LDAP authentication failed");
        }

        // Gather user attributes
        $ldapcriteria = "(&(objectClass=posixAccount)($attribute=".LDAPUtil::ldap_quote($search_for)."))";
        $result = ldap_search($ldap, $config->ldap_users_basedn, $ldapcriteria);
        if(!$result) {
            $pe = PEAR::raiseError("LDAP search failed: ".ldap_error($ldap));
            return $pe;
        }
        $entries = ldap_get_entries($ldap, $result);
        if ($entries['count'] == 0)
            return PEAR::raiseError('No such user');

        $user = User::absorb($entries[0]);
        
        // Gather groups
        $ldapcriteria = "(&(objectClass=posixGroup)(memberUid=".LDAPUtil::ldap_quote($user->uid)."))";
        $result = ldap_search($ldap, $config->ldap_groups_basedn, $ldapcriteria, array('cn'));
        if(!$result) {
            $pe = PEAR::raiseError("LDAP search failed: ".ldap_error($ldap));
            return $pe;
        }
        $entries = ldap_get_entries($ldap, $result);
        $groups = array();
        for($i = 0; $i < $entries['count']; $i++) {
            $groups[] = $entries[$i]['cn'][0];
        }
        $user->groups = $groups;

        $user->uid_from_ldap = true;
        
        // Tidy up      

        return $user;
    }
    
    function update() {
        global $config;
        
        // Connect to LDAP server
        $ldap = LDAPUtil::singleton();
        if(PEAR::isError($ldap)) return $ldap;
        if(!$ldap) {
            return PEAR::raiseError("LDAP authentication failed");
        }
        
        // Pull up existing record for comparison
        $olduser = User::fetchuser($this->uid);
        if(PEAR::isError($olduser)) return $olduser;
        if(!is_a($olduser, "User")) {
            return PEAR::raiseError("No user (".$this->uid.") found!");
        }
        
        // What's changed in the user attributes?
        $dn = "uid=".$this->uid.",".$config->ldap_users_basedn;
        $changes = array();
        $userchanges = array();
        if($olduser->cn != $this->cn) {
            $userchanges['cn'][] = $this->cn;
            $userchanges['sn'][] = $this->cn;
            $changes[] = array('id'=>"cn");
        }
        if($olduser->mail != $this->mail) {
            $userchanges['mail'][] = $this->mail;
            $changes[] = array('id'=>"mail");
        }
        if($olduser->description != $this->description) {
            $userchanges['description'][] = $this->description;
            $changes[] = array('id'=>"description");
        }
        // Dropping out of 'gnomecvs'?
        if(in_array("gnomecvs", $olduser->groups) && !in_array("gnomecvs", $this->groups)) {
            $userchanges['loginShell'][] = $this->which_shell();
            $userchanges['homeDirectory'][] = $this->which_homedir();
        }
        // Joining 'ftpadmin'?
        if(!in_array("gnomecvs", $olduser->groups) && in_array("gnomecvs", $this->groups)) {
            $userchanges['loginShell'][] = $this->which_shell();
            $userchanges['homeDirectory'][] = $this->which_homedir();
        }
        // Dropping out of 'ftpadmin'?
        if(in_array("ftpadmin", $olduser->groups) && !in_array("ftpadmin", $this->groups)) {
            $userchanges['loginShell'][] = $this->which_shell();
            $userchanges['homeDirectory'][] = $this->which_homedir();
            $changes[] = array('id'=>"shellaccessrevoked");
        }
        // Joining 'ftpadmin'?
        if(!in_array("ftpadmin", $olduser->groups) && in_array("ftpadmin", $this->groups)) {
            $userchanges['loginShell'][] = $this->which_shell();
            $userchanges['homeDirectory'][] = $this->which_homedir();
            $changes[] = array('id'=>"shellaccessgranted");
        }
        if(count($userchanges) > 0) {
            $result = ldap_modify($ldap, $dn, $userchanges);
            if(!$result) {
                $pe = PEAR::raiseError("LDAP (user) modify failed: ".ldap_error($ldap));
                return $pe;
            }
        }

        // What's changed with the SSH keys?
        $removedkeys = array_diff($olduser->authorizedKeys, $this->authorizedKeys);
        if(is_array($removedkeys) && count($removedkeys) > 0) {
            $keychanges = array();
            foreach($removedkeys as $key) {
                $keychanges['authorizedKey'][] = $key;
            }
            if ($this->pubkeyauthenticationuser
                && count($this->authorizedKeys) == 0)
            {
                $keychanges['objectclass'][] = "pubkeyAuthenticationUser";
                $changes[] = array('id'=>"pubkeyauthdisabled");
            }

            $result = ldap_mod_del($ldap, $dn, $keychanges);
            if(!$result) {
                $pe = PEAR::raiseError("LDAP (user keys) delete failed: ".ldap_error($ldap));
                return $pe;
            }
            $changes[] = array('id'=>"keysremoved");
        }
        $newkeys = array_diff($this->authorizedKeys, $olduser->authorizedKeys);
        if(is_array($newkeys) && count($newkeys) > 0) {
            $keychanges = array();
            foreach($newkeys as $key) {
                $keychanges['authorizedKey'][] = $key;

                $fingerprint = is_valid_ssh_pub_key($key, False, True);
                if ($fingerprint !== false) {
                    $changes[] = array('id'=>'key-add', "key"=>$key, "fingerprint"=>$fingerprint);
                } else {
                    $changes[] = array('id'=>'key-add', "key"=>$key);
                }
            }
            if (!$olduser->pubkeyauthenticationuser) {
                $keychanges['objectclass'][] = "pubkeyAuthenticationUser";
                $changes[] = array('id'=>"pubkeyauthenabled");
            }
            $result = ldap_mod_add($ldap, $dn, $keychanges);
            if(!$result) {
                $pe = PEAR::raiseError("LDAP (user keys) add failed: ".ldap_error($ldap));
                return $pe;
            }
            $changes[] = array('id'=>"keysadded");
        }

        // What groups are we dropping out of?
        $removedgroups = array_diff($olduser->groups, $this->groups);
        if(is_array($removedgroups) && count($removedgroups) > 0) {
            foreach($removedgroups as $group) {
                $dn = "cn=".$group.",".$config->ldap_groups_basedn;
                $groupchanges = array();
                $groupchanges['memberUid'][] = $this->uid;
                $result = ldap_mod_del($ldap, $dn, $groupchanges);
                if(!$result) {
                    $pe = PEAR::raiseError("LDAP (group '$group') delete failed: ".ldap_error($ldap));
                    return $pe;
                }
                $changes[] = array('id'=>"left-group", 'cn'=>$group);
            }
        }
        $newgroups = array_diff($this->groups, $olduser->groups);
        if(is_array($newgroups) && count($newgroups) > 0) {
            foreach($newgroups as $group) {
                $dn = "cn=".$group.",".$config->ldap_groups_basedn;
                $groupchanges = array();
                $groupchanges['memberUid'][] = $this->uid;
                $result = ldap_mod_add($ldap, $dn, $groupchanges);
                if(!$result) {
                    $pe = PEAR::raiseError("LDAP (group '$group') add failed: ".ldap_error($ldap));
                    return $pe;
                }
                $changes[] = array('id'=>"joined-group", 'cn'=>$group);
            }
        }
        
        // Tidy up      

        return $changes;
    }

    function inform_user(&$changes) {
        global $config;

        if (count($changes) == 0) return false;

        // Prepare mail body template variables
        $maildom = new DOMDocument('1.0','UTF-8');
        $mailnode = $maildom->appendChild($maildom->createElement('user_instructions'));
        $usernode = $mailnode->appendChild($maildom->createElement("user"));
        $this->add_to_node($maildom, $usernode);

        $is_new_account = false;

        // Report successes
        foreach($changes as $change) {
            $node = $mailnode->appendChild($maildom->createElement("change"));
            foreach ($change as $key=>$val) {
                $node->setAttribute($key, $val);
            }

            if ($change['id'] == 'newuser') $is_new_account = true;
        }

        // Process the mail body template
        $stylesheet = new DOMDocument('1.0','UTF-8');
        $stylesheet->loadXML(file_get_contents("../templates/user_instructions.xsl"));
        $xsltprocessor = new XSLTProcessor();
        $xsltprocessor->importStylesheet($stylesheet);
        $body = $xsltprocessor->transformToXML($maildom);
        
        if (empty($body))
            return false;

        $changes[] = array('id'=>'informed-user');

        $subject = $is_new_account ?
               'Your new GNOME account' :
               'Changes to your GNOME account';

        $mime = new Mail_Mime();
        $mime->setTXTBody($body);
        $headers = array(
            "Reply-To" => "Mango <accounts@gnome.org>",
            "From" => "Mango <accounts@gnome.org>",
            "To" => $this->mail,
            "Subject" => $subject,
        );
        $params = array(
            'head_charset' => 'UTF-8',
            'head_encoding' => 'quoted-printable',
            'text_charset' => 'UTF-8',
        );
        $content = $mime->get($params);
        $headers = $mime->headers($headers);
        $mail = &Mail::factory('smtp');
        $recipient = ($config->mode == 'live') ? $this->mail : $config->support_email;
        $error = $mail->send($recipient, $headers, $content);

        return $error;
    }

    function _next_uidnumber(&$ldap) {
        global $config;
        
        // Just as dodgy as Jonathan's method ;)
        $result = ldap_search($ldap, $config->ldap_users_basedn, "(objectClass=posixAccount)", array("uidNumber", "gidNumber"));
        if(!$result) {
            $pe = PEAR::raiseError("LDAP search failed: ".ldap_error($ldap));
            return $pe;
        }
        $entries = ldap_get_entries($ldap, $result);
        
        // Process entries
        $watermark = 1000;
        for($i = 0; $i < $entries['count']; $i++) {
            $uidnumber = $entries[$i]['uidnumber'][0];
            $gidnumber = $entries[$i]['gidnumber'][0];
            if($uidnumber > $watermark)
                $newwatermark = $uidnumber;
            if($gidnumber > $watermark)
                $newwatermark = $gidnumber;
            // Ignore really high uidNumbers (such as nobody)
            if($newwatermark > $watermark && $newwatermark < 10000)
                $watermark = $newwatermark;
        }
        
        return $watermark + 1;
    }

    function _has_shell() {
        if(in_array("ftpadmin", $this->groups))
            return true;
        if(in_array("gnomecvs", $this->groups))
            return true;
        if(in_array("gnomeweb", $this->groups))
            return true;
        if(in_array("bugzilla", $this->groups))
            return true;

        return false;
    }

    function which_shell() {
        if (!$this->_has_shell())
            return "/sbin/nologin";

        # TODO:
        #  should reuse existing shell, if any
        return "/bin/bash";
    }

    function which_homedir() {
        if (!$this->_has_shell())
            return "/";

        return "/home/users/".$this->uid;
    }

    function add_to_node(&$dom, &$formnode) {
        $node = $formnode->appendChild($dom->createElement("uid"));
        $node->appendChild($dom->createTextNode($this->uid));
        $node = $formnode->appendChild($dom->createElement("cn"));
        $node->appendChild($dom->createTextNode($this->cn));
        $node = $formnode->appendChild($dom->createElement("mail"));
        $node->appendChild($dom->createTextNode($this->mail));
        $node = $formnode->appendChild($dom->createElement("description"));
        $node->appendChild($dom->createTextNode($this->description));
        foreach($this->authorizedKeys as $authorizedKey) {
            $node = $formnode->appendChild($dom->createElement("authorizedKey"));
            $fingerprint = is_valid_ssh_pub_key($authorizedKey, False, True);
            if ($fingerprint !== false) {
                $node->setAttribute("fingerprint", $fingerprint);
            }
            $node->appendChild($dom->createTextNode($authorizedKey));
        }
        foreach($this->groups as $group) {
            $node = $formnode->appendChild($dom->createElement("group"));
            $node->setAttribute("cn", $group);
        }
    }
    
    function validate() {
        $errors = array();
        if(empty($this->uid) || (!$this->uid_from_ldap
                                 && !preg_match("/^[a-z]{1,12}$/", $this->uid))) {
            $errors[] = "uid";
        }
        if(empty($this->cn))
            $errors[] = "cn";
        if(empty($this->mail) || !preg_match('/^[\w\.\+\-=]+@[\w\.\-]+\.[\w\-]+$/', $this->mail))
            $errors[] = "mail";
        
        foreach($this->authorizedKeys as $authorizedKey) {
            if (!is_valid_ssh_pub_key($authorizedKey, false)) {
            $errors[] = 'keys';
            break;
            }
        }

        return $errors;
    }
    
    function user_modules ($all = false) { 
        global $config;
        
        // if all modules including translation based modules
        if ($all) {
            $ldapcriteria = "(&(maintainerUid=$this->uid)(objectClass=gnomeModule))";
        } else { 
            $ldapcriteria = "(&(maintainerUid=$this->uid)(objectClass=gnomeModule)(!(objectClass=localizationModule)))";
        }
        $ldap = LDAPUtil::singleton();
        if(PEAR::isError($ldap)) return $ldap;
        if(!$ldap) {
            return PEAR::raiseError("LDAP authentication failed");
        }
        
        $result = ldap_search($ldap, $config->ldap_modules_basedn, $ldapcriteria, array('cn'));
        if(!$result) {
            return PEAR::raiseError("LDAP search failed: ".ldap_error($ldap));
        }
        $entries = ldap_get_entries($ldap, $result);
        return $entries;
    }

    function user_languages () { 
        global $config;
        
        $ldapcriteria = "(&(maintainerUid=$this->uid)(objectClass=gnomeModule)(objectClass=localizationModule))";
        $ldap = LDAPUtil::singleton();
        if(PEAR::isError($ldap)) return $ldap;
        if(!$ldap) {
            return PEAR::raiseError("LDAP authentication failed");
        }
        
        $result = ldap_search($ldap, $config->ldap_modules_basedn, $ldapcriteria, array('cn', 'localizationTeam'));
        if(!$result) {
            return PEAR::raiseError("LDAP search failed: ".ldap_error($ldap));
        }
        $entries = ldap_get_entries($ldap, $result);
        return $entries;
    }
    
    function is_maintainer ($module) { 
        $modules = $this->user_modules(true);
        for ($i = 0; $i < $modules['count']; $i++) { 
            if ($modules[$i]['cn'][0] == $module) { 
                return true;
            }
        }
        return false;
    }
}

?>

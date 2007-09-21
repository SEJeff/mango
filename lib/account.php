<? 

require_once("mysql.php");
    
require_once("Mail.php");
require_once("Mail/mime.php");
require_once("module.php");
require_once("util.php");

class Account { 
    // User name 
    var $uid;
    // Full name
    var $cn;
    // E-mail 
    var $email;
    // Why account is needed
    var $comment;
    // Public keys 
    var $authorizationkeys;
    // Subversion access
    var $svn_access;
    // Module name
    var $gnomemodule;
    // Translation team
    var $translation;
    // Art web group
    var $art_access;
    // Ftp group
    var $ftp_access;
    // Web group
    var $web_access;
    // Bugzilla 
    var $bugzilla_access;
    // Membership Committee
    var $membctte;
    // @gnome.org alias
    var $mail_alias;
    // Created on
    var $timestamp;
    // Abilities
    var $abilities;
    
    var $mailverified;
    var $maintainerapproved;
    var $maintainercomment;
    var $token;
    
    // If of the account row on the databse table
    var $db_id;

    function Account ($db_id = '') { 
        if (is_numeric($db_id)) {
            $this->db_id = $db_id;
            $this->bring_account();
        } else {
            $this->authorizationkeys = array ();
            $this->gnomemodule = null;
            $this->translation = null;
            $this->svn_access = 'N';
            $this->ftp_access = 'N';
            $this->art_access = 'N';
            $this->web_access = 'N';
            $this->membctte = 'N';
            $this->bugzilla_access = 'N';
            $this->mail_alias = 'N';
            $this->mailverified = false;
            $this->maintainerapproved = false;
            $this->db_id = null;
            $this->timestamp = date ('Y-m-d H:m:s');
            $this->abilities = array ();
            $this->token = $this->create_token();
        }
    }

    function create_token () { 
        global $config;
        
        $salt = $config->token_salt;
        $random_file = fopen("/dev/urandom", "r");
        $random_bytes = base64_encode(fgets($random_file, 20));
        fclose($random_file);
        return sha1('mango'.date('c').$salt.$random_bytes);
    }
    
    function bring_account () { 
        global $config;
    
        $mysql =  MySQLUtil::singleton($config->accounts_db_url);
        $query = "SELECT * FROM account_request WHERE id=".$mysql->escape_string($this->db_id);
        $result = $mysql->query($query);
        $row = mysql_fetch_array ($result);
        $this->abilities = array ();
        $this->uid = $row['uid'];
        $this->cn = $row['cn'];
        $this->email = $row['email'];
        $this->mailverified = ($row['mail_approved'] == 'approved') ? true : false;
        $this->comment = $row['comment'];
        $this->authorizationkeys = split("\n", $row['authorizationkeys']);
        $this->gnomemodule = $row['gnomemodule'];
        $this->translation = $row['translation'];
        $this->svn_access = $row['svn_access'];
        if (!in_array ($this->svn_access, array ('R', 'Y', 'N'))) {
            $this->abilities[] = 'svn_access';
        }
        $this->ftp_access = $row['ftp_access'];
        if (!in_array ($this->ftp_access, array ('R', 'Y', 'N'))) {
            $this->abilities[] = 'ftp_access';
        }
        $this->web_access = $row['web_access'];
        if (!in_array ($this->web_access, array ('R', 'Y', 'N'))) {
            $this->abilities[] = 'web_access';
        }
        $this->bugzilla_access = $row['bugzilla_access'];
        if (!in_array ($this->bugzilla_access, array ('R', 'Y', 'N'))) {
            $this->abilities[] = 'bugzilla_access';
        }
        $this->art_access = $row['art_access'];
        if (!in_array ($this->art_access, array ('R', 'Y', 'N'))) {
            $this->abilities[] = 'art_access';
        }
        $this->membctte = $row['membctte'];
        if (!in_array ($this->membctte, array ('R', 'Y', 'N'))) {
            $this->abilities[] = 'membctte';
        }
        $this->mail_alias = $row['mail_alias'];
        if (!in_array ($this->mail_alias, array ('R', 'Y', 'N'))) {
            $this->abilities[] = 'mail_alias';
        }
        $this->abilities = array_unique($this->abilities);
        $this->maintainerapproved = ($row['maintainer_approved'] == 'approved') ? true : false;
        $this->timestamp = $row['timestamp'];
        
    }
    
    function add_account () { 
        global $config;
        
        $mysql =  MySQLUtil::singleton($config->accounts_db_url);
        $enum_values = array ('Y', 'N', 'A', 'R');
        $query = 'INSERT INTO account_request SET '.
            'uid='.$mysql->escape_string($this->uid).','.
            'cn='.$mysql->escape_string($this->cn).','.
            'email='.$mysql->escape_string($this->email).','.
            'comment='.$mysql->escape_string($this->comment).','.
            ($this->translationmodule != null ? 'translation='.$mysql->escape_string($this->translationmodule).',':'').
            ($this->gnomemodule != null ? 'gnomemodule='.$mysql->escape_string($this->gnomemodule).',' : '').
            'svn_access='.$mysql->escape_enum($this->svn_access, $enum_values).','.
            'ftp_access='.$mysql->escape_enum($this->ftp_access, $enum_values).','.
            'web_access='.$mysql->escape_enum($this->web_access, $enum_values).','.
            'art_access='.$mysql->escape_enum($this->art_access, $enum_values).','.
            'bugzilla_access='.$mysql->escape_enum($this->bugzilla_access, $enum_values).','.
            'membctte='.$mysql->escape_enum($this->membctte, $enum_values).','.
            'mail_alias='.$mysql->escape_enum($this->mail_alias, $enum_values).','.
            (count ($this->authorizationkeys) > 0 ? 'authorizationkeys='.$mysql->escape_string(join("\n", $this->authorizationkeys)) : '').','.
            'timestamp='.$mysql->escape_string($this->timestamp);
                
                $result = mysql_query ($query, $mysql->dbh());
        $this->db_id = mysql_insert_id($mysql->dbh());
        // Create the authentication token 
        $query = 'INSERT INTO account_token SET request_id = '.$mysql->escape_string($this->db_id).', token = '.$mysql->escape_string($this->token);
        $result = mysql_query ($query, $mysql->dbh());      
        $authtokenurl = $config->base_url.'/verify_mail.php?token='.$this->token.'&email='.urlencode($this->email);
        $mailbody = $this->_create_email('authtokenmail', 'authtoken_mail_verification', array ('authtokenlink' => $authtokenurl));
        $subject = "New account request: mail verification";
                $error = $this->_send_email($mailbody, $this->email, $subject);
        if(PEAR::isError($error))
            return $error;
        else 
            return true;
    }
    
    function validate () { 
        global $config;
        $error = array ();
        
        if (empty ($this->uid)) { 
            // userid should not be empty
            $error[] = 'uid';
        }  elseif (!preg_match("/^[a-z]{1,12}$/", $this->uid)) {
            // userid should be all lowercase, max 12 chars
            $error[] = 'uid'; # not valid uid
        } else {
            // Check if there is an LDAP account with this uid
            $user = User::fetchuser($this->uid);
            if (!PEAR::isError($user) && !empty($user->uid)) {
                $error[] = 'uid';
                $error[] = 'existing_uid';
            } else {
                // Check for existing account request
                $mysql = MySQLUtil::singleton($config->accounts_db_url);

                $query = "SELECT 1 FROM account_request WHERE uid = ".$mysql->escape_string($this->uid);
                $result = $mysql->query($query);
                $row = mysql_fetch_row($result);
                if ($row != false) { 
                    $error[] = 'uid';
                }
            }
        }
        if (empty ($this->cn)) { 
            $error[] = 'cn';
        }
        if (empty ($this->email)) { 
            $error[] = 'email';
        } elseif (!preg_match("/^[\w\.\+\-=]+@[\w\.\-]+\.[\w\-]+$/", $this->email)) {
            $error[] = 'email';
        } else {
            // Check for existing LDAP account with this email address
            $user = User::fetchuser($this->email, 'mail');
            if (!PEAR::isError($user) && !empty($user->uid)) {
                $error[] = 'email';
                $error[] = 'existing_email';
            } else {
                // Check if existing account request already used this email address
                $mysql = MySQLUtil::singleton($config->accounts_db_url);
                $query = "SELECT 1 FROM account_request WHERE email = ".$mysql->escape_string($this->email);
                $result = $mysql->query($query);
                $row = mysql_fetch_row($result);
                if ($row != false) { 
                    $error[] = 'email';
                    $error[] = 'existing_email';
                }
            }
        }

        if (empty ($this->comment)) { 
            $error[] = 'comment';
        }
        if (count ($this->authorizationkeys) == 0) { 
            $error[] = 'keys';
        } else {
            foreach($this->authorizationkeys as $authorizedKey) {
                if (!is_valid_ssh_pub_key($authorizedKey)) {
                    $error[] = 'keys';
                    break;
                }
            }
        }
        if ($this->svn_access == "N" && $this->ftp_access  == "N" && $this->web_access  == "N" && $this->bugzilla_access  == "N" && $this->membctte  == "N" && $this->art_access  == "N" && $this->mail_alias == "N") {
            $error[] = 'abilities';
        }

        return $error;
    }
    
    function add_to_node (&$dom, &$formnode) {
        $node = $formnode->appendChild($dom->createElement('uid'));
        $node->appendChild($dom->createTextNode($this->uid));
        $node = $formnode->appendChild($dom->createElement('cn'));
        $node->appendChild($dom->createTextNode($this->cn));
        $node = $formnode->appendChild($dom->createElement('email'));
        $node->appendChild($dom->createTextNode($this->email));
        $node = $formnode->appendChild($dom->createElement('comment'));
        $node->appendChild($dom->createTextNode($this->comment));
        if ($this->translation) { 
            $node = $formnode->appendChild($dom->createElement('group'));
            $node->setAttribute('cn', 'translation');
        }
        if ($this->gnomemodule) { 
            $node = $formnode->appendChild ($dom->createElement('group'));
            $node->setAttribute('cn', 'gnomemodule');
        }
        if ($this->ftp_access == 'Y') { 
            $node = $formnode->appendChild ($dom->createElement('group'));
            $node->setAttribute('cn', 'ftp_access');
        }
        if ($this->web_access == 'Y') { 
            $node = $formnode->appendChild ($dom->createElement('group'));
            $node->setAttribute('cn', 'web_access');
        }
        if ($this->bugzilla_access == 'Y') { 
            $node = $formnode->appendChild ($dom->createElement('group'));
            $node->setAttribute('cn', 'bugzilla_access');
        }
        if ($this->membctte == 'Y') { 
            $node = $formnode->appendChild ($dom->createElement('group'));
            $node->setAttribute('cn', 'membctte');
        }
        if ($this->art_access == 'Y') { 
            $node = $formnode->appendChild ($dom->createElement('group'));
            $node->setAttribute('cn', 'art_access');
        }
        if ($this->mail_alias == 'Y') { 
            $node = $formnode->appendChild ($dom->createElement('group'));
            $node->setAttribute('cn', 'mail_alias');
        }
        $node = $formnode->appendChild($dom->createElement('authorizationkeys'));
        $node->appendChild($dom->createTextNode(join ('\n', $this->authorizationkeys)));
    }
        
    function absorb_input ($variable) { 
        if (isset ($_POST[$variable]))
            $this->$variable = 'Y';
        else 
            $this->$variable = 'N'; 
    }
    
    function verify_email_token () {
        global $config;
        
        $return = '';
        if (!isset ($_REQUEST['email']) || !isset ($_REQUEST['token'])) { 
            return PEAR::raiseError('Bogus');
        }
        $mysql = MySQLUtil::singleton($config->accounts_db_url);
        $query = "SELECT id FROM account_request WHERE mail_approved = 'pending' AND email = ".$mysql->escape_string($_REQUEST['email'])." AND verdict = ".$mysql->escape_string('pending');
        $result = $mysql->query($query);
        $row = mysql_fetch_row($result);
        if ($row != false) { 
            $request_id = $row[0];
            $this->db_id = $request_id;
        } else {
            $query = "SELECT request_id FROM account_token WHERE status = 'approved' AND token = ".$mysql->escape_string ($_REQUEST['token']);
            $result = $mysql->query($query);
            $row = mysql_fetch_row($mysql->query($query));
            if ($row != false) { 
                return PEAR::raiseError('Already verified');
            } else { 
                return PEAR::raiseError('Bogus');
            }
        }
        $query = "SELECT count(*) FROM account_token WHERE request_id = ".$request_id." AND token = ".$mysql->escape_string ($_REQUEST['token']);
        $result = $mysql->query($query);
        $row = mysql_fetch_row($result);
        if ($row == false) { 
            return PEAR::raiseError ("Bogus");
        }
        
        
        // everything alright update database
        if (PEAR::isError ($return))
            return $return;

        $this->bring_account();

        // Update accounts table
        $query = "UPDATE account_request SET mail_approved = 'approved' WHERE id = ".$request_id;
        $result = $mysql->query($query);
        $query = "UPDATE account_token SET status = 'approved' WHERE id = ".$request_id;
        $result = $mysql->query($query);
        // Get queries abilities
        $query = "SELECT * FROM account_request WHERE id = ".$request_id;
        $result = $mysql->query($query);
        // prepare mail headers
        $subject = "New account request: pending approval";
        
        // maintainers who will get the e-mail notification for this account
        $row = mysql_fetch_array ($result);
        if (isset ($row['gnomemodule'])) {
            $ldap_info = array ();
            $maintainers = Module::get_maintainers($row['gnomemodule'], $ldap_info);
            for ($i=0; $i < $maintainers['count']; $i++) { 
                $ldap_uid = $maintainers[$i]['maintaineruid'][0];
                for ($j=0; $j < $ldap_info[$ldap_uid]['count']; $j++) { 
                    $mailbody = $this->_create_email('maintainerapproval', 'maintainer_approval', array ('maintainername' => $ldap_info[$ldap_uid][$j]['cn'][0], 'maintainermodule' => 'module "'.$row['gnomemodule'].'"'));
                    $this->_send_email($mailbody, $ldap_info[$ldap_uid][$j]['mail'][0], $subject);
                    if(PEAR::isError($error))
                        return $error;
                }
            }
        }
        
        if (isset ($row['translation'])) { 
            $ldap_mail = array ();
            $maintainers = Module::get_maintainers($row['translation'], $ldap_mail);
            for ($i=0; $i < $maintainers['count']; $i++) { 
                $ldap_uid = $maintainers[$i]['maintaineruid'][0];
                for ($j=0; $j < $ldap_info[$ldap_uid]['count']; $j++) { 
                    $mailbody = $this->_create_email('maintainerapproval', 'maintainer_approval', array ('maintainername' => $ldap_info[$ldap_uid][$j]['cn'][0], 'maintainermodule' => $row['translation']." translations"));
                    $this->_send_email($mailbody, $ldap_info[$ldap_uid][$j]['mail'][0], $subject);
                    if(PEAR::isError($error))
                        return $error;
                }
            }   
        }
/*          
        if (isset ($row['ftp_access'])) { 
            $ldap_mail = array ();
            $maintainers = Module::get_maintainers('', $ldap_mail);
            for ($i=0; $i < $maintainers['count']; $i++) { 
                $ldap_uid = $maintainers[$i]['maintaineruid'][0];
                for ($j=0; $j < $ldap_info[$ldap_uid]['count']; $j++) { 
                    $mailbody = $this->_create_email('maintainerapproval', 'maintainer_approval', array ('maintainername' => $ldap_info[$ldap_uid][$j]['cn'][0], 'maintainermodule' => 'ftp administration'));
                    $error = $this->_send_email($ldap_info[$ldap_uid][$j]['mail'][0], $headers, $content);
                    if(PEAR::isError($error))
                        return $error;
                }
            }   
        }
        
        if (isset ($row['web_admin'])) { 
            $ldap_mail = array ();
            $maintainers = Module::get_maintainers($row['mango_webadmin'], $ldap_mail);
            for ($i=0; $i < $maintainers['count']; $i++) { 
                $ldap_uid = $maintainers[$i]['maintaineruid'][0];
                for ($j=0; $j < $ldap_info[$ldap_uid]['count']; $j++) { 
                    $mailbody = $this->_create_email('maintainerapproval', 'maintainer_approval', array ('maintainername' => $ldap_info[$ldap_uid][$j]['cn'][0], 'maintainermodule' => 'web administration'));
                    $error = $this->_send_email($ldap_info[$ldap_uid][$j]['mail'][0], $headers, $content);
                    if(PEAR::isError($error))
                        return $error;
                }
            }   
        }
*/              
        if (isset ($row['bugzilla_access'])) { 
            $ldap_mail = array ();
            $maintainers = Module::get_maintainers('bugzilla.gnome.org', $ldap_mail);
            for ($i=0; $i < $maintainers['count']; $i++) { 
                $ldap_uid = $maintainers[$i]['maintaineruid'][0];
                for ($j=0; $j < $ldap_info[$ldap_uid]['count']; $j++) { 
                    $mailbody = $this->_create_email('maintainerapproval', 'maintainer_approval', array ('maintainername' => $ldap_info[$ldap_uid][$j]['cn'][0], 'maintainermodule' => 'bugzilla administration'));
                    $error = $this->_send_email($ldap_info[$ldap_uid][$j]['mail'][0], $headers, $content);
                    if(PEAR::isError($error))
                        return $error;
                }
            }   
        }
        
        if (isset ($row['membctte'])) { 
            $ldap_mail = array ();
            $maintainers = Module::get_maintainers('membctte', $ldap_mail);
            for ($i=0; $i < $maintainers['count']; $i++) { 
                $ldap_uid = $maintainers[$i]['maintaineruid'][0];
                for ($j=0; $j < $ldap_info[$ldap_uid]['count']; $j++) { 
                    $mailbody = $this->_create_email('maintainerapproval', 'maintainer_approval', array ('maintainername' => $ldap_info[$ldap_uid][$j]['cn'][0], 'maintainermodule' => 'membership committee'));
                    $error = $this->_send_email($ldap_info[$ldap_uid][$j]['mail'][0], $headers, $content);
                    if(PEAR::isError($error))
                        return $error;
                }
            }   
        }
        
        if (isset ($row['art_access'])) { 
            $ldap_mail = array ();
            $maintainers = Module::get_maintainers('art-web', $ldap_mail);
            for ($i=0; $i < $maintainers['count']; $i++) { 
                $ldap_uid = $maintainers[$i]['maintaineruid'][0];
                for ($j=0; $j < $$ldap_info[$ldap_uid]['count']; $j++) { 
                    $mailbody = $this->_create_email('maintainerapproval', 'maintainer_approval', array ('maintainername' => $ldap_info[$ldap_uid][$j]['cn'][0], 'maintainermodule' => 'web art administration'));
                    $error = $this->_send_email($ldap_info[$ldap_uid][$j]['mail'][0], $headers, $content);
                    if(PEAR::isError($error))
                        return $error;
                }
            }   
        }
/*          
        if (isset ($row['mail_alias'])) { 
            $ldap_mail = array ();
            $maintainers = Module::get_maintainers($row['mango_mailalias'], $ldap_mail);
            for ($i=0; $i < $maintainers['count']; $i++) { 
                $ldap_uid = $maintainers[$i]['maintaineruid'][0];
                for ($j=0; $j < $ldap_info[$ldap_uid]['count']; $j++) { 
                    $mailbody = $this->_create_email('maintainerapproval', 'maintainer_approval', array ('maintainername' => $ldap_info[$ldap_uid][$j]['cn'][0], 'maintainermodule' => 'gnome.org mail aliases'));
                    $error = $this->_send_email($ldap_info[$ldap_uid][$j]['mail'][0], $headers, $content);
                    if(PEAR::isError($error))
                        return $error;
                }
            }               
        }
*/      
    }
    
    function update_ability ($ability, $approved) { 
        global $config;
        
        $mysql = MySQLUtil::singleton($config->accounts_db_url);
        if (in_array ($ability, array ('ftp_access', 'web_access', 'bugzilla_access', 'art_access', 'mail_alias')) && $this->$ability == 'Y') {
            $query = "UPDATE account_request SET $ability = ".$mysql->escape_string($approved)." WHERE id = ".$this->db_id;
            $result = $mysql->query($query);
            $this->$ability = $approved;
            $this->abilities[] = $ability;
        } elseif ($ability == 'svn_access' && $this->svn_access == 'Y') { 
            $query = "UPDATE account_request SET svn_access = ".$mysql->escape_string($approved)." WHERE id = ".$this->db_id;
            $result = $mysql->query($query);
            $this->svn_access = $approved;
            $this->abilities[] = $approved;
        } else {
            return;
        }
        $this->abilities = array_unique($this->abilities);
        // if every ability is processed
        if (!in_array ($this->svn_access, array ('R', 'Y')) &&
            !in_array ($this->ftp_access, array ('R', 'Y')) &&
            !in_array ($this->web_access, array ('R', 'Y')) &&
            !in_array ($this->art_access, array ('R', 'Y')) &&
            !in_array ($this->bugzilla_access, array ('R', 'Y')) &&
            !in_array ($this->mail_alias, array ('R', 'Y')))
        { 
            //TODO: send email to accounts@gnome.org
            $query = "UPDATE account_request SET maintainer_approved = 'approved' WHERE id = ".$this->db_id;
            $result = $mysql->query($query);

            # Inform account owner
            $mailbody = $this->_create_email('statuschange', 'requestor_status_change', array ('status' => 'approved'));
            $error = $this->_send_email($mailbody, $this->email, 'New account request: status change');
        }
        
        if (in_array ($this->svn_access, array ('R', 'N')) &&
            in_array ($this->ftp_access, array ('R', 'N')) &&
            in_array ($this->web_access, array ('R', 'N')) &&
            in_array ($this->art_access, array ('R', 'N')) &&
            in_array ($this->bugzilla_access, array ('R', 'N')) &&
            in_array ($this->mail_alias, array ('R', 'N')))
        {
                
            $query = "UPDATE account_request SET maintainer_approved = 'rejected' WHERE id = ".$this->db_id;
            $result = $mysql->query($query);
                
            # Inform account owner
            $mailbody = $this->_create_email('statuschange', 'requestor_status_change', array ('status' => 'rejected'));
            $error = $this->_send_email($mailbody, $this->email, 'New account request: rejected');
        }
        
    }

    function update_verdict($verdict) {
        global $config;

        // TODO: Inform the user in case of a rejection verdict

        $mysql = MySQLUtil::singleton($config->accounts_db_url);
        $query = "UPDATE account_request SET verdict = " . $mysql->escape_string($verdict) . " WHERE maintainer_approved = 'approved' AND id = ".$this->db_id;
        $result = $mysql->query($query);
    }
    
    function get_pending_actions ($type = 'gnomemodule', $arg = '') { 
        global $config;
        
        $return = array ();
        $mysql = MySQLUtil::singleton($config->accounts_db_url);
        switch ($type) { 
            case "gnomemodule": 
                $query = "SELECT id FROM account_request WHERE mail_approved = 'approved' AND gnomemodule = ".$mysql->escape_string($arg)." AND maintainer_approved = 'pending'";
                break;
            case "translation":
                $query = "SELECT id FROM account_request WHERE mail_approved = 'approved' AND translation = ".$mysql->escape_string($arg)." AND maintainer_approved = 'pending'";
                break;
            case "ftp_access":
                $query = "SELECT id FROM account_request WHERE mail_approved = 'approved' AND ftp_access = 'Y'";
                break;
            case "web_access":
                $query = "SELECT id FROM account_request WHERE mail_approved = 'approved' AND web_admin = 'Y'";
                break;
            case "bugzilla_access":
                $query = "SELECT id FROM account_request WHERE mail_approved = 'approved' AND bugzilla = 'Y'";
                break;
            case "membctte":
                $query = "SELECT id FROM account_request WHERE mail_approved = 'approved' AND membership = 'Y'";
                break;
            case "art_access":
                $query = "SELECT id FROM account_request WHERE mail_approved = 'approved' AND web_art = 'Y'";
                break;
            case "mail_alias":
                $query = "SELECT id FROM account_request WHERE mail_approved = 'approved' AND mail_alias = 'Y'";
                break;
            case "accountsteam":
                $query = "SELECT id FROM account_request WHERE maintainer_approved = 'approved' AND verdict = 'pending'";
                break;
        }
        $result = $mysql->query($query);
        while ($row = mysql_fetch_array($result)) { 
            $account = new Account ($row['id']);
            $return[] = $account;
        }
        return $return;
    }
    
    function _create_email($mailnodename, $template, $extra_mailnodes = NULL) {
        global $config;

        // Prepare mail body template variables
        $maildom = new DOMDocument('1.0','UTF-8');
        $mailnode = $maildom->appendChild($maildom->createElement($mailnodename));
        $mailnode->setAttribute("mode", $config->mode);
        $mailnode->setAttribute("baseurl", $config->base_url);
        $usernode = $mailnode->appendChild($maildom->createElement("account"));
        $this->add_to_node($maildom, $usernode);
        
        if (!is_null($extra_mailnodes)) {
            foreach ($extra_mailnodes as $key=>$value) {
                $node = $mailnode->appendChild($maildom->createElement($key));
                $node->appendChild($maildom->createTextNode($value));
            }
        }

        // Process the mail body template
        $stylesheet = new DOMDocument('1.0','UTF-8');
        $stylesheet->loadXML(file_get_contents("../templates/$template.xsl"));
        $xsltprocessor = new XSLTProcessor();
        $xsltprocessor->importStylesheet($stylesheet);
        $body = $xsltprocessor->transformToXML($maildom);

        return $body;
    }

    function _send_email($mailbody, $to, $subject) {
        global $config;

        $mime = new Mail_Mime();
        $headers = array(
                "Reply-To" => "Mango <accounts@gnome.org>",
                "From" => "Mango <accounts@gnome.org>",
                "To" => $to,
                "Subject" => $subject,
        );
        $params = array(
                'head_charset' => 'UTF-8',
                'head_encoding' => 'quoted-printable',
                'text_charset' => 'UTF-8',
        );
        $mime->setTXTBody($mailbody);
        $content = $mime->get($params);
        $headers = $mime->headers($headers);
        $mail = &Mail::factory('smtp');

        // DEBUG: Send to support address for debugging purposes
        if ($config->mode != 'live')
            $to = $config->support_email;

        $error = $mail->send($to, $headers, $content);
        return $error;
    }

    function fill_user($user) {
        $user->uid = $this->uid;
        $user->cn = $this->cn;
        $user->mail = $this->email;
        $user->authorizedKeys = $this->authorizationkeys;

        # TODO: Should set description to a small log (mention who approved the 
        # various requests)

        if (in_array("svn_access", $this->abilities)) {$user->groups[] = 'gnomecvs';}
        if (in_array("web_access", $this->abilities)) {$user->groups[] = 'gnomeweb';}
        if (in_array("bugzilla_access", $this->abilities)) {$user->groups[] = 'bugzilla';}
        if (in_array("art_access", $this->abilities)) {$user->groups[] = 'artweb';}
        if (in_array("membctte", $this->abilities)) {$user->groups[] = 'membctte';}
        if (in_array("mail_alias", $this->abilities)) {$user->groups[] = 'mailusers';}
        $users->groups = array_unique($user->groups);

        #if ($this->svn_access == "N" && $this->ftp_access  == "N" && $this->web_access  == "N" && $this->bugzilla_access  == "N" && $this->membctte  == "N" && $this->art_access  == "N" && $this->mail_alias == "N") {
    }
}
?>

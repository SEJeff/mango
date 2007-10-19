<? 

require_once("mysql.php");
    
require_once("Mail.php");
require_once("Mail/mime.php");
require_once("module.php");
require_once("util.php");

class Account { 
    public
        $db_id,             // Id of the account row on the databse table

        $uid,               // User name 
        $cn,                // Full name
        $mail,             // E-mail 
        $comment,           // Why account is needed
        $authorizationkeys, // Public keys 

        $status,
        $is_new_account,
        $is_mail_verified,
        $mail_token,

        $timestamp,         // Created on
        $abilities         // Abilities
    ;
    
    function __construct($search = '', $what = 'id') { 
        global $config;
        $db_id = '';
        $mysql = null;

        if ($what != 'id') {
            $mysql =  MySQLUtil::singleton($config->accounts_db_url);

            $query = "SELECT id FROM account_request WHERE $what = ".$mysql->escape_string ($search);
            $result = $mysql->query($query);
            $row = mysql_fetch_row($mysql->query($query));
            if ($row == false) { 
                return PEAR::raiseError('Account does not exist');
            }
            $db_id = $row[0];
        } else {
            $db_id = $search;
        }

        if (ctype_digit($db_id)) {
            $this->db_id = $db_id;

            if(is_null($mysql))
                $mysql =  MySQLUtil::singleton($config->accounts_db_url);

            $query = "SELECT * FROM account_request WHERE id=".$mysql->escape_string($this->db_id);
            $result = $mysql->query($query);
            $row = mysql_fetch_array ($result);

            $this->abilities = array ();

            foreach ($row as $key => $value) {
                $this->$key = $value;
            }
            $this->authorizationkeys = split("\n", $this->authorizationkeys);

            $query = "SELECT * FROM account_groups WHERE request_id=" . $mysql->escape_string($this->db_id);
            $result = $mysql->query($query);
            while ($row = mysql_fetch_array($result)) {
                $group = $row['cn'];
                $this->abilities[$group] = array();
                foreach ($row as $key => $value) {
                    if ($key != 'cn')
                        $this->abilities[$group][$key] = $value;
                }
            }
        } else {
            $this->db_id = null;
            $this->authorizationkeys = array ();
            $this->is_mail_verified = 'N';
            $this->is_new_account = 'Y';
            $this->status = 'M';
            $this->timestamp = date ('Y-m-d H:m:s');
            $this->abilities = array ();
            $this->mail_token = $this->create_token();
        }

        return $this;
    }

    function create_token () { 
        global $config;
        
        $salt = $config->token_salt;
        $random_file = fopen("/dev/urandom", "r");
        $random_bytes = base64_encode(fgets($random_file, 20));
        fclose($random_file);
        return sha1('mango'.date('c').$salt.$random_bytes);
    }
    
    function add_account () { 
        global $config;
        
        $mysql =  MySQLUtil::singleton($config->accounts_db_url);
        $query = 'INSERT INTO account_request SET ' .
            'uid='.$mysql->escape_string($this->uid).',' .
            'cn='.$mysql->escape_string($this->cn).',' .
            'mail='.$mysql->escape_string($this->mail) .',' .
            'comment='.$mysql->escape_string($this->comment).',' .
            'timestamp='.$mysql->escape_string($this->timestamp) .','.
            (count ($this->authorizationkeys) > 0 ? 'authorizationkeys='.$mysql->escape_string(join("\n", $this->authorizationkeys)) .',' : '') .
            'status=' . $mysql->escape_string($this->status) .','.
            'is_new_account=' . $mysql->escape_string($this->is_new_account) .','.
            'is_mail_verified=' . $mysql->escape_string($this->is_mail_verified) .','.
            'mail_token=' . $mysql->escape_string($this->mail_token)
        ;
        $result = mysql_query ($query, $mysql->dbh());
        $this->db_id = mysql_insert_id($mysql->dbh());
        
        // Create the authentication token
        foreach($this->abilities as $key => $val) {
            $query = 'INSERT INTO account_groups SET ' .
                'request_id='.$mysql->escape_string($this->db_id).','.
                'cn='.$mysql->escape_string($key).','.
                'voucher_group='.$mysql->escape_string($val['voucher_group']).','.
                'verdict='.$mysql->escape_string($val['verdict'])
            ;
            $result = mysql_query ($query, $mysql->dbh());      
            $this->abilities[$key]['id'] = mysql_insert_id($mysql->dbh());
        }

        $authtokenurl = $config->base_url.'/verify_mail.php?token='.$this->mail_token.'&uid='.urlencode($this->uid);
        $mailbody = $this->_create_email('authtokenmail', 'authtoken_mail_verification', array ('authtokenlink' => $authtokenurl));
        $subject = "New account request: mail verification";
        $error = $this->_send_email($mailbody, $this->mail, $subject);
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
        if (!empty($this->cn) && !empty($this->uid)) {
            foreach(explode(' ', $this->cn) as $name) {
                if(strtolower($name) === $this->uid) {
                    $error[] = 'uid'; # UID should not just be the first/last name
                    break;
                }
            }
        }
        if (empty ($this->mail)) { 
            $error[] = 'mail';
        } elseif (!preg_match('/^[\w\.\+\-=]+@[\w\.\-]+\.[\w\-]+$/', $this->mail)) {
            $error[] = 'mail';
        } else {
            // Check for existing LDAP account with this email address
            $user = User::fetchuser($this->mail, 'mail');
            if (!PEAR::isError($user) && !empty($user->uid)) {
                $error[] = 'mail';
                $error[] = 'existing_email';
            } else {
                // Check if existing account request already used this email address
                $mysql = MySQLUtil::singleton($config->accounts_db_url);
                $query = "SELECT 1 FROM account_request WHERE mail = ".$mysql->escape_string($this->mail);
                $result = $mysql->query($query);
                $row = mysql_fetch_row($result);
                if ($row != false) { 
                    $error[] = 'mail';
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
        if (count($this->abilities) == 0) {
            $error[] = 'abilities';
        }


        return $error;
    }
    
    function add_to_node (&$dom, &$formnode) {
        $node = $formnode->appendChild($dom->createElement('uid'));
        $node->appendChild($dom->createTextNode($this->uid));
        $node = $formnode->appendChild($dom->createElement('cn'));
        $node->appendChild($dom->createTextNode($this->cn));
        $node = $formnode->appendChild($dom->createElement('mail'));
        $node->appendChild($dom->createTextNode($this->mail));
        $node = $formnode->appendChild($dom->createElement('comment'));
        $node->appendChild($dom->createTextNode($this->comment));
        foreach(array_keys($this->abilities) as $ability) {
            $node = $formnode->appendChild($dom->createElement('group'));
            $node->setAttribute('cn', $ability);
        }
        $node = $formnode->appendChild($dom->createElement('authorizationkeys'));
        $node->appendChild($dom->createTextNode(join ('\n', $this->authorizationkeys)));
    }
        
    function ability_from_form($ability, $vouchgroup = null) { 
        if (!isset ($_POST[$ability]))
            return;

        if (!array_key_exists($ability, $this->abilities))
            $this->abilities[$ability] = array();

        $this->abilities[$ability]['voucher_group'] = $vouchgroup;
        $this->abilities[$ability]['verdict'] = (is_null($vouchgroup)) ? 'A' : 'P';
    }
    
    function validate_mail_token($token) {
        if (!isset($token) || $this->mail_token !== $token) { 
            return PEAR::raiseError('Bogus');
        }
        if ($this->is_mail_verified != 'N') { 
            return PEAR::raiseError('Already verified');
        }

        return true;
    }

    function approve_mail_token() {
        global $config;
        
        // Update accounts table
        $mysql =  MySQLUtil::singleton($config->accounts_db_url);
        $query = "UPDATE account_request SET is_mail_verified = 'Y' WHERE id = ".$this->db_id;
        $this->is_mail_verified = 'Y';
        $mysql->query($query);

        // Check if we need vouchers
        $verdicts = $this->verdict_status();
        if (in_array('P', $verdicts)) {
            $this->update_status('V'); // There are pending requests
        } else {
            $this->update_status('S');
        }
        
        return true;
    }

    function verdict_status () {
        $tmp = array();

        foreach ($this->abilities as $key => $ability) {
            $tmp[$ability['verdict']] = 1;
        }
        
        return array_keys($tmp);
    }

    function update_ability ($ability, $approved) { 
        global $config;
        
        $mysql = MySQLUtil::singleton($config->accounts_db_url);
        if (array_key_exists($ability, $this->abilities)) {
            $voucher = $_SESSION['user']->uid;
            $query = "UPDATE account_groups ".
                        "SET verdict = ".$mysql->escape_string($approved).",".
                           " voucher = ". $mysql->escape_string($voucher) .
                     " WHERE id = ".$this->abilities[$ability]['id'] . 
                       " AND cn = " . $mysql->escape_string($ability);
            $result = $mysql->query($query);
            $this->abilities[$ability]['verdict'] = $approved;
            $this->abilities[$ability]['voucher'] = $approved;
        } else {
            return;
        }
        
        // has every ability been processed?
        $verdict_status = $this->verdict_status();
        if (count(array_diff($verdict_status, array('R', 'A'))) == 0) {
            // Requested groups have either all been approved or have been rejected.
            // Note: If at least one access has been approved, this means the 
            // account is approved
            
            $new_account_status =  (in_array('A', $verdict_status)) ? 'S' : 'R';
            $this->update_status($new_account_status);
        }
    }

    function update_status($new_status) {
        global $config;

        if ($this->status == $new_status)
            return; // paranoia

        $mysql = MySQLUtil::singleton($config->accounts_db_url);
        $query = "UPDATE account_request SET status = " . $mysql->escape_string($new_status) . " WHERE id = ".$this->db_id;
        $result = $mysql->query($query);

        $this->status = $new_status;

        if ($new_status == 'V') { // Vouchers needed
            $subject = "New account request: voucher needed";
            $ldap_info = array ();
            $all_maintainers = array();

            foreach ($this->abilities as $group => $ability) {
                if ($ability['verdict'] != 'P')
                    continue;

                $maintainers = Module::get_maintainers($ability['voucher_group'], $ldap_info);
                foreach ($maintainers as $maintainer) { 
                    if (array_key_exists($maintainer, $ldap_info)) { 
                        if (!array_key_exists($maintainer, $all_maintainers))
                            $all_maintainers[$maintainer] = array();

                        $all_maintainers[$maintainer][] = $group;
                    }
                }
            }
            foreach ($all_maintainers as $maintainer => $groups) {
                $mailbody = $this->_create_email('maintainerapproval', 'maintainer_approval', array ('maintainername' => $ldap_info[$maintainer]['cn'][0], 'maintainermodule' => $groups));
                $error = $this->_send_email($mailbody, $ldap_info[$maintainer]['mail'][0], $subject);
                if(PEAR::isError($error))
                    return $error;
            }
        } elseif ($new_status == 'S') { // Awaiting setup by accounts team
            # Inform accounts team
            $mailbody = $this->_create_email('informaccounts', 'inform_accounts');
            $error = $this->_send_email($mailbody, 'accounts@gnome.org', 'New account request: ' . $this->uid);

            # Inform requester
            $mailbody = $this->_create_email('statuschange', 'requestor_status_change', array ('status' => 'approved'));
            $error = $this->_send_email($mailbody, $this->mail, 'New account request: status change');
        } elseif ($new_status == 'R') { // Rejected
            # Inform account owner
            $mailbody = $this->_create_email('statuschange', 'requestor_status_change', array ('status' => 'rejected'));
            $error = $this->_send_email($mailbody, $this->mail, 'New account request: rejected');
        }

        return $result;
    }
    
    function get_accountsteam_actions() { 
        global $config;
        
        $return = array ();
        $mysql = MySQLUtil::singleton($config->accounts_db_url);
        $query = "SELECT id " .
                   "FROM account_request ".
                  "WHERE status = 'S' ";

        $result = $mysql->query($query);
        while ($row = mysql_fetch_array($result)) { 
            $account = new Account ($row['id']);
            $return[] = $account;
        }
        return $return;
    }
    
    function get_pending_actions ($vouch_group = 'gnomecvs') { 
        global $config;
        
        $return = array ();
        $mysql = MySQLUtil::singleton($config->accounts_db_url);
        $query = "SELECT DISTINCT account_request.id AS id " .
                   "FROM account_request ".
             "INNER JOIN account_groups ".
                     "ON account_request.id = account_groups.request_id " .
                  "WHERE status = 'V' ".
                    "AND verdict = 'P' ".
                    "AND voucher_group IN (". 
                         implode(',', array_map(array($mysql, 'escape_string'),
                                                $vouch_group)) . ")";

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
        $maildom = new DOMDocument('1.0', 'UTF-8');
        $mailnode = $maildom->appendChild($maildom->createElement($mailnodename));
        $mailnode->setAttribute("mode", $config->mode);
        $mailnode->setAttribute("baseurl", $config->base_url);
        $usernode = $mailnode->appendChild($maildom->createElement("account"));
        $this->add_to_node($maildom, $usernode);
        
        if (!is_null($extra_mailnodes)) {
            foreach ($extra_mailnodes as $key=>$value) {
                $items = is_array($value) ? $value : array($value);
                foreach ($items as $item) {
                    $node = $mailnode->appendChild($maildom->createElement($key));
                    $node->appendChild($maildom->createTextNode($item));
                }
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
        $user->mail = $this->mail;
        $user->authorizedKeys = $this->authorizationkeys;

        # TODO: Should set description to a small log (mention who approved the 
        # various requests)

        foreach ($this->abilities as $groupname => $groupinfo) {
            if ($groupinfo['verdict'] == 'A') {
                $user->groups[] = $groupname;
            }
        }
        $user->groups = array_unique($user->groups);
    }
}
?>

<? 

require_once("mysql.php");
	
require_once("Mail.php");
require_once("Mail/mime.php");
require_once("module.php");

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
	
		$mysql =  new MySQLUtil($config->accounts_db_url);
		$query = "SELECT * FROM account_request WHERE id=".$mysql->escape_string($this->db_id);
		$result = $mysql->query($query);
		$row = mysql_fetch_array ($result);
		$this->uid = $row['uid'];
		$this->cn = $row['cn'];
		$this->email = $row['email'];
		$this->mailverified = ($row['mail_approved'] == 'approved') ? true : false;
		$this->comment = $row['comment'];
		$this->authorizationkeys = $row['authorizationkeys'];
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
		
		$mysql =  new MySQLUtil($config->accounts_db_url);
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
		if ($config->debug == 'enabled') { 
		    $query .= ', mail_approved = "approved"';
		}	
		$result = mysql_query ($query, $mysql->link);		
		$this->db_id = mysql_insert_id($mysql->link);
		// Create the authentication token 
		$query = 'INSERT INTO account_token SET request_id = '.$mysql->escape_string($this->db_id).', token = '.$mysql->escape_string($this->token);
		$result = mysql_query ($query, $mysql->link);		
		$authtokenurl = $config->base_url.'/verify_mail.php?token='.$this->token.'&email='.urlencode($this->email);
		$mailbody = $this->_create_email('authtokenmail', 'authtoken_mail_verification', array ('authtokenlink' => $authtokenurl));
		$subject = "New account request: mail verification";
		$mime = new Mail_Mime();
		$mime->setTXTBody($mailbody);
		$headers = array(
			"Reply-To" => "Mango <accounts@gnome.org>",
			"From" => "Mango <accounts@gnome.org>",
			"To" => $this->email,
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
		if ($config->debug != 'enabled')
		  $error = $mail->send($this->email, $headers, $content);
		else 
		  var_dump ($content);
		if(PEAR::isError($error))
			return $error;
		else 
			return true;
	}
	
	function validate () { 
		$error = array ();
		
		if (empty ($this->uid)) { 
			$error[] = 'uid';
		}  else {
			$user_array = array ($this->uid);  // User::listusers accepts reference to variable
			$user = User::listusers($user_array);
			if ($user['count'] > 0) { 
				$error[] = 'uid';
				$error[] = 'existing_uid';
			}
		}
		if (empty ($this->cn)) { 
			$error[] = 'cn';
		}
		if (empty ($this->email)) { 
			$error[] = 'mail';
		}
		if (empty ($this->comment)) { 
			$error[] = 'comment';
		}
		if (count ($this->authorizationkeys) == 0) { 
			$error[] = 'keys';
		}
		if (!$this->svn_access && !$this->ftp_access && !$this->web_access && !$this->bugzilla_access && !$this->membctte && !$this->art_access && !$this->mail_alias) {
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
		$mysql = new MySQLUtil($config->accounts_db_url);
		$query = "SELECT id FROM account_request WHERE mail_approved = 'pending' AND email = ".$mysql->escape_string($_REQUEST['email'])." AND verdict = ".$mysql->escape_string('pending');
		$result = $mysql->query($query);
		$row = mysql_fetch_row($result);
		if ($row != false) { 
			$request_id = $row[0];
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
		if (!PEAR::isError ($return)) { 
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
			$mime = new Mail_Mime();
			$headers = array(
				"Reply-To" => "Mango <accounts@gnome.org>",
				"From" => "Mango <accounts@gnome.org>",
				"To" => $this->email,
				"Subject" => $subject,
			);
			$params = array(
				'head_charset' => 'UTF-8',
				'head_encoding' => 'quoted-printable',
				'text_charset' => 'UTF-8',
			);
			
			// maintainers who will get the e-mail notification for this account
			$row = mysql_fetch_array ($result);
			if (isset ($row['gnomemodule'])) {
				$ldap_info = array ();
				$maintainers = Module::get_maintainers($row['gnomemodule'], $ldap_info);
				for ($i=0; $i < $maintainers['count']; $i++) { 
					$ldap_uid = $maintainers[$i]['maintaineruid'][0];
					for ($j=0; $j < $ldap_info[$ldap_uid]['count']; $j++) { 
						$mailbody = $this->_create_email('maintainerapproval', 'maintainer_approval', array ('maintainername' => $ldap_info[$ldap_uid][$j]['cn'][0], 'maintainermodule' => 'module "'.$row['gnomemodule'].'"'));
						$mime->setTXTBody($mailbody);
						$content = $mime->get($params);
						$headers = $mime->headers($headers);
						$mail = &Mail::factory('smtp');
						$error = $mail->send($ldap_info[$ldap_uid][$j]['mail'][0], $headers, $content);
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
						$mime->setTXTBody($mailbody);
						$content = $mime->get($params);
						$headers = $mime->headers($headers);
						$mail = &Mail::factory('smtp');
						$error = $mail->send($ldap_info[$ldap_uid][$j]['mail'][0], $headers, $content);
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
						$mime->setTXTBody($mailbody);
						$content = $mime->get($params);
						$headers = $mime->headers($headers);
						$mail = &Mail::factory('smtp');
						$error = $mail->send($ldap_info[$ldap_uid][$j]['mail'][0], $headers, $content);
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
						$mime->setTXTBody($mailbody);
						$content = $mime->get($params);
						$headers = $mime->headers($headers);
						$mail = &Mail::factory('smtp');
						$error = $mail->send($ldap_info[$ldap_uid][$j]['mail'][0], $headers, $content);
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
						$mime->setTXTBody($mailbody);
						$content = $mime->get($params);
						$headers = $mime->headers($headers);
						$mail = &Mail::factory('smtp');
						$error = $mail->send($ldap_info[$ldap_uid][$j]['mail'][0], $headers, $content);
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
						$mime->setTXTBody($mailbody);
						$content = $mime->get($params);
						$headers = $mime->headers($headers);
						$mail = &Mail::factory('smtp');
						$error = $mail->send($ldap_info[$ldap_uid][$j]['mail'][0], $headers, $content);
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
						$mime->setTXTBody($mailbody);
						$content = $mime->get($params);
						$headers = $mime->headers($headers);
						$mail = &Mail::factory('smtp');
						$error = $mail->send($ldap_info[$ldap_uid][$j]['mail'][0], $headers, $content);
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
						$mime->setTXTBody($mailbody);
						$content = $mime->get($params);
						$headers = $mime->headers($headers);
						$mail = &Mail::factory('smtp');
						$error = $mail->send($ldap_info[$ldap_uid][$j]['mail'][0], $headers, $content);
						if(PEAR::isError($error))
							return $error;
					}
				}				
			}
*/		
		} else {
			return $result;
		}
	}
	
	function update_ability ($ability, $approved) { 
		global $config;
		
		$mysql = new MySQLUtil($config->accounts_db_url);
		if (in_array ($ability, array ('ftp_access', 'web_access', 'bugzilla_access', 'art_access', 'mail_alias')) && $this->$ability == 'Y') {
			$query = "UPDATE account_request SET $ability = ".$mysql->escape_string($approved)." WHERE id = ".$this->db_id;
			$result = $mysql->query($query);
			$this->$ability = $approved;
			$this->abilities[] = $ability;
		} elseif ($ability == 'svn_access' && $this->svn_access == 'Y') { 
			$query = "UPDATE account_request SET svn_access = ".$mysql->escape_string($approved)." WHERE id = ".$this->db_id;
			$result = $mysql->query($query);
			$this->svn_access = $approved;
			$this->abilities = $approved;
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
			!in_array ($this->mail_alias, array ('R', 'Y'))) { 
				
				//TODO: send email to accounts@gnome.org
				//TODO: send email to account owner
				$query = "UPDATE account_request SET maintainer_approved = 'approved' WHERE id = ".$this->db_id;
				$result = $mysql->query($query);
		}
		
		if (in_array ($this->svn_access, array ('R', 'N')) &&
			in_array ($this->ftp_access, array ('R', 'N')) &&
			in_array ($this->web_access, array ('R', 'N')) &&
			in_array ($this->art_access, array ('R', 'N')) &&
			in_array ($this->bugzilla_access, array ('R', 'N')) &&
			in_array ($this->mail_alias, array ('R', 'N'))) { 
				
				//TODO: send email to account owner
				$query = "UPDATE account_request SET maintainer_approved = 'rejected' WHERE id = ".$this->db_id;
				$result = $mysql->query($query);
		}
		
	}
	
	function get_pending_actions ($type = 'gnomemodule', $arg = '') { 
		global $config;
		
		$return = array ();
		$mysql = new MySQLUtil($config->accounts_db_url);
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
				$query = "SELECT id FROM account_request WHERE maintainer_approved = 'approved'";
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

		// Prepare mail body template variables
		$maildom = new DOMDocument('1.0','UTF-8');
		$mailnode = $maildom->appendChild($maildom->createElement($mailnodename));
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
}
?>

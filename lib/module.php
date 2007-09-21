<?php

require_once("ldap.php");
require_once("user.php");

class Module {
	// Main attributes
	var $cn;
	var $description;
		
	// Details of the maintainers for this module
	var $maintainerUids;

	// Has 'localizationModule' objectclass set?
	var $localizationModule;
	var $localizationTeam;
	var $mailingList;
		
	function Module() {
		$this->maintainerUids = array();
		$this->localizationModule = false;
	}

	function absorb($entry) {
		$module = new Module();
		$module->cn = $entry['cn'][0];
		$module->description = $entry['description'][0];
		if($entry['maintaineruid']['count'] > 0) {
			for($i = 0; $i < $entry['maintaineruid']['count']; $i++) {
				$module->maintainerUids[] = $entry['maintaineruid'][$i];
			}
		}
		$module->localizationModule = false;
		if(count($entry['objectclass']) > 0) {
			for($i = 0; $i < $entry['objectclass']['count']; $i++) {
				$objectclass = $entry['objectclass'][$i];
				if($objectclass == "localizationModule") {
					$module->localizationModule = true;
					$module->localizationTeam = $entry['localizationteam'][0];
					$module->mailingList = $entry['mailinglist'][0];
				}
			}
		}
		return $module;
	}
	
	function listmodule(&$results, $moduletype = "all") {
		global $config;
		
		// Process list of CNs into an LDAP search criteria
		$ldapcriteria = "";
		foreach($results as $result) {
			$ldapcriteria .= "(cn=". LDAPUtil::ldap_quote($result).")";
		}
		
		if($ldapcriteria) {
			$ldapcriteria = "(&(objectClass=gnomeModule)(|".$ldapcriteria."))";
			switch ($moduletype) { 
				case "devmodule": 
					$ldapcriteria = "(&(&(!(objectClass=localizationModule))(objectClass=gnomeModule))($ldapcriteria))";
					break;
				case "translationmodule":
					$ldapcriteria = "(&(objectClass=localizationModule))($ldapcriteria))";
					break;
				default:
					break;
			}
		} else { 
			switch ($moduletype) { 
				case "devmodule": 
					$ldapcriteria = "(&(!(objectClass=localizationModule))(objectClass=gnomeModule))";
					break;
				case "translationmodule":
					$ldapcriteria = "(objectClass=localizationModule)";
					break;
				default:
					break;
			}
		}
		
		// Connect to LDAP server
		$ldap = LDAPUtil::singleton();
		if(PEAR::isError($ldap)) return $ldap;
		if(!$ldap) {
			return PEAR::raiseError("LDAP authentication failed");
		}
		
		$result = ldap_search($ldap, $config->ldap_modules_basedn, $ldapcriteria, array('cn', 'description','maintainerUid'));
		if(!$result) {
			return PEAR::raiseError("LDAP search failed: ".ldap_error($ldap));
		}
		$entries = ldap_get_entries($ldap, $result);
		
		return $entries;
	}			
	
	function addmodule() {
		global $config;
		
		// Connect to LDAP server
		$ldap = LDAPUtil::singleton();
		if(PEAR::isError($ldap)) return $ldap;
		if(!$ldap) {
			return PEAR::raiseError("LDAP authentication failed");
		}
		

		// Add user entry
		$dn = "cn=".$this->cn.",".$config->ldap_modules_basedn;
		$entry = array();
		$entry['objectclass'][] = "gnomeModule";
		$entry['objectclass'][] = "inetOrgPerson";
		$entry['cn'][] = $this->cn;
		$entry['sn'][] = $this->cn;
		foreach ($this->maintainerUids as $maintainerUid) { 
				$entry['maintainerUid'][] = $maintainerUid;
		}
		if(!empty($this->description))
			$entry['description'][] = $this->description;
		if($this->localizationModule) {
			$entry['objectclass'][] = 'localizationModule';
			$entry['localizationteam'][] = $this->localizationTeam;
			if (!empty($this->mailingList)) 
					$entry['mailinglist'][] = $this->mailingList;
		}
		$result = ldap_add($ldap, $dn, $entry);
		if(!$result) {
			$pe = PEAR::raiseError("LDAP (module) add failed: ".ldap_error($ldap));
			return $pe;
		}

		// Tidy up		

		return true;
	}

	function fetchmodule($cn) {
		global $config;
		
		// Connect to LDAP server
		$ldap = LDAPUtil::singleton();
		if(PEAR::isError($ldap)) return $ldap;
		if(!$ldap) {
			return PEAR::raiseError("LDAP authentication failed");
		}

		// Gather module attributes
		$ldapcriteria = "(&(objectClass=gnomeModule)(cn=".LDAPUtil::ldap_quote($cn)."))";
		$result = ldap_search($ldap, $config->ldap_modules_basedn, $ldapcriteria);
		if(!$result) {
			$pe = PEAR::raiseError("LDAP search failed: ".ldap_error($ldap));
			return $pe;
		}
		$entries = ldap_get_entries($ldap, $result);
		$module = Module::absorb($entries[0]);
		
		// Tidy up		

		return $module;
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
		$oldmodule = Module::fetchmodule($this->cn);
		if(PEAR::isError($oldmodule)) return $oldmodule;
		if(!is_a($oldmodule, "Module")) {
			return PEAR::raiseError("No module (".$this->cn.") found!");
		}
		
		// What's changed in the user attributes?
		$dn = "cn=".$this->cn.",".$config->ldap_modules_basedn;
		$changes = array();
		$modulechanges = array();
		$moduledelete = array();
		if($oldmodule->cn != $this->cn) {
			$modulechanges['cn'][] = $this->cn;
			$changes[] = 'cn';
		}
		if($oldmodule->localizationModule != $this->localizationModule) {
		    if ($this->localizationModule) {
		  	   $modulechanges['objectClass'][] = 'localizationModule';
		    } else {
		        $moduledelete['objectClass'] = 'localizationModule';
		        $moduledelete['localizationTeam'] = $oldmodule->localizationTeam;
		        $moduledelete['mailingList'] = $oldmodule->mailingList;
		    }
			$modulechanges['objectClass'][] = 'gnomeModule';
			$modulechanges['objectClass'][] = 'inetOrgPerson';
			$changes[] = 'localizationTeam';
		}
		
		if ($this->localizationModule && $oldmodule->localizationTeam != $this->localizationTeam) { 
		    $modulechanges['localizationTeam'][] = $this->localizationTeam;
		    $changes[] = 'localizationTeam';
		}

		if ($this->localizationModule && $oldmodule->mailingList != $this->mailingList) { 
		    $modulechanges['mailingList'][] = $this->mailingList;
		    $changes[] = 'mailingList';
		}

		if($oldmodule->description != $this->description) {
			$modulechanges['description'][] = $this->description;
			$changes[] = 'description';
		}
	
		// Maintainers changed?
		if ($oldmodule->maintainerUids != $this->maintainerUids) {
			$modulechanges['maintainerUid'] = $this->maintainerUids;
			$changes[] = 'maintainerUids';
		}
		if(count($moduledelete) > 0) {
			$result = ldap_mod_del($ldap, $dn, $moduledelete);
			if(!$result) {
				$pe = PEAR::raiseError("LDAP (attribute) delete failed: ".ldap_error($ldap));
				return $pe;
			}
		}
		if(count($modulechanges) > 0) {
			$result = ldap_modify($ldap, $dn, $modulechanges);
			if(!$result) {
				$pe = PEAR::raiseError("LDAP (module) modify failed: ".ldap_error($ldap));
				return $pe;
			}
		}

		return $changes;
	}

	
	
	function add_to_node(&$dom, &$formnode) {
		$node = $formnode->appendChild($dom->createElement("cn"));
		$node->appendChild($dom->createTextNode($this->cn));
		$node = $formnode->appendChild($dom->createElement("description"));
		$node->appendChild($dom->createTextNode($this->description));
		$result = array ();
		$entries = User::listusers($result);
		for($i = 0; $i < $entries['count']; $i++) {
			$usernode = $formnode->appendChild($dom->createElement("maintainerUid"));
			$usernode->appendChild($node = $dom->createElement("key"));
			$node->appendChild($dom->createTextNode($entries[$i]['uid'][0]));
			$usernode->appendChild($node = $dom->createElement("value"));
			$node->appendChild($dom->createTextNode($entries[$i]['cn'][0]));
			if (in_array ($entries[$i]['uid'][0], $this->maintainerUids)) {
				$node = $usernode->appendChild($dom->createElement("selected"));
				$node->appendChild($dom->createTextNode("selected"));
			}
		}
		if ($this->localizationModule) {
			$formnode->appendChild($dom->createElement("localizationModule"));
			$node = $formnode->appendChild($dom->createElement("localizationTeam"));
			$node->appendChild($dom->createTextNode($this->localizationTeam));
			$node = $formnode->appendChild($dom->createElement("mailingList"));
			$node->appendChild($dom->createTextNode($this->mailingList));
		}
	}
	
	function validate() {
		$errors = array();
		if(empty($this->cn))
			$errors[] = "cn";
		if(count($this->maintainerUids) == 0)
			$errors[] = "maintainerUids";
			
		return $errors;
	}
	
	function get_maintainers ($cn='', &$info = array ()) { 
		global $config;
		
		$modules = array ();
		$ldapcriteria = "(&(cn=".LDAPUtil::ldap_quote($cn).")(objectClass=gnomeModule))";
		$ldap = LDAPUtil::singleton();
		if(PEAR::isError($ldap)) return $ldap;
		if(!$ldap) {
			return PEAR::raiseError("LDAP authentication failed");
		}
		
		$result = ldap_search($ldap, $config->ldap_modules_basedn, $ldapcriteria, array('maintainerUid'));
		if(!$result) {
			return PEAR::raiseError("LDAP search failed: ".ldap_error($ldap));
		}
		$entries = ldap_get_entries($ldap, $result);
		if ($entries['count'] > 0) {
			$entry_count = $entries['count'];
			for ($i=0; $i < $entry_count; $i++) { 
				$entry_uid = $entries[$i]['maintaineruid'][0];
				$ldapcriteria = "(&(uid=".LDAPUtil::ldap_quote($entry_uid).")(objectClass=posixAccount))";
				$result = ldap_search($ldap, $config->ldap_basedn, $ldapcriteria, array ('mail', 'cn'));
				if (!$result) { 
					return PEAR::raiseError("LDAP search failed: ".ldap_error($ldap));
				}
				$mail_entries = ldap_get_entries($ldap, $result);
				if ($mail_entries['count'] > 0) { 
					$info[$entry_uid] = $mail_entries;
				}
			}
		}
		return $entries;	
	}
}

?>

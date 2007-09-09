#!/usr/bin/php-cgi
<?php

require_once ("../lib/page.php");
require_once ("../lib/account.php");
require_once ("common.php");

$page = new Page("index.xsl");
//$rootnode = $page->result->add_root("page");
$rootnode = $page->result->createElement('page');
$page->result->appendChild($rootnode);
$rootnode->setAttribute("title", "Login page");
$element = $page->result->createElement('homepage');
$rootnode->appendChild($element);

// Check if user is a maintainer of a module or language
$maintainer = false; 
$coordinator = false;
if (isset($_SESSION['user']) && is_a ($_SESSION['user'], 'User')) { 
	$modules = $_SESSION['user']->user_modules();
	if ($modules['count'] > 0)  { 
		$maintainer = true;
	}
	$languages = $_SESSION['user']->user_languages();
	if ($languages['count'] > 0) { 
		$coordinator = true;
	}
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
		foreach ($_POST as $key => $value) { 
				$splittted = split ('-',$key);
				if (is_numeric($splittted[1])) { 
					$request_id = $splittted[1];
					$account = new Account($request_id);
					if ($_SESSION['user']->is_maintainer($account->gnomemodule) || 
						$_SESSION['user']->is_maintainer($account->translation)) {
						switch ($value) { 
							case "approve": 
									$account->update_ability('svn_access', $_SESSION['user']->uid);
									break;
							case "reject": 
									$account->update_ability('svn_access', 'R');
									break;
						}
					}
				}
		}	
	}
	if ($maintainer) { 
		$node = $rootnode->appendChild($page->result->createElement('maintainer'));
		$pending_actions = false;
		for ($i = 0; $i < $modules['count']; $i++) { 
			$module_name = $modules[$i]['cn'][0];
			// if it's mango reserved module pass it
			if (strpos($module_name, 'mango_')) continue;
			$action_list = Account::get_pending_actions('gnomemodule', $module_name);
			if (PEAR::isError($action_list)) { 
				print ("ERROR FOR MODULE: ".$module_name);
			}
			if ($action_list != false) $actions[$module_name] = $action_list;
			if (count(@$actions[$module_name]) > 0) { 
				$pending_actions = true;
			}
		}
		if (!$pending_actions) {
			$element->appendChild($node = $page->result->createElement('module'));
			$node->appendChild($actionnode = $page->result->createElement('moduletemplate'));
			$actionnode->appendChild($node = $page->result->createElement('actiontext'));
			$node->appendChild($page->result->createTextNode("There is no pending request waiting for your action."));
		} else { 
			foreach ($actions as $module=>$action_list) { 
				$element->appendChild($node = $page->result->createElement('module'));
				$node->appendChild($actionnode = $page->result->createElement('moduletemplate'));
				$actionnode->appendChild($node = $page->result->createElement('actiontext'));
				$node->appendChild($page->result->createTextNode("Actions for module: $module (".count($action_list).((count($action_list) > 1) ? " actions)" : " action)")));
				foreach ($action_list as $action) { 
					$actionnode->appendChild($node = $page->result->createElement('request'));
					$node->appendChild ($resultnode = $page->result->createElement('request'));
					$resultnode->appendChild ($node = $page->result->createElement('cn'));
					$node->appendChild ($page->result->createTextNode($action->cn));
					$resultnode->appendChild ($node = $page->result->createElement('comment'));
					$node->appendChild ($page->result->createTextNode($action->comment));
					$resultnode->appendChild ($node = $page->result->createElement('email'));
					$node->appendChild ($page->result->createTextNode($action->email));
					$resultnode->appendChild ($node = $page->result->createElement('requestid'));
					$node->appendChild ($page->result->createTextNode($action->db_id));
				}
			}
		}
	}
	if ($coordinator) { 
		$node = $rootnode->appendChild($page->result->createElement('coordinator'));
		// Check for translation modules
		$pending_actions = false;
		$actions = array ();
		for ($i = 0; $i < $languages['count']; $i++) { 
			$module_name = $languages[$i]['cn'][0];
			$language_name[] = $languages[$i]['localizationteam'][0];
			$action_list = Account::get_pending_actions('translation', $module_name);
			if (PEAR::isError($action_list)) { 
				print ("ERROR FOR MODULE: ".$module_name);
			}
			if ($action_list != false) $actions[$module_name] = $action_list;
			if (count(@$actions[$module_name]) > 0) { 
				$pending_actions = true;
			}
		}
		if (!$pending_actions) {
			$node = $element->appendChild($node = $page->result->createElement('translation'));
			$node->appendChild($node = $page->result->createElement('actiontext'));
			$node->appendChild($page->result->createTextNode("There is no pending internationlization request waiting for your action."));
		} else { 
			$i = 0;
			foreach ($actions as $module=>$action_list) { 
				$node = $rootnode->appendChild($node = $page->result->createElement('translation'));
				$node->appendChild($node = $page->result->createElement('actiontext'));
				$node->appendChild($page->result->createTextNode("Actions for language: $language_name[$i] (".count($action_list).((count($action_list) > 1) ? " actions)" : " action)")));
				$i++;
			}
		}
	}
	// Check for other groups
	if (in_array ('mango_ftp_access', $modules)) { 
		$node = $rootnode->appendChild($page->result->createElement('ftp_access'));
		// Check for translation modules
		$pending_actions = false;
		$actions = array ();
		$action_list = Account::get_pending_actions('ftp_access');
		if (PEAR::isError($action_list)) { 
			print ("ERROR FOR FTP ACCESS");
		}
		if (count($action_list) > 0) {
			$pending_actions = true;
		}
		if (!$pending_actions) {
			$node = $element->appendChild($node = $page->result->createElement('ftp_access'));
			$node->appendChild($node = $page->result->createElement('actiontext'));
			$node->appendChild($page->result->createTextNode("There is no pending ftp access requests."));
		} else { 
			foreach ($actions as $action_list) { 
				$node = $element->appendChild($node = $page->result->createElement('ftp_access'));
				$node->appendChild($node = $page->result->createElement('actiontext'));
				$node->appendChild($page->result->createTextNode("Actions for ftp access (".count($action_list).((count($action_list) > 1) ? " actions)" : " action)")));
			}
		}
	}
	
	if (in_array ('bugzilla.gnome.org', $modules)) { 
		$node = $rootnode->appendChild($page->result->createElement('bugzilla_access'));
		// Check for translation modules
		$pending_actions = false;
		$actions = array ();
		$action_list = Account::get_pending_actions('bugzilla_access');
		if (PEAR::isError($action_list)) { 
			print ("ERROR FOR BUGZILLA ACCESS");
		}
		if (count($action_list) > 0) {
			$pending_actions = true;
		}
		if (!$pending_actions) {
			$node = $element->appendChild($node = $page->result->createElement('bugzilla_access'));
			$node->appendChild($node = $page->result->createElement('actiontext'));
			$node->appendChild($page->result->createTextNode("There is no pending bug access requests."));
		} else { 
			foreach ($actions as $action_list) { 
				$node = $element->appendChild($node = $page->result->createElement('bugzilla'));
				$node->appendChild($node = $page->result->createElement('actiontext'));
				$node->appendChild($page->result->createTextNode("Actions for bugzilla access (".count($action_list).((count($action_list) > 1) ? " actions)" : " action)")));
			}
		}
	}
	
	if (in_array ('art-web', $modules)) { 
		$node = $rootnode->appendChild($page->result->createElement('art_access'));
		// Check for translation modules
		$pending_actions = false;
		$actions = array ();
		$action_list = Account::get_pending_actions('art_access');
		if (PEAR::isError($action_list)) { 
			print ("ERROR FOR WEB ART ACCESS");
		}
		if (count($action_list) > 0) {
			$pending_actions = true;
		}
		if (!$pending_actions) {
			$node = $element->appendChild($node = $page->result->createElement('art_access'));
			$node->appendChild($node = $page->result->createElement('actiontext'));
			$node->appendChild($page->result->createTextNode("There is no pending web art access requests."));
		} else { 
			foreach ($actions as $action_list) { 
				$node = $element->appendChild($node = $page->result->createElement('art_access'));
				$node->appendChild($node = $page->result->createElement('actiontext'));
				$node->appendChild($page->result->createTextNode("Actions for web art access (".count($action_list).((count($action_list) > 1) ? " actions)" : " action)")));
			}
		}
	}
	
}
$page->send();

?>

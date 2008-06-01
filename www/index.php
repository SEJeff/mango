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
$is_maintainer = false; 
$is_coordinator = false;
if (isset($_SESSION['user']) && $_SESSION['user'] instanceof User) { 
    $rootnode->setAttribute("title", "Main page");

    $vouchers = array();
    $modules = $_SESSION['user']->user_modules();
    $entry_count = $modules['count'];
    if ($entry_count > 0)  { 
        $is_maintainer = true;
        for ($i=0; $i < $entry_count; $i++) {
            $vouchers[$modules[$i]['cn'][0]] = $modules[$i];
        }
    }
    $languages = $_SESSION['user']->user_languages();
    $entry_count = $languages['count'];
    if ($languages['count'] > 0) { 
        $is_coordinator = true;
        for ($i=0; $i < $entry_count; $i++) {
            $vouchers[$languages[$i]['cn'][0]] = $languages[$i];
        }
    }
    
    if ($is_maintainer || $is_coordinator) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
            foreach ($_POST as $key => $value) { 
                if (substr($key, 0, 3) !== 'rq:'
                    || ($value !== 'approve' && $value !== 'reject'))
                {
                    continue;
                }

                $splitted = split (':',$key);


                if (!ctype_digit($splitted[1]))
                    continue;

                $request_id = $splitted[1];
                $ability = $splitted[2];
                $account = new Account($request_id);
                
                if ($account->status !== 'V'                            # Must be at verdict stage
                    || !array_key_exists($ability, $account->abilities) # Ability must be one requested
                    || $account->abilities[$ability]['verdict'] !== 'P' # Must be in pending stage
                    || !array_key_exists($account->abilities[$ability]['voucher_group'], $vouchers)) # And this user must be able to vouch for it
                {
                    continue;
                }

                switch ($value) { 
                    case "approve": 
                            $account->update_ability($ability, 'A');
                            break;
                    case "reject": 
                            $account->update_ability($ability, 'R');
                            break;
                }
            }   
        }

        $vouchers_keys = array_keys($vouchers);
        $action_list = Account::get_pending_actions(array_keys($vouchers));

        $pending_actions = false;
        if (count($action_list)) {
            $vnode = $element->appendChild($page->result->createElement('vouchers'));
            foreach ($action_list as $account) { 
                $vnode->appendChild($accountnode = $page->result->createElement('account'));
                $accountnode->setAttribute('cn', $account->cn);
                $accountnode->setAttribute('uid', $account->uid);
                $accountnode->setAttribute('mail', $account->mail);
                $accountnode->setAttribute('comment', $account->comment);
                $accountnode->setAttribute('db_id', $account->db_id);
                $accountnode->appendChild ($groupsnode = $page->result->createElement('groups'));
                foreach($account->abilities as $group => $ability) {
                    if (!is_null($ability['voucher_group'])
                        && $ability['verdict'] == 'P'
                        && in_array($ability['voucher_group'], $vouchers_keys))
                    {
                        $groupnode = $page->result->createElement('group');
                        $groupnode->setAttribute('cn', $group);
                        $groupsnode->appendChild($groupnode);
                    }
                }
            }
        }
    }
}
$page->send();

?>

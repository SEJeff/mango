<?php

require_once("Mail.php");
require_once("Mail/mime.php");
require_once("mysql.php");
require_once("datefield.php");

class FoundationMember {
    // Main attributes
    var $id;
    var $firstname;
    var $lastname;
    var $email;
    var $comments;
    var $renew;             // denotes that member's membership is renewed.
    var $first_added;
    var $need_to_renew;
    var $last_renewed_on;
    var $last_update;   
    var $resigned_on;   // denotes time when a member retired
        
    function absorb($record) {
        $member = new FoundationMember();
        $member->id = $record->id;
        $member->firstname = $record->firstname;
        $member->lastname = $record->lastname;
        $member->email = $record->email;
        $member->comments = $record->comments;
        $member->renew = false;
        $member->first_added = DateField::from_sql($record->first_added);
        $member->last_renewed_on = DateField::from_sql($record->last_renewed_on);
        $member->resigned_on = $record->resigned_on;

        // Check if it's two years since last renewed
        $member->need_to_renew = false;
        if((time() - $member->last_renewed_on) > ((2 * 365 * 24 * 3600) - (30 * 24 * 3600))) {
            $member->need_to_renew = true;
        }

        $member->last_update = DateField::from_sql($record->last_update);
        return $member;
    }
    
    function listmembers(&$results) {
        global $config;
        
        // If no results...
        if(!is_array($results) || count($results) < 1) {
            return array();
        }
        
        // Get database connection
        $db = MySQLUtil::singleton($config->membership_db_url)->dbh();
        if(PEAR::isError($db)) return $db;
        if(!$db) {
            return PEAR::raiseError("MySQL connection failed unexpectedly");
        }

        // Perform query
        $criteria = "";
        foreach($results as $result) {
            $criteria .= ", ".$result;
        }
        $query = "SELECT * FROM foundationmembers WHERE id IN (".substr($criteria, 2).") ORDER BY lastname, firstname";
        $result = mysql_query($query, $db);
        if(!$result) {
            return PEAR::raiseError("Database error: ".mysql_error());
        }

        // Gather results
        $results = array();
        while($record = mysql_fetch_object($result)) {
            $results[] = FoundationMember::absorb($record);
        }

        return $results;
    }           
    
    function addmember() {
        global $config;
        
        // Get database connection
        $db = MySQLUtil::singleton($config->membership_db_url)->dbh();
        if(PEAR::isError($db)) return $db;
        if(!$db) {
            return PEAR::raiseError("MySQL connection failed unexpectedly");
        }

        // Default some values
        $aboutnow = time();
        
        // Prepare query
        $query = "INSERT INTO foundationmembers (";
        $query .= "firstname, lastname, email, comments, first_added, last_renewed_on, last_update";
        $query .= ") VALUES (";
        $query .= MySQLUtil::escape_string($this->firstname).", ";
        $query .= MySQLUtil::escape_string($this->lastname).", ";
        $query .= MySQLUtil::escape_string($this->email).", ";
        $query .= MySQLUtil::escape_string($this->comments).", ";
        $query .= MySQLUtil::escape_date($aboutnow).", ";
        $query .= MySQLUtil::escape_date($this->last_renewed_on).", ";
        $query .= MySQLUtil::escape_date($aboutnow);
        $query .= ")";
        $result = mysql_query($query, $db);
        if(!$result) {
            return PEAR::raiseError("Database error: ".mysql_error());
        }            

        // Gather new id
        $this->id = mysql_insert_id($db);
        $this->first_added = $aboutnow;
        $this->last_update = $aboutnow;
        
        return true;
    }

    function fetchmember($id) {
        global $config;
        
        // Get database connection
        $db = MySQLUtil::singleton($config->membership_db_url)->dbh();
        if(PEAR::isError($db)) return $db;
        if(!$db) {
            return PEAR::raiseError("MySQL connection failed unexpectedly");
        }

        // Perform query
        $query = "SELECT * FROM foundationmembers WHERE id = " . MySQLUtil::escape_string($id);
        $result = mysql_query($query, $db);
        if(!$result) {
            return PEAR::raiseError("Database error: ".mysql_error());
        }

        // Gather results
        $record = mysql_fetch_object($result);
        if(!$record) {
            return PEAR::raiseError("Error unwrapping mirror record");
        }
        
        return FoundationMember::absorb($record);
    }

    function update() {
        global $config;
        
        /* Get old record */
        $oldrec = FoundationMember::fetchmember($this->id);
        if(PEAR::isError($oldrec)) return $oldrec;
        if(!is_a($oldrec, "FoundationMember")) {
            return PEAR::raiseError("No Foundation member exists for the id '".$this->id."'");
        }

        /* Prepare query */
        $sql = "";
        $changes = array();
        if($oldrec->firstname != $this->firstname) {
            $sql .= ", firstname = ".MySQLUtil::escape_string($this->firstname);
            $changes[] = "firstname";
        }
        if($oldrec->lastname != $this->lastname) {
            $sql .= ", lastname = ".MySQLUtil::escape_string($this->lastname);
            $changes[] = "lastname";
        }
        if($oldrec->email != $this->email) {
            $sql .= ", email = ".MySQLUtil::escape_string($this->email);
            $changes[] = "email";
        }
        if($oldrec->comments != $this->comments) {
            $sql .= ", comments = ".MySQLUtil::escape_string($this->comments);
            $changes[] = "comments";
        }
        if($oldrec->last_renewed_on != $this->last_renewed_on) {
            $sql .= ", last_renewed_on = ".MySQLUtil::escape_date($this->last_renewed_on);
            $changes[] = "last_renewed_on";
        }
        if(count($changes) < 1) {
            return PEAR::raiseError("No changes made.");
        }
        $aboutnow = time();
        $query = "UPDATE foundationmembers SET last_update = ".MySQLUtil::escape_date($aboutnow);
        $query .= $sql." WHERE id = ".$this->id;

        // Get database connection
        $db = MySQLUtil::singleton($config->membership_db_url)->dbh();
        if(PEAR::isError($db)) return $db;
        if(!$db) {
            return PEAR::raiseError("MySQL connection failed unexpectedly");
        }

        // Pass query to database
        $result = mysql_query($query, $db);
        if(!$result) {
            return PEAR::raiseError("Database error: ".mysql_error());
        }
        
        $this->last_update = $aboutnow;
        
        return $changes;
    }

    function renew () {
      $this->last_renewed_on = time (); 
      $this->update();    
    }
    
    function add_to_node(&$dom, &$formnode) {
        $node = $formnode->appendChild($dom->createElement("id"));
        $node->appendChild($dom->createTextNode($this->id));
        $node = $formnode->appendChild($dom->createElement("firstname"));
        $node->appendChild($dom->createTextNode($this->firstname));
        $node = $formnode->appendChild($dom->createElement("lastname"));
        $node->appendChild($dom->createTextNode($this->lastname));
        $node = $formnode->appendChild($dom->createElement("email"));
        $node->appendChild($dom->createTextNode($this->email));
        $node = $formnode->appendChild($dom->createElement("comments"));
        $node->appendChild($dom->createTextNode($this->comments));
        DateField::add_to($dom, $formnode, "first_added", $this->first_added);
        DateField::add_to($dom, $formnode, "last_renewed_on", $this->last_renewed_on);
        if ($this->resigned_on == null) {
          $formnode->appendChild($dom->createElement("member"));
        }
        if($this->need_to_renew)
            $node = $formnode->appendChild($dom->createElement("need_to_renew"));
        DateField::add_to($dom, $formnode, "last_update", $this->last_update);
    }
    
    function validate() {
        $errors = array();
        if(empty($this->firstname))
            $errors[] = "firstname";
        if(empty($this->lastname))
            $errors[] = "lastname";
        if(empty($this->email))
            $errors[] = "email";
        if(empty($this->last_renewed_on))
            $errors[] = "last_renewed_on";
        return $errors;
    }
}

?>

<?php

require_once("mysql.php");

class FTPMirror {
	// Main attributes
	var $id;
	var $name;
	var $url;
	var $location;
	var $email;
	var $comments;
	var $description;
	var $active;
	var $last_update;
		
	function absorb($record) {
		$mirror = new FTPMirror();
		$mirror->id = $record->id;
		$mirror->name = $record->name;
		$mirror->url = $record->url;
		$mirror->location = $record->location;
		$mirror->email = $record->email;
		$mirror->comments = $record->comments;
		$mirror->description = $record->description;
		$mirror->active = $record->active == 1;
		$mirror->last_update = $record->last_update;
		return $mirror;
	}
	
	function listmirrors(&$results) {
		global $config;
		
		// If no results...
		if(!is_array($results) || count($results) < 1) {
			return array();
		}
		
        // Get database connection
		$db = MySQLUtil::connectToMySQL($config->mirrors_db_url);
		if(PEAR::isError($db)) return $db;
		if(!$db) {
			return PEAR::raiseError("MySQL connection failed unexpectedly");
		}

		// Perform query
		$criteria = "";
		foreach($results as $result) {
			$criteria .= ", ".$result;
		}
		$query = "SELECT * FROM ftpmirrors WHERE id IN (".substr($criteria, 2).") ORDER BY location";
		$result = mysql_query($query, $db);
		if(!$result) {
			return PEAR::raiseError("Database error: ".mysql_error());
		}

		// Gather results
		$results = array();
		while($record = mysql_fetch_object($result)) {
			$results[] = FTPMirror::absorb($record);
		}

		return $results;
	}			
	
	function addmirror() {
		global $config;
		
        // Get database connection
		$db = MySQLUtil::connectToMySQL($config->mirrors_db_url);
		if(PEAR::isError($db)) return $db;
		if(!$db) {
			return PEAR::raiseError("MySQL connection failed unexpectedly");
		}

		// Prepare query
		$query = "INSERT INTO ftpmirrors (";
		$query .= "name, url, location, email, comments, description";
		$query .= ") VALUES (";
		$query .= MySQLUtil::escape_string($this->name).", ";
		$query .= MySQLUtil::escape_string($this->url).", ";
		$query .= MySQLUtil::escape_string($this->location).", ";
		$query .= MySQLUtil::escape_string($this->email).", ";
		$query .= MySQLUtil::escape_string($this->comments).", ";
		$query .= MySQLUtil::escape_string($this->description);
		$query .= ")";
		$result = mysql_query($query, $db);
		if(!$result) {
			return PEAR::raiseError("Database error: ".mysql_error());
		}            

		// Gather new id
		$this->id = mysql_insert_id($db);
		
		return true;
	}

	function fetchmirror($id) {
		global $config;
		
        // Get database connection
		$db = MySQLUtil::connectToMySQL($config->mirrors_db_url);
		if(PEAR::isError($db)) return $db;
		if(!$db) {
			return PEAR::raiseError("MySQL connection failed unexpectedly");
		}

		// Perform query
		$query = "SELECT * FROM ftpmirrors WHERE id = ".$id;
		$result = mysql_query($query, $db);
		if(!$result) {
			return PEAR::raiseError("Database error: ".mysql_error());
		}

		// Gather results
		$record = mysql_fetch_object($result);
		if(!$record) {
			return PEAR::raiseError("Error unwrapping mirror record");
		}
		
		return FTPMirror::absorb($record);
	}

	function update() {
		global $config;
		
		/* Get old record */
		$oldrec = FTPMirror::fetchmirror($this->id);
		if(PEAR::isError($oldrec)) return $oldrec;
		if(!is_a($oldrec, "FTPMirror")) {
			return PEAR::raiseError("No FTP mirror exists for the id '".$this->id."'");
		}

		/* Prepare query */
		$sql = "";
		$changes = array();
		if($oldrec->name != $this->name) {
			$sql .= ", name = ".MySQLUtil::escape_string($this->name);
			$changes[] = "name";
		}
		if($oldrec->url != $this->url) {
			$sql .= ", url = ".MySQLUtil::escape_string($this->url);
			$changes[] = "url";
		}
		if($oldrec->location != $this->location) {
			$sql .= ", location = ".MySQLUtil::escape_string($this->location);
			$changes[] = "location";
		}
		if($oldrec->email != $this->email) {
			$sql .= ", email = ".MySQLUtil::escape_string($this->email);
			$changes[] = "email";
		}
		if($oldrec->description != $this->description) {
			$sql .= ", description = ".MySQLUtil::escape_string($this->description);
			$changes[] = "description";
		}
		if($oldrec->comments != $this->comments) {
			$sql .= ", comments = ".MySQLUtil::escape_string($this->comments);
			$changes[] = "comments";
		}
		if(!$oldrec->active && $this->active) {
			$sql .= ", active = 1";
			$changes[] = "activated";
		}
		if($oldrec->active && !$this->active) {
			$sql .= ", active = 0";
			$changes[] = "deactivated";
		}
		if(count($changes) < 1) {
			return PEAR::raiseError("No changes made.");
		}
		$query = "UPDATE ftpmirrors SET ".substr($sql, 2)." WHERE id = ".$this->id;

        // Get database connection
		$db = MySQLUtil::connectToMySQL($config->mirrors_db_url);
		if(PEAR::isError($db)) return $db;
		if(!$db) {
			return PEAR::raiseError("MySQL connection failed unexpectedly");
		}

		// Pass query to database
		$result = mysql_query($query, $db);
		if(!$result) {
			return PEAR::raiseError("Database error: ".mysql_error());
		}
		
		return $changes;
	}

	function add_to_node(&$dom, &$formnode) {
		$node = $formnode->append_child($dom->create_element("id"));
		$node->append_child($dom->create_text_node($this->id));
		$node = $formnode->append_child($dom->create_element("name"));
		$node->append_child($dom->create_text_node($this->name));
		$node = $formnode->append_child($dom->create_element("url"));
		$node->append_child($dom->create_text_node($this->url));
		$node = $formnode->append_child($dom->create_element("location"));
		$node->append_child($dom->create_text_node($this->location));
		$node = $formnode->append_child($dom->create_element("email"));
		$node->append_child($dom->create_text_node($this->email));
		$node = $formnode->append_child($dom->create_element("description"));
		$node->append_child($dom->create_text_node($this->description));
		$node = $formnode->append_child($dom->create_element("comments"));
		$node->append_child($dom->create_text_node($this->comments));
		if($this->active) {
			$node = $formnode->append_child($dom->create_element("active"));
		}
		$node = $formnode->append_child($dom->create_element("last_update"));
		$node->append_child($dom->create_text_node($this->last_update));
	}
	
	function validate() {
		$errors = array();
		if(empty($this->name)) {
			$errors[] = "name";
		}
		/* GNOME has this blank in a lot of cases
		if(empty($this->givenName))
			$errors[] = "givenName";
		*/
		if(empty($this->url))
			$errors[] = "url";
		if(empty($this->location))
			$errors[] = "location";
		if(empty($this->email))
			$errors[] = "email";
		return $errors;
	}
}

?>

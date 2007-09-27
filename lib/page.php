<?php

require_once("../lib/config.php");

/**
 * This class is a container for building up a response as an XML fragment.
 * When ready, the response can be returned to the browser in a mutally agreed
 * format. For example, an XML-capable browser could be given the raw XML with
 * an 'xsl:stylesheet' processing instruction, whereas for HTML-capable browsers
 * it could pre-process it against the stylesheet server-side and return the
 * result as HTML. It could even by used to generate hard-copys (using XSL:FO).
 *
 * @package Mango
 */
class Page {
	var $result;
	
	var $stylesheet;
	
	function Page($stylesheet) {
		$this->stylesheet = $stylesheet;
		$this->result = new DOMDocument();
                $node = $this->result->createProcessingInstruction("xml-stylesheet", "href=\"".$this->stylesheet."\" type=\"text/xsl\"");
                $this->result->appendChild($node);
	}

	function validate_post() {
		// SECURITY: Protect against CSRF (POST only)
		// Based upon the method used by Michal Cihar (michal@cihar.com), phpMyAdmin (GPL)
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (!isset($_POST['mango_token']) || !isset($_SESSION[' token_bits '])
			    || !is_scalar($_POST['mango_token']) || !((bool) strlen($_POST['mango_token']))
			    || $_POST['mango_token'] != Page::generate_token())
			{
				$keys = array_keys(array_merge((array)$_REQUEST, (array)$_GET, (array)$_POST, (array)$_COOKIE));
				foreach($keys as $key) {
					unset($_REQUEST[$key], $_GET[$key], $_POST[$key], $GLOBALS[$key]);
				}
			}
		}

	}

	/**
	 * Process the given input file using the given stylesheet
	 */
	function process($filename) {
		$this->result->loadXML(file_get_contents($filename));
		$this->send();
	}

	/**
	 * Parse the content with the stylesheet
	 */
	function send() {
                /* Grab root node */
                $dom = $this->result;
		$xpath = new DOMXPath($dom);
		$result = $xpath->query("/page");
		if($result->length > 0) {
			$pagenode = $result->item(0);
			$this->_add_dynamic_data($dom, $pagenode);
		}


                /* Just let the client transform it */
		header("Content-Type: application/xml");
                echo $dom->saveXML();
                return;


                /* Disabled for now, let the browser do the XSLT conversion
                 *
                 * 

		# Catch debug hook
		if(isset($_REQUEST['debugxml'])) {
			header("Content-Type: application/xml");
                        echo $this->result->saveXML();
			return;
		}
		
		# Process it
		$this->result->xinclude();
		$xsl_file = new DOMDocument('1.0','UTF-8');
		$xsl_file->loadXML(file_get_contents($this->stylesheet));
		$xsltprocessor = new XSLTProcessor();
		$xsltprocessor->importStylesheet($xsl_file);

		# Pass the result to the browser
		header("Content-Type: text/html");
		echo $xsltprocessor->transformToXML($this->result);
	*/	

	}

	/**
	 * Add additional stuff to the pagenode. Intended to be overriden
	 * in client applications.
	 *
	 * @param $dom DOM to create elements from
	 * @param $pagenode Page node to add stuff to
	 *
	 * @access public
	 * @since 1.0
	 */
	function _add_dynamic_data(&$dom, &$pagenode) {
		global $config;

		/* Add runtime mode and useful URLs */
		$thisurl = ($_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://");
		$thisurl .= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		$pagenode->setAttribute("mode", $config->mode);
		$pagenode->setAttribute("baseurl", $config->base_url);
		$pagenode->setAttribute("thisurl", $thisurl);
		$pagenode->setAttribute("token", Page::generate_token());
		$pagenode->setAttribute("support", $config->support_email);

		/* Add page generation date */
		$pagenode->setAttribute("date", strftime("%d %B %y %T %Z"));

		/* If user registered in session, add info */
		if (isset ($_SESSION['user']))
			$user = $_SESSION['user'];
			
		if(isset($user) && is_a($user, "User") && !isset($_REQUEST['logout'])) {
			$pagenode->appendChild($usernode = $dom->createElement("user"));
			$usernode->appendChild($node = $dom->createElement("cn"));
			$node->appendChild($dom->createTextNode($user->cn));

			/* Add group information too */
			$groups = $_SESSION['groups'];
			if(isset($groups) && is_array($groups)) {
				foreach($groups as $group) {
					$groupnode = $pagenode->appendChild($dom->createElement("group"));
					$groupnode->setAttribute("cn", $group);
				}
			}
		}
	}

	/**
	 * Return a HTTP error response (e.g. 404 Not Found).
	 *
	 * @param $response_code integer HTTP error code (e.g. 404)
	 *
	 * @access public
	 * @since 1.0
	 */
	function sendError($response_code) {
		header($_SERVER['SERVER_PROTOCOL']." ".$response_code);
	}
	
	/**
	 * Redirect user to another URL
	 *
	 * @param $otherurl
	 *
	 * @access public
	 * @since 1.0
	 */
	function sendRedirect($url) {
		header("Location: $url");
	}

	/**
	 * Generate unique token
	 *
	 * @access public
	 * @since 1.0
	 */
	function generate_token() {
		if (!isset($_SESSION[' token_bits '])) {
			$_SESSION[' token_bits '] = sha1(uniqid(rand(), true));
		}

		return $_SESSION[' token_bits '];

	}
}

?>

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
		$this->result = new DOMDocument();;
	}

	/**
	 * Process the given input file using the given stylesheet
	 */
	function process($filename) {
		$this->result = domxml_open_file($filename);
		$this->send();
	}

	/**
	 * Parse the content with the stylesheet
	 */
	function send() {
		/* Grab root node */
		//$xpath = xpath_new_context($this->result);
		//$result = xpath_eval($xpath, "/page");
		$xpath = new DOMXPath($this->result);
		$result = $xpath->query("/page");
		if($result->length > 0) {
			$pagenode = $result->item(0);
			//	HDOM::insertProcessingInstruction($dom, $pagenode, "xml-stylesheet", "href=\"".$xslref."\" type=\"text/xsl\"");
			$this->_add_dynamic_data($this->result, $pagenode);
		}

		/* Catch debug hook */
		if(isset($_REQUEST['debugxml'])) {
			header("Content-Type: application/xml");
			//header("Content-Type: text/plain");
			echo $this->result->dump_mem(true);
			return;
		}
		
		/* Process it */
		$this->result->xinclude();
		$xsl_file = new DOMDocument('1.0','UTF-8');
		$xsl_file->loadXML(file_get_contents($this->stylesheet));
		//$xsltprocessor = domxml_xslt_stylesheet_file($this->stylesheet);
		//$xsltprocessor = domxml_xslt_stylesheet_file($this->stylesheet);
		$xsltprocessor = new XSLTProcessor();
		$xsltprocessor->importStylesheet($xsl_file);
		//$result = $xsltprocessor->process($this->result);
		$result = $xsltprocessor->transformToDoc($this->result);
		
		/* Pass the result to the browser */
		header("Content-Type: text/html");
		echo "Content-Type: text/html\n\n";
		//echo $xsltprocessor->result_dump_mem($result);
		echo $result->saveXML();
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

		/* Add page generation date */
		$pagenode->setAttribute("date", strftime("%d %B %y %T %Z"));

		/* If user registered in session, add info */
		$user = $_SESSION['user'];
		if(isset($user) && is_a($user, "User") && !isset($_REQUEST['logout'])) {
			$pagenode->appendChild($usernode = $dom->create_element("user"));
			$usernode->appendChild($node = $dom->create_element("cn"));
			$node->appendChild($dom->create_text_node($user->cn));

			/* Add group information too */
			$groups = $_SESSION['groups'];
			if(isset($groups) && is_array($groups)) {
				foreach($groups as $group) {
					$groupnode = $pagenode->appendChild($dom->create_element("group"));
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
}

?>

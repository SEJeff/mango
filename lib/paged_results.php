<?php

/**
 * This class is a generic 'paged results' container. The 'results' array
 * can be used to store any type of object (usually keys).
 *
 * @package Mango
 * @author Ross Golder <ross@golder.org>
 */
class PagedResults {
	/**
	 * Main results array
	 *
	 * @var array
	 * @access private
	 */
	var $_results;

	/**
	 * Number of results per page
	 *
	 * @var int
	 * @access private
	 */
	var $_page_size;

	/**
	 * Cursor (index into results)
	 *
	 * @var int
	 * @access private
	 */
	var $_cursor;

	/**
	 * Constructor. Takes new search results set and a number of results to
	 * display per page.
	 *
	 * @param $results array of results to deal with
	 *
	 * @access public
	 * @since 1.0
	 */
	function PagedResults($results, $page_size = 25) {
		$this->_results = array();
		$this->_page_size = $page_size;
		$this->_cursor = 0;
		
		// Re-pack array into an indexed array
		$i = 0;
		foreach($results as $result) {
			$this->_results[$i++] = $result;
		}
	}

	/**
	 * Add the current navigation state information as XML to the given node
	 *
	 * @param $dom DOM to create elements with
	 * @param $node DOM node to add details to
	 *
	 * @return the child node it created for it's results
	 *
	 * @access public
	 * @since 1.0
	 */
	function add_navinfo_to(&$dom, &$node) {
		$node->appendChild($pagedresultsnode = $dom->createElement("pagedresults"));
		$subnode = $pagedresultsnode->appendChild($dom->createElement("total_results"));
		$subnode->appendChild($dom->createTextNode(count($this->_results)));
		$subnode = $pagedresultsnode->appendChild($dom->createElement("total_pages"));
		$subnode->appendChild($dom->createTextNode($this->total_pages()));
		$subnode = $pagedresultsnode->appendChild($dom->createElement("result_num"));
		$subnode->appendChild($dom->createTextNode($this->_cursor));
		$subnode = $pagedresultsnode->appendChild($dom->createElement("page_num"));
		$subnode->appendChild($dom->createTextNode($this->page_num()));
		$subnode = $pagedresultsnode->appendChild($dom->createElement("page_size"));
		$subnode->appendChild($dom->createTextNode($this->_page_size));
		return $pagedresultsnode;
	}

	/**
	 * Get the total number of results
	 *
	 * @return total number of results
	 *
	 * @access public
	 * @since 1.0
	 */
	function total_results() {
		if(!isset($this->_results) || !is_array($this->_results)) {
			return 0;
		}
		return count($this->_results);
	}

	/**
	 * Get the total number of pages
	 *
	 * @return total number of pages
	 *
	 * @access public
	 * @since 1.0
	 */
	function total_pages() {
		if(!$this->_results || count($this->_results) < 1) {
			return 0;
		}
		if($this->_page_size > 0) {
			return intval((count($this->_results) - 1) / $this->_page_size) + 1;
		}
		return 0;
	}

	/**
	 * Goto page number
	 *
	 * @param page number to go to
	 *
	 * @access public
	 * @since 1.0
	 */
	function goto_page($page_num) {
		$this->_cursor = 1;
		if($this->_page_size > 0) {
			$this->_cursor = (($page_num - 1) * $this->_page_size);
		}
	}

	/**
	 * Set the number of results to display per page
	 *
	 * @param page_size number of results to display per page
	 *
	 * @access public
	 * @since 0.1.8
	 */
	function set_page_size($page_size) {
		$this->_page_size = $page_size;
	}

	/**
	 * Get this page number
	 *
	 * @return this page number
	 *
	 * @access public
	 * @since 1.0
	 */
	function page_num() {
		if($this->_page_size < 1)
			return 1;
		return intval(($this->_cursor) / $this->_page_size) + 1;
	}

	/**
	 * Get first result number for the current page
	 *
	 * @return first result number for the current page
	 *
	 * @access public
	 * @since 0.1.8
	 */
	function from_result() {
		$pagenum = $this->page_num();
		return intval(($pagenum - 1) * $this->_page_size) + 1;
	}

	/**
	 * Get last result number for the current page
	 *
	 * @return last result number for the current page
	 *
	 * @access public
	 * @since 0.1.8
	 */
	function to_result() {
		$pagenum = $this->page_num();
		$endofpage = intval(($pagenum - 1) * $this->_page_size) + $this->_page_size;
		return min($endofpage, count($this->_results));
	}

	/**
	 * Return results for a given page number
	 *
	 * @param $page_num requested page number
	 *
	 * @return array of results
	 *
	 * @access public
	 * @since 1.0
	 */
	function for_page() {
		// Find bounds
		$start_result = 1;
		$end_result = count($this->_results);
		if($this->_page_size > 0) {
			$page_num = $this->page_num();
			$start_result = (($page_num - 1) * $this->_page_size);
			$end_result = $start_result + $this->_page_size;
		}

		// Get results
		$results = array();
		for($i = $start_result; $i < $end_result; $i++) {
			if(isset($this->_results[$i])) {
				$results[$i] = $this->_results[$i];
			}
		}

		// Return them
		return $results;
	}
}

?>

<?php

class DateField {
	function from_sql($timestamp) {
		$date = split("([^0-9])", $timestamp);
		if(count($date) < 3)
		        return 0;
		$year = $date[0];
		$month = $date[1];
		$day = $date[2];
		$hour = 0;
		$minute = 0;
		$second = 0;
		if(count($date) > 3) {
		        $hour = $date[3];
		        $minute = $date[4];
		        $second = $date[5];
		}
		return date("U", mktime($hour, $minute, $second, $month, $day, $year));
	}
	
	function add_to(&$dom, &$node, $id, $timestamp) {
		$subnode = $node->append_child($dom->create_element($id));
		$subnode->set_attribute("year", strftime("%Y", $timestamp));
		$subnode->set_attribute("month", strftime("%m", $timestamp));
		$subnode->set_attribute("day", strftime("%d", $timestamp));
		$subnode->append_child($dom->create_text_node(strftime("%a, %d %b %Y", $timestamp)));
		return $subnode;
	}
}

?>

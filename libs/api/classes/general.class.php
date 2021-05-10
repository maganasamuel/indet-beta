<?php

/**
@name: General.helper.php
@author: Jesse
@desc:
	Hold all common functions that can be used all throughout the system
 */

class General
{

	/**
		@desc: fetch the requested index from the submitted array
	 */
	public function param(
		$array, // array look up
		$index, // index to look within the array
		$defaultValue = "" // optional: default value in case the requested index is not in the array
	) {
		if (is_array($array)) {
			return isset($array[$index]) ? $array[$index] : $defaultValue;
		}
		return $defaultValue;
	}

	/**
		@desc: fetch the requested index from the submitted array
	 */
	public function convertToNA(
		$array, // array look up
		$index, // index to look within the array
		$defaultValue = "N/A" // optional: default value in case the requested index is not in the array
	) {
		if (is_array($array)) {
			$op = isset($array[$index]) ? $array[$index] : $defaultValue;
			$op = !empty($op) ? $op : $defaultValue;
			return $op;
		}
		return $defaultValue;
	}


	/**
		@desc: remove commas and spaces from numbers
	 */
	public function FilterNumber($number)
	{
		$op = str_replace(',', '', $number);
		$op = str_replace(' ', '', $op);
		return $op;
	}

	/**
		@desc: displays an on page alert
		@alertType:
			primary 	:blue
			secondary 	:lightgrey
			success		:green
			danger		:red
			warning		:yellow
			info		:lightblue
			light		:white
			dark:		:grey
	 */
	public function displayMessage(
		$message = "", // the message to be displayed
		$alertType = "" // type of alert to be displayed following the bootstrap color scheme
	) {
		if ($message == "") {
			return "";
		}
		$returnMessage = <<<EOF
		<div class="alert alert-{$alertType}" role="alert">{$message}</div>
EOF;
		return $returnMessage;
	}


	/**
		@desc: commonly used for data retrieval with parameter "message". Can be used to determine what alertType to be displayed
		@return:
			success for message with [success]
			danger for message with [error]
	 */
	public function getAlertType(
		$message = ""
	) {
		if (strpos($message, "[success]") !== false) {
			return "success";
		} else if (strpos($message, "[error]") !== false) {
			return "danger";
		}
		return "";
	}


	/**
		@desc: used to display "*" for fields that are required.
	 */
	public function required()
	{
		echo "<span class=\"required\">&nbsp;*</span>";
	}

	/**
		@desc: upload a file
	 */
	public function upload(
		$destination, // path of destination directory
		$fileName // filename to be used once uploaded
	) {
		$fileSize = $_FILES["file"]["size"];
		$fileTmp = $_FILES["file"]["tmp_name"];
		$fileType = $_FILES["file"]["type"];

		if ($fileSize > 0) {
			$filepathOrig = $destination . $fileName;
			if (move_uploaded_file($fileTmp, $filepathOrig)) {
				return $filepathOrig;
			} else {
				error_log("[UPLOAD STATUS]: FAILED", 0);
			}
		}
	}


	/**
		@desc: returns a default row for tables with empty dataset
	 */
	function emptyRow(
		$columnCount, // number of columns to be spanned
		$message = "No records found." // message to be displayed.
	) {
		return <<<EOF
    <tr>
      <td colspan="{$columnCount}">{$message}</td>
    </tr>
EOF;
	}

	/**
		@desc: generates generic header for tables
	 */
	function getHeader(
		$columns = [] // array of column names to be displayed at the top most section of a table
	) {
		$header = "<thead>
						<tr class=\"thead-light\">";
		foreach ($columns as $column) {
			$header .= "<th scope=\"col\">{$column}</th>";
		}
		$header .= "</tr>
				</thead>";
		return $header;
	}

	function convertToNDigits($number, $digits)
	{
		return sprintf('%0' . $digits . 'd', $number);
	}

	/**
		@desc: generates a searchbox
	 */
	function getSearchBox(
		$page = "", // page to GET the current request
		$action = "index.php", // the root page(master page)
		$otherTags = "" // other HTML tags can be passed to as extra parameter of the form
	) {
		return <<<EOF
		<form class="form-inline" action="index.php" method="GET">
			<div class="input-group">
				{$otherTags}
				<input type="hidden" name="page" value="{$page}"/>
				<input class="form-control" placeholder="Search" aria-label="search" aria-describedby="basic-search" type="text" name="keyword">
				<div class="input-group-append">
					<button class="input-group-text" id="basic-search">
						<i class="material-icons">&#xE8B6;</i>
					</button>
				</div>
			</div>
		</form>	
EOF;
	}


	function ConvertToCurrency($value, $currency = "$")
	{
		return $currency . number_format($value, 2);
	}
}

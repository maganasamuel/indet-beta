<?php
/**
@name: General.helper.php
@author: Jesse
@desc:
	Hold all common functions that can be used all throughout the system
*/

class DateHelper  {
	
	/**
		@desc: fetch the requested index from the submitted array
	*/
	public function DateToNZFormat (
		$date = "" // optional: default value in case the requested index is not in the array
	) {
		return date("d/m/Y", strtotime($date));
	}

	/**
		@desc: fetch the requested index from the submitted array
	*/
	public function NZFormatToDate (
		$date = "" // optional: default value in case the requested index is not in the array
	) {
		$date_array = explode("/", $date);
		$day = $date_array[0];
		$month = $date_array[1];
		$year = $date_array[2];
		return $year . $month . $day;
	}

	/**
		@desc: fetch the requested index from the submitted array
	*/
	public function DateToNZFormat2 (
		$date = "" // optional: default value in case the requested index is not in the array
	) {
		return date("F d, Y", strtotime($date));
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
	public function displayMessage (
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
	public function getAlertType (
		$message = ""
	) {
		if (strpos($message, "[success]") !== false) {
			return "success";
		}
		else if (strpos($message, "[error]") !== false) {
			return "danger";
		}
		return "";
	}
	
	
	/**
		@desc: used to display "*" for fields that are required.
	*/
	public function required (
	
	) {
		echo "<span class=\"required\">&nbsp;*</span>";
	}
	
	/**
		@desc: upload a file
	*/
	public function upload (
		$destination, // path of destination directory
		$fileName // filename to be used once uploaded
	) {
		$fileSize = $_FILES["file"]["size"];
		$fileTmp = $_FILES["file"]["tmp_name"];
		$fileType = $_FILES["file"]["type"];
		
		if ($fileSize > 0) {
			$filepathOrig = $destination . $fileName;
			if (move_uploaded_file($fileTmp, $filepathOrig)) {
				error_log ("[UPLOAD STATUS]: SUCCESS", 0);
			}
			else {
				error_log("[UPLOAD STATUS]: FAILED", 0);
			}
		}
	}
	
	
	/**
		@desc: returns a default row for tables with empty dataset
	*/
	function emptyRow (
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
	function getHeader (
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
	
	
	/**
		@desc: generates a searchbox
	*/
	function getSearchBox (
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
}
?>
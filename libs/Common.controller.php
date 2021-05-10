<?php
/**
@name: Common.controller.php
@author: Jesse
@desc:
	Handles the login request
*/

if(file_exists("api/classes/database.class.php"))
	include_once("api/classes/database.class.php");
elseif(file_exists("libs/api/classes/database.class.php"))
    include_once("libs/api/classes/database.class.php");
elseif(file_exists("classes/database.class.php"))
    include_once("classes/database.class.php");

class CommonController extends Database {	
    /**
        @desc: Init class
    */
    public function __construct () {
        // init API
        parent::__construct();
    }
	
    /**
        @desc: handle the delete customized invoice request
    */
	public function deleteCustomizedInvoice (
		$id = 0
	) {

        $query = "DELETE FROM customized_invoices where id = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);		
        
		return $dataset;
	}
}
?>
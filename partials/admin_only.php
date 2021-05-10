<?php

	if(!empty($_SESSION['myusertype'])){
		if($_SESSION['myusertype']=="User"){
			
			session_destroy();
			header("Refresh:0; url=index.php");
		}
	}

?>
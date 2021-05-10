<?php

if(session_id()==""){
    session_start();
  }

  if(isset($_SESSION["myusername"])){ 
    if($_SESSION["myusername"]!=""){

      $server_time = $_SERVER['REQUEST_TIME'];
      
      /**
      * for a 10-hour timeout, specified in seconds
      */

      $timeout_duration = 36000;

      if (isset($_SESSION['LAST_ACTIVITY']) && ($server_time - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {  
        session_unset();
        session_destroy();
        echo "SESSION INACTIVE";
        return;
      }
    
      /**
      * Finally, update LAST_ACTIVITY so that our timeout
      * is based on it and not the user's login time.
      */
      echo "SESSION ACTIVE, " . ($timeout_duration - ($server_time - $_SESSION['LAST_ACTIVITY'])) . " seconds left.";
      return;
    }
  }
  else{
        echo "NOONE LOGGED IN";
        return;
  }

?>
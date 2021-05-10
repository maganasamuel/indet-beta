<?php
/**
 * Generic Helper for
 * 
 */

class INDET_GENERIC_HELPER { 
      
    function debuggingLog($header,$variable){
        $isDebuggerActive= false;

        if(!$isDebuggerActive)
            return;

        $op = "<br>";
        $op .=  $header;
        echo $op . "<hr>" . "<pre>";
        var_dump($variable);
        echo "</pre>" . "<hr>";
    }

    function sortFunction( $a, $b ) {
        return strtotime($a["date"]) - strtotime($b["date"]);
    }

    function contains($needle, $haystack)
    {
        return strpos($haystack, $needle) !== false;
    }
}
?>
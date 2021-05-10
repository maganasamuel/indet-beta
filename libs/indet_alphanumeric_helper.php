<?php
/**
 * Generic Helper for
 * 
 */

    class INDET_ALPHANUMERIC_HELPER { 
        //CLASS START
            
        //Convert Date
        function convertToFourDigits($num = 0){
            $op = "";
            if($num < 10){
                $op = "000" . $num;
            }
            elseif($num < 100){
                $op = "00" . $num;
            }
            elseif($num < 1000){
                $op = "0" . $num;
            }
            elseif($num < 10000){
                $op = "" . $num;
            }
            return $op;
        }

        //Convert Date
        function convertToTwoDigits($num = 0){
            $op = "";
            if($num < 10){
                $op = "0" . $num;
            }
            else{
                $op = $num;
            }
            return $op;
        }

        //convert to 2 decimal number
        function convertNum($x){
            return number_format($x, 2, '.', ',');
        }

        //Convert to negative 2 decimal number
        function convertNegNum($x){
            $x=$x*-1;
            return number_format($x, 2, '.', ',');
        }

        //Remove Special Characters
        function removeSpecialCharacters($x){
            return preg_replace("/\([^)]+\)/","",$x); // 'ABC ';
        }

        //CLASS END
    }
?>
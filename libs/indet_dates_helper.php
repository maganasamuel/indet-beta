<?php

/**
 * HELPER CLASS FOR INDET'S DATES
 * 
 */
class INDET_DATES_HELPER
{
    //INDET DATES HELPER START

    //This is actually NZEntryToDateTime 27/05/2019 -> 20190527
    function DateTimeToNZEntry($date_submitted)
    {
        return substr($date_submitted, 6, 4) . substr($date_submitted, 3, 2) . substr($date_submitted, 0, 2);
    }

    //This is actually DateTimeToNZEntry 20190527 -> 27/05/2019
    function NZEntryToDateTime($NZEntry)
    {
        if ($NZEntry == "")
            return "";
        return substr($NZEntry, 6, 2) . "/" . substr($NZEntry, 4, 2) . "/" . substr($NZEntry, 0, 4);
    }

    //23:00 to 11:00 PM
    function MilitaryTimeToCommonTime($time)
    {
        if ($time == "")
            return "";
        return date("h:i A", strtotime($time));
    }

    //11:00 PM to 23:00
    function CommonTimeToMilitaryTime($time)
    {
        if ($time == "")
            return "";

        return date("H:i", strtotime($time));
    }

    function getMonday($date = null)
    {
        if ($date instanceof \DateTime) {
            $date = clone $date;
        } else if (!$date) {
            $date = new \DateTime();
        } else {
            $date = new \DateTime($date);
        }

        $date->setTime(0, 0, 0);
        $Nday = $date->format('N');
        if ($Nday == 1) {
            // If the date is already a Monday, return it as-is
            return $date;
        } elseif ($Nday == 0) {
            // Otherwise, return the date of the nearest Monday in the past
            // This includes Sunday in the previous week instead of it being the start of a new week
            return $date->modify('last monday');
        } else {
            // Otherwise, return the date of the nearest Monday in the past
            // This includes Sunday in the previous week instead of it being the start of a new week
            return $date->modify('monday this week');
        }
    }

    function getDay($day, $d2)
    {
        $output = new stdClass();
        $day1 = clone $day;
        $output->from = $day1;
        $output->to = $day1;

        return clone $output;
    }

    function getWeek($day, $d2)
    {
        $output = new stdClass();
        $day1 = clone $day;
        $day2 = clone $day;
        $output->from = $day1;
        $day2 = $day2->modify('+ 6 days');

        if ($d2 > $day2)
            $output->to = $day2;
        else
            $output->to = $d2;

        return clone $output;
    }

    function getBiMonth($day)
    {
        $output = new stdClass();
        $day1 = clone $day;
        $day2 = clone $day;
        if ($day->format('d') <= 15) {
            $output->note = "First half";
            $output->from = $day1->modify('first day of this month');
            $to = $day2->modify('first day of this month');
            $output->to = $to->modify('+ 14 days');
        } else {
            $output->note = "Second half";
            $output->from = $day1->modify('first day of this month');
            $output->from = $output->from->modify('+ 15 days');
            $output->to = $day2->modify('last day of this month');
        }
        //echo "<br><br><br>Output:" . $output->from->format('Ymd') . "-" . $output->to->format('Ymd') . "<br><br><br>";
        return clone $output;
    }

    function getMonth($month, $year)
    {
        $output = new stdClass();
        $dateString = "$year-$month-01";
        $day1 = date("Ymd", strtotime($dateString));
        $day2 = date("Ymt", strtotime($dateString));
        $output->from = new DateTime($day1);
        $output->to = new DateTime($day2);
        $output->string = $dateString;
        return clone $output;
    }

    function getFlexibleMonth($month, $year, $d_from, $d_until)
    {
        $output = new stdClass();
        $dateString = "$year-$month-01";
        $day1 = date("Y-m-d", strtotime($dateString));
        $day2 = date("Y-m-t", strtotime($dateString));
        $d_1 = new DateTime($day1);
        $d_2 = new DateTime($day2);
        //var_dump($d_from);

        if ($d_from < $d_1)
            $output->from = clone $d_1;
        else
            $output->from = clone $d_from;

        if ($d_until > $d_2)
            $output->to = clone $d_2;
        else
            $output->to = clone $d_until;

        return clone $output;
    }

    function getSumitMonth($day, $thirdMonth = false)
    {
        $output = new stdClass();

        $daysToAdd = 27;

        if ($thirdMonth)
            $daysToAdd += 7;

        $day1 = clone $day;
        $day2 = clone $day;
        $output->from = $day1;

        $day2 = $day2->modify('+ ' . $daysToAdd . ' days');

        $last_day = clone $day2;
        if ($last_day->format("m") == 12 && $last_day->format("d") <= 24) {
            $day2 = $day2->modify('+ 7 days');
        }

        $output->to = $day2;

        return clone $output;
    }

    function GetQuarter($quarter, $year)
    {
        $op = new stdClass();
        $firstDay = $this->getMonday(new \DateTime("$year/01/01"));
        $op->from = clone $firstDay;

        switch ($quarter) {
            case "First":
                break;
            case "Second":
                $op->from = $op->from->modify('+91 days');
                break;
            case "Third":
                $op->from = $op->from->modify('+182 days');
                break;
            case "Fourth":
                $op->from = $op->from->modify('+273 days');
                break;
        }

        $op->to = clone $op->from;
        $op->to = $op->to->modify('+90 days');      //Set Last Day

        //If Last Day is less thanor equal to December 24 then it should have another week to compensate
        if ($quarter == "Fourth" && $op->to->format("d") <= 24) {
            $op->to = $op->to->modify('+7 days');
        }

        return $op;
    }

    function getQuarterMonth($d_from, $d_until, $month_index)
    {
        $output = new stdClass();
        $day_offset = 27;

        $day1 = date("Y-m-d", strtotime($d_from));
        $day2 = date("Y-m-d", strtotime($d_until));
        $d_1 = new DateTime($day1);
        $d_2 = clone $d_1;
        $d_2->modify('+' . $day_offset . ' days');
        $d_3 = new DateTime($day2);
        //var_dump($d_from);

        //echo "First Day: " . $d_from;
        $output->from = clone $d_1;

        if ($month_index == 3)
            $output->to = clone $d_3;
        else
            $output->to = clone $d_2;

        //echo " to: " . $output->to->format('Ymd');
        return clone $output;
    }

    function getNextDate($input)
    {
        $output;

        if ($input->note == "First half") {
            $output = $input->from->modify('first day of this month');
            $output = $input->from->modify('+15 days');
        } else {
            $output = $input->from->modify('first day of next month');
        }
        return $output;
    }


    //INDET DATES HELPER END
}

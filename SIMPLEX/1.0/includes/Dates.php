<?php
define("DAY_SECONDS",86400); //secondi in un giorno
class Dates {
	static function str_to_date_time ($sdate,$ypos,$mpos,$dpos,$sep='/',$tsep=':'){
		//from string date to timestamp
		if(!$sdate) return null;
		$pdate = explode(" ",$sdate);
		$sdate=$pdate[0];
		$tdate =$pdate[1];
		$parts = explode($sep,$sdate);
		$tdate = explode($tsep,$tdate);
		return @mktime($tdate[0],$tdate[1],$tdate[2],$parts[$mpos],$parts[$dpos],$parts[$ypos]);
	}
	static function str_to_date ($sdate,$ypos,$mpos,$dpos,$sep='/'){
		//from string date to timestamp
		if(!$sdate) return null;
		$parts = explode($sep,$sdate);
		return @mktime(0,0,0,$parts[$mpos],$parts[$dpos],$parts[$ypos]);
	}
	static function mysql_date_parse($date){
		//2006-12-12 10:00:00.5
		$tmp = explode(" ",$date);
		$date_part = $tmp[0];
		$time_part = @$tmp[1];
		if(!$time_part) $time_part = "00:00:00";
		
		$ms = 0;
		if(strpos(".",$time_part)){
		   $tmp = explode($time_part,".");
		   $time_part = $tmp[0];
		   $ms  = $tmp[1];
		}
		$d=explode("-",$date_part);
		$t=explode(":",$time_part);
		
		return mktime($t[0],$t[1],$t[2], $d[1],$d[2],$d[0]);
	}
	static function datediff($interval, $datefrom, $dateto, $using_timestamps = false) {
	 /*
	 $interval can be:
	 yyyy - Number of full years
	 q - Number of full quarters
	 m - Number of full months
	 y - Difference between day numbers
	 (eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".)
	 d - Number of full days
	 w - Number of full weekdays
	 ww - Number of full weeks
	 h - Number of full hours
	 n - Number of full minutes
	 s - Number of full seconds (default)
	 */
	
	 if (!$using_timestamps) {
		$datefrom = strtotime($datefrom, 0);
		$dateto = strtotime($dateto, 0);
	 }
	 $difference = $dateto - $datefrom; // Difference in seconds
	
	 switch($interval) {
	
	 case 'yyyy': // Number of full years
		$years_difference = floor($difference / 31536000);
		if (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom), date("j", $datefrom), date("Y", $datefrom)+$years_difference) > $dateto) {
			$years_difference--;
		}
		if (mktime(date("H", $dateto), date("i", $dateto), date("s", $dateto), date("n", $dateto), date("j", $dateto), date("Y", $dateto)-($years_difference+1)) > $datefrom) {
		   $years_difference++;
		}
		$datediff = $years_difference;
		break;
	  
	 case "q": // Number of full quarters
		$quarters_difference = floor($difference / 8035200);
		while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($quarters_difference*3), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
		$months_difference++;
		}
		$quarters_difference--;
		$datediff = $quarters_difference;
		break;
	  
	 case "m": // Number of full months
	  
	 $months_difference = floor($difference / 2678400);
		while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
		$months_difference++;
		}
		$months_difference--;
		$datediff = $months_difference;
		break;
	  
	 case 'y': // Difference between day numbers
		
	   $datediff = date("z", $dateto) - date("z", $datefrom);
	   break;
	  
	 case "d": // Number of full days
	  
		$datediff = floor($difference / 86400);
		break;
	  
	 case "w": // Number of full weekdays
	  
		$days_difference = floor($difference / 86400);
		$weeks_difference = floor($days_difference / 7); // Complete weeks
		$first_day = date("w", $datefrom);
		$days_remainder = floor($days_difference % 7);
		$odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
		if ($odd_days > 7) { // Sunday
		$days_remainder--;
		}
		if ($odd_days > 6) { // Saturday
		$days_remainder--;
		}
		$datediff = ($weeks_difference * 5) + $days_remainder;
		break;
	  
	case "ww": // Number of full weeks
		$datediff = floor($difference / 604800);
		break;
	
	case "h": // Number of full hours
		$datediff = floor($difference / 3600);
		break;
		
	case "n": // Number of full minutes
		$datediff = floor($difference / 60);
		break;
	default: // Number of full seconds (default)
		$datediff = $difference;
		break;
	}
	 return $datediff;
	}
	
	function dateCompare ($dt1,$dt2=null){
		if(!$dt1)  $dt1 = strtotime("now");
		if(is_string($dt1)) $dt1=strtotime($dt1);
		if(!$dt2)  $dt2 = strtotime("now");
		if(is_string($dt2)) $dt2=strtotime($dt2);
		if($dt1<$dt2) return -1;
		if($dt1>$dt2) return 1;
		return 0;
	}
	
	function dateAdd($interval, $number, $date) {
		if(is_string($date)) {
			$date=strtotime($date);
		}
		$date_time_array = getdate($date);
		$hours = $date_time_array['hours'];
		$minutes = $date_time_array['minutes'];
		$seconds = $date_time_array['seconds'];
		$month = $date_time_array['mon'];
		$day = $date_time_array['mday'];
		
		$year = $date_time_array['year'];
		switch ($interval) {
			case 'yyyy':
				$year+=$number;
				break;
			case 'q':
				$year+=($number*3);
				break;
			case 'm':
				$month+=$number;
				break;
			case 'ww':
				$day+=($number*7);
				break;
			case 'h':
				$hours+=$number;
				break;
			case 'n':
				$minutes+=$number;
				break;
			case 's':
				$seconds+=$number;
				break;
			default:
			$day+=$number;
		}
		$timestamp= mktime($hours,$minutes,$seconds,$month,$day,$year);
		return $timestamp;
	}
	
}
?>
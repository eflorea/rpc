<?php

namespace RPC;

/**
 * Incorporates a set of most used functions which manipulate dates and time
 * 
 * @package Core
 */
class Date
{
	
	/**
	 * Timestamp
	 * 
	 * @var int
	 */
	protected $timestamp;
	
	/**
	 * Converts the given date to a timestamp and stores it
	 * 
	 * @param mixed  $date   Date string or timestamp
	 * @param string $format Format of the date
	 */
	public function __construct( $date = null, $format = 'Y-m-d' )
	{
		if( is_null( $date ) )
		{
			$this->timestamp = time();
		}
		else
		{
			$this->timestamp = self::getTimestamp( $date, $format );
		}
	}
	
	/**
	 * Checks to see if the date is between two other dates. It can receive two
	 * parameters (two RPC_Date objects) or four paramters (two dates as strings
	 * each with it's format)
	 */
	public function between()
	{
		$args = func_get_args();
		
		/**
		 * I assume two RPC_Date objects have been passed
		 */
		if( func_num_args() == 2 )
		{
			return ( $args[0]->getDate( 'U' ) < $this->timestamp ) && ( $this->timestamp < $args[1]->getDate( 'U' ) );
		}
		/**
		 * I assume two dates with their formats have been passed
		 */
		elseif( func_num_args() == 4 )
		{
			return ( self::getTimestamp( $args[0], $args[1] ) < $this->timestamp ) && ( $this->timestamp < self::getTimestamp( $args[2], $args[3] ) );
		}
		else
		{
			throw new Exception( 'The function expects two or four parameters' );
		}
	}
	
	/**
	 * Sets a new date within the class
	 * 
	 * @param mixed  $date   Date string of timestamp
	 * @param string $format Format of the date
	 * 
	 * @return self
	 */
	public function setDate( $date, $format = 'Y-m-d' )
	{
		$this->timestamp = self::getTimestamp( $date, $format );
		return $this;
	}
	
	/**
	 * Returns a date in the corresponding format
	 * 
	 * @param string $format Format of the date
	 * 
	 * @return string The date string or timestamp
	 */
	public function getDate( $format = 'Y-m-d' )
	{
		return date( $format, $this->timestamp );
	}
	
	/**
	 * Calculates a timestamp of $date based on the given $format
	 * 
	 * @param mixed  $date   Date string or timestamp
	 * @param string $format Format of the date
	 * 
	 * @return int Date timestamp
	 */
	static public function getTimestamp( $date, $format = 'Y-m-d' )
	{
		if( ! $date )
		{
			return null;
		}
		elseif( $format == 'U' )
		{
			return $date;
		}
		else
		{
			list( $Y, $m, $d, $H, $i, $s ) = self::getVariables( $date, $format );
			
			return strtotime( $Y . '-' . $m . '-' . $d . ' ' . $H . ':' . $i . ':' . $s );
		}
	}
	
	/**
	 * Converts dates from one format to another
	 *
	 * @param mixed  $date    The date to be changed
	 * @param string $iformat Initial format
	 * @param string $fformat Final format
	 * 
	 * @return string The date in the final format
	 */
	static public function changeFormat( $date, $iformat, $fformat )
	{
		if( ! $date )
		{
			return null;
		}
		elseif( !self::validDate( $date, $iformat ) )
		{
			return '';
		}
		else
		{
			return date( $fformat, self::getTimestamp( $date, $iformat ) );
		}
	}
	
	/**
	 * Checkes if a given date is valid, according to its format
	 * 
	 * @param string $date   Date string
	 * @param string $format Format of the date
	 * 
	 * @return boolean Wheter the date is valid
	 */
	static public function validDate( $date, $format = 'Y-m-d' )
	{
		if( ! $date )
		{
			return false;
		}
		
		$characters = array
		(
			'Y' => '[0-9]{4}', // Year;           e.g. 2005
			'm' => '[0-9]{2}', // Month;          e.g. 09
			'd' => '[0-9]{2}', // Day;            e.g. 17
			'H' => '[0-9]{2}', // Hour;           e.g. 21
			'i' => '[0-9]{2}', // Minute;         e.g. 09
			's' => '[0-9]{2}', // Second;         e.g. 03
			'U' => '[0-9]{10}' // UNIX Timestamp; e.g. 1124969051
		);
		
		$regex = $format;
		foreach( $characters as $c => $r )
		{
			$regex = str_replace( $c, $r, $regex );
		}
		
		$regex = '/^' . str_replace( '/', '\/', $regex ) . '$/';
		
		if( ! preg_match(  $regex, $date ) )
		{
			return false;
		}
		
		list( $Y, $m, $d, $H, $i, $s ) = self::getVariables( $date, $format );
		$Y = (int) $Y;
		$m = (int) $m;
		$d = (int) $d;
		$H = (int) $H;
		$i = (int) $i;
		$s = (int) $s;
		
		return @checkdate( $m, $d, $Y );
	}
	
	/**
	 * Adds the amount of time specified by the amount * unit.
	 * Unit can be one of:
	 * - y: year
	 * - m: month
	 * - d: day
	 * - h: hour
	 * - i: minute
	 * - s: second
	 * 
	 * @param int    $amount Number of units
	 * @param string $unit   Type of time unit
	 * 
	 * @return RPC_Date
	 */
	public function add( $amount, $unit )
	{
		switch( strtolower( $unit ) )
		{
			case 'y':
				$unit = 'year';
				break;
			case 'm':
				$unit = 'month';
				break;
			case 'd':
				$unit = 'day';
				break;
			case 'h':
				$unit = 'hour';
				break;
			case 'i':
				$unit = 'minute';
				break;
			case 's':
				$unit = 'second';
				break;
		}
		
		return new RPC\Date( strtotime( '+' . $amount . ' ' . $unit, $this->timestamp ), 'U' );
	}
	
	/**
	 * Substracts the amount specified by the amount * unit.
	 * Unit can be one of:
	 * - y: year
	 * - m: month
	 * - d: day
	 * - h: hour
	 * - i: minute
	 * - s: second
	 * 
	 * @param int $amount  Number of units
	 * @param string $unit Type of time unit
	 * 
	 * @return RPC_Date
	 */
	public function substract( $amount, $unit )
	{
		switch( strtolower( $unit ) )
		{
			case 'y':
				$unit = 'year';
				break;
			case 'm':
				$unit = 'month';
				break;
			case 'd':
				$unit = 'day';
				break;
			case 'h':
				$unit = 'hour';
				break;
			case 'i':
				$unit = 'minute';
				break;
			case 's':
				$unit = 'second';
				break;
		}
		
		return new RPC\Date( strtotime( '-' . $amount . ' ' . $unit, $this->timestamp ), 'U' );
	}
	
	/**
	 * Calculates the difference between 2 dates
	 * 
	 * $interval can be:
	 * yyyy - Number of full years
	 * q - Number of full quarters
	 * m - Number of full months
	 * y - Difference between day numbers
	 * (eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".)
	 * d - Number of full days
	 * w - Number of full weekdays
	 * ww - Number of full weeks
	 * h - Number of full hours
	 * n - Number of full minutes
	 * s - Number of full seconds (default)
	 * 
	 * @param string $interval Format in which to return the difference
	 * @param mixed  $datefrom From date as string or int
	 * @param mixed  $dateto   To date as string or int
	 * 
	 * @return int Number of units separating the two dates
	 */
	static public function dateDiff( $interval, $datefrom, $dateto )
	{
		$datefrom = is_string( $datefrom ) ? strtotime( $datefrom ) : $datefrom;
		$dateto   = is_string( $dateto ) ? strtotime( $dateto ) : $dateto;
		
		$difference = $dateto - $datefrom; // Difference in seconds

		switch( $interval )
		{
			case 'yyyy': // Number of full years
				$years_difference = floor( $difference / 31536000 );
				if( mktime( date( 'H', $datefrom ), date( 'i', $datefrom ), date( 's', $datefrom ), date( 'n', $datefrom ), date( 'j', $datefrom ), date( 'Y', $datefrom ) + $years_difference ) > $dateto ) 
				{
					$years_difference--;
				}
				if( mktime( date( 'H', $dateto ), date( 'i', $dateto ), date( 's', $dateto ), date( 'n', $dateto), date( 'j', $dateto ), date( 'Y', $dateto )-( $years_difference + 1 ) ) > $datefrom )
				{
					$years_difference++;
				}
				$datediff = $years_difference;
				break;
			case 'q': // Number of full quarters
				$quarters_difference = floor( $difference / 8035200 );
				while( mktime( date( 'H', $datefrom ), date( 'i', $datefrom ), date( 's', $datefrom ), date( 'n', $datefrom ) + ( $quarters_difference * 3 ), date( 'j', $dateto ), date( 'Y', $datefrom ) ) < $dateto )
				{
					$months_difference++;
				}
				$quarters_difference--;
				$datediff = $quarters_difference;
				break;
			case 'm': // Number of full months
				$months_difference = floor($difference / 2678400);
				while( mktime( date( 'H', $datefrom ), date( 'i', $datefrom ), date( 's', $datefrom ), date( 'n', $datefrom ) + ( $months_difference ), date( 'j', $dateto ), date( 'Y', $datefrom ) ) < $dateto )
				{
					$months_difference++;
				}
				$months_difference--;
				$datediff = $months_difference;
				break;
			case 'y': // Difference between day numbers
				$datediff = date( 'z', $dateto ) - date( 'z', $datefrom );
				break;
			case 'd': // Number of full days
				$datediff = floor( $difference / 86400 );
				break;
			case 'w': // Number of full weekdays
				$days_difference = floor( $difference / 86400 );
				$weeks_difference = floor( $days_difference / 7 ); // Complete weeks
				$first_day = date( 'w', $datefrom );
				$days_remainder = floor( $days_difference % 7 );
				$odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
				if ( $odd_days > 7 )
				{ // Sunday
					$days_remainder--;
				}
				if( $odd_days > 6 )
				{ // Saturday
					$days_remainder--;
				}
				$datediff = ( $weeks_difference * 5 ) + $days_remainder;
				break;
			case 'ww': // Number of full weeks
				$datediff = floor( $difference / 604800 );
				break;
			case 'h': // Number of full hours
				$datediff = floor( $difference / 3600 );
				break;
			case 'n': // Number of full minutes
				$datediff = floor( $difference / 60 );
				break;
			default: // Number of full seconds (default)
				$datediff = $difference;
				break;
		}    
		
		return $datediff;
	}
	
	
	/**
	 * Extracts the parts from a given date according to a format and returns an
	 * array like this:
	 * <pre>
	 * array
	 * (
	 *     0 => year,
	 *     1 => month,
	 *     2 => day,
	 *     3 => hour,
	 *     4 => minute,
	 *     5 => second
	 * )
	 * </pre>
	 * 
	 * @param string $date   Date string
	 * @param string $format Format of the date
	 */
	static public function getVariables( $date, $format = 'Y-m-d' )
	{
		$characters = array
		(
			'Y' => 4, // Year;           e.g. 2005
			'm' => 2, // Month;          e.g. 09
			'd' => 2, // Day;            e.g. 17
			'H' => 2, // Hour;           e.g. 21
			'i' => 2, // Minute;         e.g. 09
			's' => 2, // Second;         e.g. 03
			'U'       // UNIX Timestamp; e.g. 1124969051
		);
		
		$Y = '0000';
		$m = '00';
		$d = '00';
		$H = '00';
		$i = '00';
		$s = '00';
		
		$positions = array();
		
		foreach( $characters as $character => $length )
		{
			if( ( $pos = strpos( $format, $character ) ) !== false )
			{
				$positions[$character] = $pos;
			}
		}
		asort( $positions );
		
		$add = 0;
		foreach( $positions as $character => $position )
		{
			$positions[$character] = $position + $add;
			$add += $characters[$character] - 1;
		}
		
		foreach( $positions as $character => $position )
		{
			$$character = substr( $date, $position, $characters[$character] );
		}
		
		return array( $Y, $m, $d, $H, $i, $s );
	}
	
	/**
	 * Change the seconds in the current date
	 * 
	 * @param int $seconds
	 * 
	 * @return RPC_Date
	 */
	public function setSeconds( $seconds )
	{
		$seconds = ( 0 <= $seconds ) && ( 60 >= $seconds ) ? $seconds : 0;
		
		list( $y, $m, $d, $h, $m, $s ) = explode( '/', date( 'Y/m/d/H/i/s', $this->timestamp ) );
		
		$s = $seconds;
		
		return new RPC\Date( mktime( $h, $m, $s, $m, $d, $y ), 'U' );
	}
	
	/**
	 * Change the minutes in the current date
	 * 
	 * @param int $minutes
	 * 
	 * @return RPC_Date
	 */
	public function setMinutes( $minutes )
	{
		$minutes = ( 0 <= $minutes ) && ( 60 >= $minutes ) ? $minutes : 0;
		
		list( $y, $m, $d, $h, $m, $s ) = explode( '/', date( 'Y/m/d/H/i/s', $this->timestamp ) );
		
		$m = $minutes;
		
		return new RPC\Date( mktime( $h, $m, $s, $m, $d, $y ), 'U' );
	}
	
	/**
	 * Change the hours in the current date
	 * 
	 * @param string $hour
	 * 
	 * @return RPC_Date
	 */
	public function setHour( $hour )
	{
		$hour = ( 0 <= $hour ) && ( 23 >= $hour ) ? $hour : 0;
		
		list( $y, $m, $d, $h, $m, $s ) = explode( '/', date( 'Y/m/d/H/i/s', $this->timestamp ) );
		
		$h = $hour;
		
		return new RPC\Date( mktime( $h, $m, $s, $m, $d, $y ), 'U' );
	}
	
	/**
	 * Change the day in the current date
	 * 
	 * @param int $day
	 * 
	 * @return RPC_Date
	 */
	public function setDay( $day )
	{
		$day = ( 1 <= $day ) && ( 31 >= $day ) ? $day : 0;
		
		list( $y, $m, $d, $h, $m, $s ) = explode( '/', date( 'Y/m/d/H/i/s', $this->timestamp ) );
		
		$d = $day;
		
		return new RPC\Date( mktime( $h, $m, $s, $m, $d, $y ), 'U' );
	}
	
	/**
	 * Change the month in the current date
	 * 
	 * @param int $month
	 * 
	 * @return RPC_Date
	 */
	public function setMonth( $month )
	{
		$seconds = ( 1 <= $month ) && ( 12 >= $month ) ? $month : 0;
		
		list( $y, $m, $d, $h, $m, $s ) = explode( '/', date( 'Y/m/d/H/i/s', $this->timestamp ) );
		
		$m = $month;
		
		return new RPC\Date( mktime( $h, $m, $s, $m, $d, $y ), 'U' );
	}
	
	/**
	 * Change the year in the current date
	 * 
	 * @param int $year
	 * 
	 * @return RPC_Date
	 */
	public function setYear( $year )
	{
		$year = ( 1901 <= $year ) && ( 2038 >= $year ) ? $year : 0;
		
		list( $y, $m, $d, $h, $m, $s ) = explode( '/', date( 'Y/m/d/H/i/s', $this->timestamp ) );
		
		$y = $year;
		
		return new RPC\Date( mktime( $h, $m, $s, $m, $d, $y ), 'U' );
	}
	
}

?>

<?php

namespace RPC;

/**
 * Wrapper for a number of functions which could not be grouped under other
 * classes
 *
 * @package Core
 */
class Util
{

	/**
	 * Determines whether an IP address is in a given IP range.
	 *
	 * @param range A string giving the IP range. You can use semi-colons to
	 *              seperate multiple IP's or IP ranges and you should use
	 *              a dash to specify a range. Asterisks may be used in single IP
	 *              addresses. Examples of valid ranges are: "127.0.0.1",
	 *              "192.168.0.1-192.168.0.100", "192.168.0.*" and
	 *              "192.168.0.1-192.168.0.100;127.0.0.1". If the range is an
	 *              empty string, this function will always return @p true.
	 * @param ip    A string giving the IP address.
	 *
	 * @return bool
	 */
	public static function isIpInRange( $range, $ip )
	{
		if( $range == '' )
		{
			return true;
		}

		$ranges   = explode(';', $range);
		$ipFields = explode('.', $ip);
		$ipFields[0] = (int) ( isset( $ipFields[0]) ? $ipFields[0] : 0 );
		$ipFields[1] = (int) ( isset( $ipFields[1]) ? $ipFields[1] : 0 );
		$ipFields[2] = (int) ( isset( $ipFields[2]) ? $ipFields[2] : 0 );
		$ipFields[3] = (int) ( isset( $ipFields[3]) ? $ipFields[3] : 0 );

		foreach( $ranges as $range )
		{
			if( strchr( $range, '-' ) )
			{
				list( $range1, $range2 ) = explode( '-', $range, 2 );
				$range1Fields = explode( '.', $range1 );
				$range1Fields[0] = (int) $range1Fields[0];
				$range1Fields[1] = (int) $range1Fields[1];
				$range1Fields[2] = (int) $range1Fields[2];
				$range1Fields[3] = (int) $range1Fields[3];
				$range2Fields = explode('.', $range2);
				$range2Fields[0] = (int) $range2Fields[0];
				$range2Fields[1] = (int) $range2Fields[1];
				$range2Fields[2] = (int) $range2Fields[2];
				$range2Fields[3] = (int) $range2Fields[3];

				$match = true;
				for( $i = 0; $i < 4; $i++ )
				{
					if( ( $ipFields[$i] < $range1Fields[$i] ) ||
					    ( $ipFields[$i] > $range2Fields[$i] ) )
					{
						$match = false;
						break;
					}
				}

				if( $match == true )
				{
					return true;
				}
			}
			else
			{
				$rangeFields = explode( '.', $range );
				$rangeFields[0] = ( $rangeFields[0] == '*' ? '*' : (int) $rangeFields[0] );
				$rangeFields[1] = ( $rangeFields[1] == '*' ? '*' : (int) $rangeFields[1] );
				$rangeFields[2] = ( $rangeFields[2] == '*' ? '*' : (int) $rangeFields[2] );
				$rangeFields[3] = ( $rangeFields[3] == '*' ? '*' : (int) $rangeFields[3] );

				$match = true;
				for( $i = 0; $i < 4; $i++ )
				{
					if( $rangeFields[$i] == '*' )
					{
						continue;
					}

					if( $ipFields[$i] != $rangeFields[$i] )
					{
						$match = false;
						break;
					}
				}

				if( $match == true )
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Transforms a bidimensional array (like most result sets from a database
	 * are) into an array where the option values are mapped as keys and their
	 * content as the corresponding value.
	 *
	 * @param array  $array Bidimensional array or array of objects
	 * @param string $key   Column in every array which will represent the
	 *                      value of the option
	 * @param string $value Column in every array which will represent the
	 *                      content of the option
	 *
	 * @return array
	 */
	public static function arrayToOptions( $array, $key, $value )
	{
		$options = array();

		foreach( $array as $entry )
		{
			if( is_array( $entry ) )
			{
				$options[$entry[$key]] = $entry[$value];
			}
			else
			{
				$options[$entry->$key] = $entry->$value;
			}
		}

		return $options;
	}
	

	/**
	 * Generates a password according to the type given. Actually the function
	 * uses another two functions and calls the appropriate with an apropriate
	 * number of parameters according to the first parameter.
	 *
	 * @param int $nice          Type of password to generate
	 * @param int $length        Length of the returned password
	 * @param string $allowchars Characters allowed in the password
	 *
	 * @return string
	 *
	 * @author Lars B. Jensen <lars.jensen@ljweb.com>
	 */
	public static function generatePassword( $nice = 1, $length = 8, $allowchars = '' )
	{
		switch( $nice )
		{
			// pronouncable password
			case 1:
				return self::generatePronouncablePassword( $length );
			// lowercase only, fix similar
			case 2:
				return self::generatePasswordAdvanced( $length, 0, 1, 0, 0, 1, $allowchars );
			// lowercase and numbers only, fix similar
			case 3:
				return self::generatePasswordAdvanced( $length, 0, 1, 1, 0, 1, $allowchars );
			// both lower and uppercase chars and numbers , fix similar
			case 4:
				return self::generatePasswordAdvanced( $length, 1, 1, 1, 0, 1, $allowchars );
			// all types of letters, including special chars, fix similar
			case 5:
				return self::generatePasswordAdvanced( $length, 1, 1, 1, 1, 1, $allowchars );
			// oh my :) the real deal - get it all and dont fix similars
			case 6:
				return self::generatePasswordAdvanced( $length, 1, 1, 1, 1, 0, $allowchars );
			// $nice contained illegal value, go for the easy 3
			default:
				return self::generatePasswordAdvanced( $length, 1, 1, 1, 0, 1 );
		}
	}

	/**
	 * Generates an easy to remember password.
	 *
	 * @param int $length
	 *
	 * @return string
	 *
	 * @author Lars B. Jensen <lars.jensen@ljweb.com>
	 */
	public static function generatePronouncablePassword( $length = 8 )
	{
		$valid_consonant = 'bcdfghjkmnprstv';
		$valid_vowel     = 'aeiouy';
		$valid_numbers   = '0123456789';

		$consonant_length = strlen( $valid_consonant );
		$vowel_length     = strlen( $valid_vowel );
		$numbers_length   = strlen( $valid_numbers );

		$password = '';
		while( strlen( $password ) < $length )
		{
			if( mt_rand( 0, 2 ) != 1 )
			{
				$password .= $valid_consonant[mt_rand( 0, ( $consonant_length - 1 ) )] . $valid_vowel[mt_rand( 0, ( $vowel_length - 1 ) )] . $valid_consonant[mt_rand( 0, ( $consonant_length - 1 ) )];
			}
			else
			{
				$password .= $valid_numbers[mt_rand( 0, ( $numbers_length - 1 ) )];
			}
		}

		return substr( $password, 0, $length );
	}

	/**
	 * Very customisable function to generate a password. Usually it is called by
	 * the generatePassword function to ease things.
	 *
	 * @param int    $length
	 * @param bool   $allow_uppercase
	 * @param bool   $allow_lowercase
	 * @param bool   $allow_numbers
	 * @param bool   $allow_special
	 * @param bool   $fix_similar
	 * @param string $valid_charset
	 *
	 * @return string
	 *
	 * @author Lars B. Jensen <lars.jensen@ljweb.com>
	 */
	public static function generatePasswordAdvanced( $length = 8, $allow_uppercase = 1, $allow_lowercase = 1, $allow_numbers = 1, $allow_special = 1, $fix_similar = 0, $valid_charset = '' )
	{
		if( ! $valid_charset )
		{
			if( $allow_uppercase )
			{
				$valid_charset .= 'ABCDEFGHIJKLMNOPQRSTUVXYZ';
			}
			if( $allow_lowercase )
			{
				$valid_charset .= 'abcdefghijklmnopqrstuvxyz';
			}
			if( $allow_numbers )
			{
				$valid_charset .= '0123456789';
			}
			if( $allow_special )
			{
				$valid_charset .= '!#$%&()*+-./;<=>@\_';
			}
		}

		$charset_length = strlen( $valid_charset );

		$password = '';
		while( strlen( $password ) < $length )
		{
			$char = $valid_charset[mt_rand( 0, ( $charset_length - 1 ) )];

			if( ( $fix_similar &&
			      ! strpos( 'O01lI5S', $char ) ) ||
			    ! $fix_similar )
		    {
		    	$password .= $char;
		    }
		}

		return $password;
	}

	/**
	 * Retrieve or set session cookie for csrf_token based on name 
	 */
	public static function csrf( $name = 'general' )
	{
		if( ! isset( $_SESSION['csrf_token_' . $name] ) )
		{
			$_SESSION['csrf_token_' . $name] = md5( $name . session_id() . rand() );
		}

		return $_SESSION['csrf_token_' . $name];
	}

	/**
	* Error Logging Interface
	*
	* We use this as a simple mechanism to access the logging
	* class and send messages to be logged.
	*
	* @access	public
	* @return	void
	*/
	public static function log_message( $level = 'error', $message, $php_error = false )
	{
		$log = new Log;

		if ( ! $log->_threshold )
		{
			return;
		}

		$log->write_log( $level, $message, $php_error );
	}

    /**
     * Returns client IP address used for request and falls back on php_sapi_name if that not present
     *
     * @return string
     */
    public static function get_client_source() {

        // Return early if CLI
        if(PHP_SAPI === 'cli') return PHP_SAPI;

        // Should try these in the order they are listed
        $remote_sources = ['HTTP_X_REAL_IP', 'REMOTE_ADDR'];

        foreach ($remote_sources as $source) {
            if (isset($_SERVER[$source])) {
                return $_SERVER[$source];
            }
        }

        // If all else fails return something
        return 'unknown';
    }

}

?>

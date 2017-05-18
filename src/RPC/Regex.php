<?php

namespace RPC;


/**
 * Regular expressions class
 * 
 * Please note that all the regexes below are aproximations, all of them are
 * considered "good enough" for most cases, but email validation, for example,
 * is not a simple matter of matching against a regex
 * 
 * @package Core
 */
class Regex
{
	
	/**
	 * Should match a HTTP, FTP & HTTPS URI resource
	 */
	const URI = '/^(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:\/~\+#;]*[\w\-\@?^=%&\/~\+#;])?$/';
	
	/**
	 * Should match any domain name
	 */
	const DOMAIN = '/^[A-Za-z0-9-]+(\\.[A-Za-z0-9-]+)*(\\.[A-Za-z]{2,})$/';
	
	/**
	 * Should match a person's name
	 */
	const NAME = '/^[a-zA-Z0-9\&\.\(\)\+\=\@\!\*\'\"\# -]{3,64}$/';
	
	/**
	 * Should match an application username
	 */
	const USERNAME = '/^[a-zA-Z]{1}[a-zA-Z0-9]{2,31}$/';
	
	/**
	 * Should match any (3 digit representation of a) currency
	 */
	const CURRENCY =  '/^[a-zA-Z]{3}$/';
	
	/**
	 * Should match a password
	 */
	//const PASSWORD = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,32}$/';
	
	const PASSWORD = '/^.{6,32}$/';
	
	/**
	 * Should match an email address
	 * Source: http://emailregex.com/
	 */
	// const EMAIL = '/^(\w|[-])+(\.(\w|[-])+)*@((\[([0-1]?\d?\d|2[0-4]\d|25[0-5])\.([0-1]?\d?\d|2[0-4]\d|25[0-5])\.([0-1]?\d?\d|2[0-4]\d|25[0-5])\.([0-1]?\d?\d|2[0-4]\d|25[0-5])\])|((([a-zA-Z0-9])+(([-])+([a-zA-Z0-9])+)*\.)+([a-zA-Z])+(([-])+([a-zA-Z0-9])+)*))$/';
	const EMAIL = '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/';
	
	/**
	 * Used to split a CSV line into parts
	 */
	const CSV_LINE = '#,(?=([^"]*"[^"]*")*(?![^"]*"))#';
	
	/**
	 * Visa Credit Card
	 */
	const VISA_CC = '/^4(?:[0-9]{12}|[0-9]{15})$/';
	
	/**
	 * MasterCard Credit Card
	 */
	const MASTER_CC = '/^5[1-5][0-9]{14}$/';
	
	/**
	 * Discover Credit Card
	 */
	const DISCOVER_CC = '/^3(?:0[0-5][0-9]{11}|[68][0-9]{12})$/';
	
	/**
	 * Amex Credit Card
	 */
	const AMEX_CC = '/^3[47][0-9]{13}$/';

	/**
	 * US Phone
	 * E.g. (111) 222-3333 || 111-222-3333 || 111.222.3333 || 111 222 3333 || 1112223333
	 */
	const US_PHONE = '/^\(?([0-9]{3})\)?[-\.\s]?([0-9]{3})[-\.\s]?([0-9]{4})$/';

	const US_ZIP = '/(^\d{5}$)|(^\d{5}-\d{4}$)/';
	
	/**
	 * Regular expression against which all values will be matched
	 * 
	 * @var string
	 */
	protected $regex = null;
	
	/**
	 * Class constructor which sets the regex
	 * 
	 * @param string $regex
	 */
	public function __construct( $regex )
	{
		$this->regex = $regex;
	}
	
	/**
	 * Returns the interal regex
	 * 
	 * @return string
	 */
	public function getRegex()
	{
		return $this->regex;
	}
	
	/**
	 * Matches the given value against the regex
	 * 
	 * Parameters are the same as with <code>preg_match_all</code>
	 * 
	 * $matches[0] is an array of first set of matches, $matches[1] is an array
	 * of second set of matches, and so on. For every occurring match the
	 * appendant string offset will also be returned:
	 * <code>
	 * array
	 * (
	 *     0 => array
	 *     (
	 *         0 => array
	 *         (
	 *             0 => entire match
	 *             1 => offset
	 *         ),
	 *         1 => array
	 *         (
	 *             0 => first subpattern
	 *             1 => offset
	 *         ),
	 *         .
	 *         .
	 *         .
	 *     )
	 *     .
	 *     .
	 *     .
	 * )
	 * </code>
	 * 
	 * @param string $subject
	 * @param array  $matches
	 * @param int    $offset
	 * 
	 * @return bool
	 */
	public function match( $subject, & $matches = array(), $offset = 0 )
	{
		return preg_match_all( $this->regex, $subject, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE, $offset );
	}
	
	/**
	 * Replaces portions of the string which match the regex with $replacement
	 * 
	 * @param string $string
	 * @param string $replacement
	 * @param int    $limit
	 * @param int    $count
	 * 
	 * @return string
	 */
	public function replace( $subject, $replacement, $limit = -1, & $count = 0 )
	{
		return preg_replace( $this->regex, $replacement, $subject, $limit, $count );
	}
	
	/**
	 * Returns the object's regex 
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return $this->regex;
	}
	
}

?>

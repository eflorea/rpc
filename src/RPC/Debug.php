<?php

namespace RPC;

/**
 * Wrapper for a few debugging/optimization useful methods
 * 
 * @todo Remove email & all that stuff, eventually adding signals so
 * as to decouple domains
 */
class Debug
{
	
	/**
	 * Contains all the timers started throught a script
	 *
	 * @var array
	 */
	protected static $timer = array();
	
	/**
	 * From which error level on should we handle it?
	 */
	const LEVEL = 6135; // 2039 for E_ALL &~ E_NOTICE
	
	/**
	 * Display errors/exceptions or not?
	 */
	const DISPLAY = true;
	
	/**
	 * Encoded logfile of unique errors (from there we will know which errors
	 * already happened so they don't get mailed or logged again and again)
	 * NOTICE: You will need this if you're using logging or e-mails
	 */
	const UNIQUE_LOGFILE = '';
	
	/**
	 * Error logfile ('' for no log)
	 */
	const LOGFILE = '';
	
	/**
	 * Where should error/exception reports be sent to? ('' for no mailing)
	 */
	const EMAIL_ADDRESS = '';
	
	/**
	 * Starts a timer with a given label
	 *
	 * @param string $label
	 */
	public static function startTimer( $label = 'default' )
	{
		self::$timer[$label]['start'] = microtime( true );
	}
	
	/**
	 * Returns the difference in seconds since the last call of getDelta (or
	 * startTimer if getDelta hasn't been called before)
	 *
	 * @param string $label
	 * 
	 * @return float
	 */
	public static function getDelta( $label = 'default' )
	{
		$now = microtime( true );
		if( ! isset( self::$timer[$label]['last'] ) )
		{
			self::$timer[$label]['last'] = $now;
			
			return $now - self::$timer[$label]['start'];
		}
		else
		{
			$diff = $now - self::$timer[$label]['last'];
			
			self::$timer[$label]['last'] = $now;
			
			return $diff;
		}
	}
	
	/**
	 * Returns the difference in seconds since the start of the timer
	 *
	 * @param string $label
	 * 
	 * @return float
	 */
	public static function getElapsedTime( $label = 'default' )
	{
		return microtime( true ) - self::$timer[$label]['start'];
	}
	
	/**
	 * Dump any resource with syntax highlighting, indenting and variable type
	 * information
	 * 
	 * @param mixed $data Variable to be dumped
	 * @param bool  $die  If true the script will end the script execution
	 * 
	 * @author Jari Berg Jensen <jari@razormotion.com>
	 * @author Eduard Florea
	 */
	public static function dump( $data, $die = true )
	{
		ob_start();
		var_dump( $data );
		$c = ob_get_contents();
		ob_end_clean();
	
		$c = preg_replace( "/\r\n|\r/", "\n", $c );
		
		// Insert linebreak after the first '{' character
		if( strpos( $c, '{' ) !== false )
		{
			$c = substr_replace( $c, "{\n", strpos( $c, '{' ), 1 );
		}
		
		$c = str_replace( "]=>\n", '] = ', $c );
		$c = preg_replace( '/= {2,}/', '= ', $c );
		$c = preg_replace( "/\[\"(.*?)\"\] = /i", "[$1] = ", $c );
		$c = preg_replace( '/  /', "    ", $c );
		$c = preg_replace( "/}\n( {0,})\[/i", "}\n\n$1[", $c );
		$c = preg_replace( "/\"\"(.*?)\"/i", "\"$1\"", $c );
		
		$c = htmlspecialchars( $c, ENT_NOQUOTES );
		
		// Syntax Highlighting of Strings. This seems cryptic, but it will also allow non-terminated strings to get parsed.
		$c = preg_replace( "/(\[[\w ]+\] = string\([0-9]+\) )\"(.*?)/sim", "$1<span class=\"string\">\"", $c );
		$c = preg_replace( "/(\"\n{1,})( {0,}\})/sim", "$1</span>$2", $c );
		$c = preg_replace( "/(\"\n{1,})( {0,}\[)/sim", "$1</span>$2", $c );
		$c = preg_replace( "/(string\([0-9]+\) )\"(.*?)\"\n/sim", "$1<span class=\"string\">\"$2\"</span>\n", $c );
		
		$regex = array
		(
			// Numbers
			'numbers' => array
			(
				'/(^|] = )(array|float|int|string|object\(.*\))\(([0-9\.]+)\)/i',
				'$1$2(<span class="number">$3</span>)'
			),
			// Keywords
			'null' => array
			(
				'/(^|] = )(null)/i',
				'$1<span class="keyword">$2</span>'
			),
			'bool' => array
			(
				'/(bool)\((true|false)\)/i',
				'$1(<span class="keyword">$2</span>)'
			),
			// Objects
			'object' => array
			(
				'/(object|\&amp;object)\(([\w]+)\)/i',
				'$1(<span class="object">$2</span>)'
			),
			// Function
			'function' => array
			(
				'/(^|] = )(array|string|int|float|bool|object)\(/i',
				'$1<span class="function">$2</span>('
			)
		);
		
		foreach( $regex as $x )
		{
			$c = preg_replace( $x[0], $x[1], $c );
		}
		
		$style = '
		/* outside div - it will float and match the screen */
		.dumpr {
		margin: 2px;
		padding: 2px;
		background-color: #fbfbfb;
		clear: both;
		}
		/* font size and family */
		.dumpr pre {
		color: #000000;
		font-size: 9pt;
		font-family: "Courier New",Courier,Monaco,monospace;
		margin: 0px;
		padding-top: 5px;
		padding-bottom: 7px;
		padding-left: 9px;
		padding-right: 9px;
		}
		/* inside div */
		.dumpr div {
		background-color: #fcfcfc;
		border: 1px solid #d9d9d9;
		clear: both;
		}
		/* syntax highlighting */
		.dumpr span.string {color: #c40000;}
		.dumpr span.number {color: #ff0000;}
		.dumpr span.keyword {color: #007200;}
		.dumpr span.function {color: #0000c4;}
		.dumpr span.object {color: #ac00ac;}
		';
		
		$style = preg_replace( "/ {2,}/", "", $style );
		$style = preg_replace( "/\t|\r\n|\r|\n/", "", $style );
		$style = preg_replace( "/\/\*.*?\*\//i", '', $style );
		$style = str_replace( '}', '} ', $style );
		$style = str_replace( ' {', '{', $style );
		$style = trim( $style );
		$c = trim( $c );
		
		$lines = explode( "\n", $c );
		
		$c = '';
		$i = 1;
		foreach( $lines as $line )
		{
			$c .= $i . ' ' . $line . "\n";
			$i++;
		}
		
		echo "\n<!-- dumpr -->\n";
		echo "<style type=\"text/css\">" . $style . "</style>\n";
		echo "<div class=\"dumpr\"><div><pre>\n$c\n</pre></div></div><div style=\"clear:both;\">&nbsp;</div>";
		echo "\n<!-- dumpr -->\n";
		
		flush();
		
		if( $die )
		{
			exit;
		}
	}
	
	/**
	 * Hook for exception handling
	 *
	 * @param Exception Exception object to be handled
	 */
	public static function handleException( $e )
	{
		$details['type']    = get_class( $e );
		$details['code']    = $e->getCode();
		$details['message'] = $e->getMessage();
		$details['line']    = $e->getLine();
		$details['file']    = $e->getFile();
		$details['trace']   = $e->getTrace();
		
		self::bluescreen( $details );
	}
	
	/**
	 * Hook for error handling
	 *
	 * @param integer Error level
	 * @param string Error message
	 * @param string Error file
	 * @param integer Line
	 * @param array Context (all defined vars)
	 */
	public static function handleError( $errno, $errstr, $errfile, $errline, $errcontext )
	{
		// Check if error needs to be handled
		if( ( $errno & self::LEVEL ) != $errno )
		{
			return;
		}
		
		// Error types
		$error_types = array
		(
			1 => 'ERROR',
			2 => 'WARNING',
			4 => 'PARSE',
			8 => 'NOTICE',
			16 => 'CORE_ERROR',
			32 => 'CORE_WARNING',
			64 => 'COMPILE_ERROR',
			128 => 'COMPILE_WARNING',
			256 => 'USER_ERROR',
			512 => 'USER_WARNING',
			1024 => 'USER_NOTICE',
			2047 => 'ALL',
			2048 => 'STRICT',
			4096 => 'RECOVERABLE_ERROR'
		);
		
		// Filling up details
		$backtrace          = debug_backtrace();
		$details['type']    = 'Error';
		$details['code']    = $error_types[$errno];
		$details['message'] = preg_replace("%\s\[<a href='function\.[\d\w-_]+'>function\.[\d\w-_]+</a>\]%", '', $errstr); // Removing PHP function links
		$details['line']    = $errline;
		$details['file']    = $errfile;
		$details['trace']   = array();
		
		// Building exception-like backtrace
		for( $i = 1; $i < count( $backtrace ); $i++ )
		{
			$details['trace'][$i - 1]['file']     = @$backtrace[$i]['file'];
			$details['trace'][$i - 1]['line']     = @$backtrace[$i]['line'];
			$details['trace'][$i - 1]['function'] = @$backtrace[$i]['function'];
			$details['trace'][$i - 1]['class']    = @$backtrace[$i]['class'];
			$details['trace'][$i - 1]['type']     = @$backtrace[$i]['type'];
			$details['trace'][$i - 1]['args']     = @$backtrace[$i]['args'];
		}
		
		self::bluescreen( $details );
	}
	
	/**
	 * A static function to nicely output exceptions
	 * Named after Harry's bluescreen method ;)
	 *
	 * @param array Details
	 * 
	 * @see self::displayException()
	 */
	public static function bluescreen( $input )
	{
		// saving previously buffered output for later
		$previous_output = ob_get_clean();
		
		$o = create_function( '$in', 'echo htmlspecialchars($in);' );
		
		$sub = create_function( '$f', '$loc="";if(isset($f["class"])){
	    $loc.=$f["class"].$f["type"];}
	    if(isset($f["function"])){$loc.=$f["function"];}
	    if(!empty($loc)){$loc=htmlspecialchars($loc);
	    $loc="<strong>$loc</strong>";}return $loc;');
		
		$parms = create_function('$f','$params=array();if(isset($f["function"])){
	    try{if(isset($f["class"])){
	    $r=new ReflectionMethod($f["class"]."::".$f["function"]);}
	    else{$r=new ReflectionFunction($f["function"]);}
	    return $r->getParameters();}catch(Exception $e){}}
	    return $params;');
		
		$src2lines = create_function('$file','$src=nl2br(highlight_file($file,TRUE));
	    return explode("<br />",$src);');
		
		$clean = create_function('$line','return trim(strip_tags($line));');
		
		$desc = $input['type'] . " making " . $_SERVER['REQUEST_METHOD'] . " request to " . $_SERVER['REQUEST_URI'];
		
		?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	  "http://www.w3.org/TR/html4/loose.dtd">
	<html lang="en">
	<head>
	  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	  <meta name="robots" content="NONE,NOARCHIVE" />
	  <title><?php $o($desc);?></title>
	  <style type="text/css">
	    html * { padding:0; margin:0; }
	    body * { padding:10px 20px; }
	    body * * { padding:0; }
	    body { font:small sans-serif; background: #70DBFF; }
	    body>div { border-bottom:1px solid #ddd; }
	    h1 { font-weight:normal; }
	    h2 { margin-bottom:.8em; }
	    h2 span { font-size:80%; color:#666; font-weight:normal; }
	    h2 a { text-decoration:none; }
	    h3 { margin:1em 0 .5em 0; }
	    h4 { margin:0.5em 0 .5em 0; font-weight: normal; font-style: italic; }
	    table { 
	        border:1px solid #ccc; border-collapse: collapse; background:white; }
	    tbody td, tbody th { vertical-align:top; padding:2px 3px; }
	    thead th { 
	        padding:1px 6px 1px 3px; background:#70FF94; text-align:left; 
	        font-weight:bold; font-size:11px; border:1px solid #ddd; }
	    tbody th { text-align:right; color:#666; padding-right:.5em; }
	    table.vars { margin:5px 0 2px 40px; }
	    table.vars td, table.req td { font-family:monospace; }
	    table td { background: #70FFDB; }
	    table td.code { width:95%;}
	    table td.code div { overflow:hidden; }
	    table.source th { color:#666; }
	    table.source td { 
	        font-family:monospace; white-space:pre; border-bottom:1px solid #eee; }
	    ul.traceback { list-style-type:none; }
	    ul.traceback li.frame { margin-bottom:1em; }
	    div.context { margin:5px 0 2px 40px; background-color:#70FFDB; }
	    div.context ol { 
	        padding-left:30px; margin:0 10px; list-style-position: inside; }
	    div.context ol li { 
	        font-family:monospace; white-space:pre; color:#666; cursor:pointer; }
	    div.context li.current-line { color:black; background-color:#70FF94; }
	    div.commands { margin-left: 40px; }
	    div.commands a { color:black; text-decoration:none; }
	    p.headers { background: #70FFDB; font-family:monospace; }
	    #summary { background: #00B8F5; }
	    #summary h2 { font-weight: normal; color: #666; }
	    #traceback { background:#eee; }
	    #request { background:#f6f6f6; }
	    #response { background:#eee; }
	    #summary table { border:none; background:#00B8F5; }
	    #summary td  { background:#00B8F5; }
	    .switch { text-decoration: none; }
	    .whitemsg { background:white; color:black;}
	  </style>
	  <script type="text/javascript">
	  //<!--
	    function getElementsByClassName(oElm, strTagName, strClassName){
	        // Written by Jonathan Snook, http://www.snook.ca/jon; 
	        // Add-ons by Robert Nyman, http://www.robertnyman.com
	        var arrElements = (strTagName == "*" && document.all)? document.all :
	        oElm.getElementsByTagName(strTagName);
	        var arrReturnElements = new Array();
	        strClassName = strClassName.replace(/\-/g, "\\-");
	        var oRegExp = new RegExp("(^|\\s)" + strClassName + "(\\s|$)");
	        var oElement;
	        for(var i=0; i<arrElements.length; i++){
	            oElement = arrElements[i];
	            if(oRegExp.test(oElement.className)){
	                arrReturnElements.push(oElement);
	            }
	        }
	        return (arrReturnElements)
	    }
	    function hideAll(elems) {
	      for (var e = 0; e < elems.length; e++) {
	        elems[e].style.display = 'none';
	      }
	    }
	    function toggle() {
	      for (var i = 0; i < arguments.length; i++) {
	        var e = document.getElementById(arguments[i]);
	        if (e) {
	          e.style.display = e.style.display == 'none' ? 'block' : 'none';
	        }
	      }
	      return false;
	    }
	    function varToggle(link, id, prefix) {
	      toggle(prefix + id);
	      var s = link.getElementsByTagName('span')[0];
	      var uarr = String.fromCharCode(0x25b6);
	      var darr = String.fromCharCode(0x25bc);
	      s.innerHTML = s.innerHTML == uarr ? darr : uarr;
	      return false;
	    }
	    function sectionToggle(span, section) {
	      toggle(section);
	      var span = document.getElementById(span);
	      var uarr = String.fromCharCode(0x25b6);
	      var darr = String.fromCharCode(0x25bc);
	      span.innerHTML = span.innerHTML == uarr ? darr : uarr;
	      return false;
	    }
	    
	    window.onload = function() {
	      hideAll(getElementsByClassName(document, 'table', 'vars'));
	      hideAll(getElementsByClassName(document, 'div', 'context'));
	      hideAll(getElementsByClassName(document, 'ul', 'traceback'));
	      hideAll(getElementsByClassName(document, 'div', 'section'));
	    }
	    //-->
	  </script>
	</head>
	<body>
	
	<div id="summary">
	  <h1><?php $o($desc);?></h1>
	  <h2><?php
	    if ( $input['code'] ) { echo $o($input['code']). ': '; }
	    ?> <?php $o($input['message']); ?></h2>
	  <table>
	    <tr>
	      <th>PHP</th>
	      <td><?php $o($input['file']); ?>, line <?php $o($input['line']); ?></td>
	    </tr>
	    <tr>
	      <th>URI</th>
	      <td><?php $o($_SERVER['REQUEST_METHOD'].' '.
	        $_SERVER['REQUEST_URI']);?></td>
	    </tr>
	  </table>
	</div>
	
	<div id="traceback">
	  <h2>Stacktrace
	    <a href='#' onclick="return sectionToggle('tb_switch','tb_list')">
	    <span id="tb_switch">&#x25b6;</span></a></h2>
	  <ul id="tb_list" class="traceback">
	    <?php $frames = $input['trace']; foreach ( $frames as $frame_id => $frame ) { ?>
	      <li class="frame">
	        <?php echo $sub($frame); ?>
	        [<?php $o($frame['file']); ?>, line <?php $o($frame['line']);?>]
	        <?php
	        if ( count($frame['args']) > 0 ) {
	          $params = $parms($frame);
	        ?>
	          <div class="commands">
	              <a href='#' onclick="return varToggle(this, '<?php
	              $o($frame_id); ?>','v')"><span>&#x25b6;</span> Args</a>
	          </div>
	          <table class="vars" id="v<?php $o($frame_id); ?>">
	            <thead>
	              <tr>
	                <th>Arg</th>
	                <th>Name</th>
	                <th>Value</th>
	              </tr>
	            </thead>
	            <tbody>
	                <?php
	                foreach ( $frame['args'] as $k => $v ) {
	                  $name = isset($params[$k]) ? '$'.$params[$k]->name : '?';
	                ?>
	                <tr>
	                  <td><?php $o($k); ?></td>
	                  <td><?php $o($name);?></td>
	                  <td class="code">
	                    <div><?php highlight_string(var_export($v,TRUE));?></div>
	                  </td>
	                </tr>
	                <?php
	                }
	                ?>
	            </tbody>
	          </table>
	        <?php } if ( is_readable($frame['file']) ) { ?>
	        <div class="commands">
	            <a href='#' onclick="return varToggle(this, '<?php
	            $o($frame_id); ?>','c')"><span>&#x25b6;</span> Src</a>
	        </div>
	        <div class="context" id="c<?php $o($frame_id); ?>">
	          <?php
	          $lines = $src2lines($frame['file']);
	          $start = $frame['line'] < 5 ?
	            0 : $frame['line'] -5; $end = $start + 10;
	          $out = '';
	          foreach ( $lines as $k => $line ) {
	            if ( $k > $end ) { break; }
	            $line = trim(strip_tags($line));
	            if ( $k < $start && isset($frames[$frame_id+1]["function"])
	              && preg_match(
	                '/function( )*'.preg_quote($frames[$frame_id+1]["function"]).'/',
	                  $line) ) {
	              $start = $k;
	            }
	            if ( $k >= $start ) {
	              if ( $k != $frame['line'] ) {
	                $out .= '<li><code>'.$clean($line).'</code></li>'."\n"; }
	              else {
	                $out .= '<li class="current-line"><code>'.
	                  $clean($line).'</code></li>'."\n"; }
	            }
	          }
	          echo "<ol start=\"$start\">\n".$out. "</ol>\n";
	          ?>
	        </div>
	        <?php } else { ?>
	        <div class="commands">No src available</div>
	        <?php } ?>
	      </li>
	    <?php } ?>
	  </ul>
	  
	</div>
	
	<div id="request">
	  <h2>Request
	    <a href='#' onclick="return sectionToggle('req_switch','req_list')">
	    <span id="req_switch">&#x25b6;</span></a></h2>
	  <div id="req_list" class="section">
	    <?php
	    if ( function_exists('apache_request_headers') ) {
	    ?>
	    <h3>Request <span>(raw)</span></h3>
	    <?php
	      $req_headers = apache_request_headers();
	        ?>
	      <h4>HEADERS</h4>
	      <?php
	      if ( count($req_headers) > 0 ) {
	      ?>
	        <p class="headers">
	        <?php
	        foreach ( $req_headers as $req_h_name => $req_h_val ) {
	          $o($req_h_name.': '.$req_h_val);
	          echo '<br>';
	        }
	        ?>
	        
	        </p>
	      <?php } else { ?>
	        <p>No headers.</p>
	      <?php } ?>
	      
	      <?php
	      $req_body = file_get_contents('php://input');
	      if ( strlen( $req_body ) > 0 ) {
	      ?>
	      <h4>Body</h4>
	      <p class="req" style="padding-bottom: 2em"><code>
	        <?php $o($req_body); ?>
	      </code></p>
	      <?php } ?>
	    <?php } ?>
	    <h3>Request <span>(parsed)</span></h3>
	    <?php
	    $superglobals = array('$_GET','$_POST','$_COOKIE','$_SERVER','$_ENV');
	    foreach ( $superglobals as $sglobal ) {
	      $sfn = create_function('','return '.$sglobal.';');
	    ?>
	    <h4><?php echo $sglobal; ?></h4>
	      <?php
	      if ( count($sfn()) > 0 ) {
	      ?>
	      <table class="req">
	        <thead>
	          <tr>
	            <th>Variable</th>
	            <th>Value</th>
	          </tr>
	        </thead>
	        <tbody>
	          <?php
	          foreach ( $sfn() as $k => $v ) {
	          ?>
	            <tr>
	              <td><?php $o($k); ?></td>
	              <td class="code">
	                <div><?php $o(var_export($v,TRUE)); ?></div>
	                </td>
	            </tr>
	          <?php } ?>
	        </tbody>
	      </table>
	      <?php } else { ?>
	      <p class="whitemsg">No data</p>
	      <?php } } ?>
	      
	  </div>
	</div>
	
	<?php if ( function_exists('headers_list') ) { ?>
	<div id="response">
	
	  <h2>Response
	    <a href='#' onclick="return sectionToggle('resp_switch','resp_list')">
	    <span id="resp_switch">&#x25b6;</span></a></h2>
	  
	  <div id="resp_list" class="section">
	
	    <h3>Headers</h3>
	    <?php
	    $resp_headers = headers_list();
	    if ( count($resp_headers) > 0 ) {
	    ?>
	    <p class="headers">
	      <?php
	      foreach ( $resp_headers as $resp_h ) {
	        $o($resp_h);
	        echo '<br>';
	      }
	      ?>
	    </p>
	    <?php } else { ?>
	      <p>No headers.</p>
	    <?php } ?>
	    <?php if(!empty($previous_output)) { ?>
	    	<p class="headers" style="padding-bottom: 1em; padding-top:1em; margin-top:1em;">
	    		<?php $o($previous_output, TRUE); ?>
	    	</p>
	    <?php } ?>
	</div>
	<?php } ?>
	
	</body>
	</html>
	<?php
		exit;
	}
	
}

?>

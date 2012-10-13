<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: JoungKyun Kim <http://www.oops.org>                          |
// +----------------------------------------------------------------------+
//
// $Id: print.php,v 1.2 2006-09-14 19:14:06 oops Exp $

class eSystem_sysColor
{
	function setColor ($color) {
		$color = $color ? strtolower ($color) : '';

		switch ($color) :
			case 'gray'    : return '[1;30m'; break;
			case 'red'     : return '[1;31m'; break;
			case 'green'   : return '[1;32m'; break;
			case 'yellow'  : return '[1;33m'; break;
			case 'blue'    : return '[1;34m'; break;
			case 'megenta' : return '[1;35m'; break;
			case 'cyan'    : return '[1;36m'; break;
			case 'white'   : return '[1;37m'; break;
			case 'end'     : return '[7;0m'; break;
		endswitch;

		return '[1;30m';
	}

	function putColor ($str, $color = '') {
		$color = eSystem_sysColor::setColor ($color);
		$end = eSystem_sysColor::setColor ('end');

		return $color . $str . $end;
	}
}

class eSystem_output
{
	function makeWhiteSpace ($no) {
		if ( ! is_numeric ($no) )
			return '';

		for ( $i = 0; $i<$no; $i++ )
			$_r .= ' ';

		return $_r;
	}

	function backSpace ($no) {
		if ( ! is_numeric ($no) )
			return '';

		for ( $i=0; $i<$no; $i++ ) :
			printf ("%c %c", '008', '008');
			#echo "12" ^ "9";
			#echo ' ';
			#echo "12" ^ "9";
		endfor;
	}

	function printe ($format, $msg = "") {
		/*
		if ( function_exists ('fprintf') ) :
			fprintf ('stderr', $format, $msg);
		else :
		*/
			if ( $msg ) :
				$functionName = is_array ($msg) ? 'vsprintf' : 'sprintf';
				$format = $functionName ($format, $msg);
			endif;

			error_log ($format);
		//endif;
	}
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
?>

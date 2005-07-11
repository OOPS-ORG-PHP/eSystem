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
// $Id: system.php,v 1.1.1.1 2005-07-11 05:24:11 oops Exp $

class _command
{
	/*
	 * define origin proto function
	 * start function name __
	 */
	function __system ($_cmd, &$__var, $_out = 0) {
		$__var = -1;
		$__cmd = $_cmd . ' 2> /dev/null; echo "RET_VAL:$?"';

		$p = popen ($__cmd, "r");

		while ( ! feof ($p) ) :
			$_r = fread ($p, 1024);

			if ( preg_match ("/RET_VAL:[^0-9]*([0-9]+)$/", $_r, $_match) ) :
				$__var = $_match[1];
				$_r = preg_replace ("/RET_VAL:[^0-9]*[0-9]+$/", '', $_r);
			endif;

			if ( $_out ) :
				$_rr .= $_r;
			else :
				$_last = $_r;
				echo $_r;
				flush ();
			endif;
		endwhile;
		pclose ($p);

		if ( $_out ) return $_rr;
		return $_last;
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

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
// | Author: JoungKyun.Kim <http://oops.org>                              |
// +----------------------------------------------------------------------+
//
// $Id: system.php,v 1.9 2009-08-06 18:50:46 oops Exp $

class eSystem_system
{
	// {{{ properties
	var $tmpdir = '/tmp';
	var $tmpname = 'eSystem_system_';
	var $_stdout;
	var $_stderr;
	var $_retint = 0;
	// }}}

	// {{{ function _system ($_cmd, $_out = 0)
	/*
	 * define origin proto function
	 * start function name _
	 */
	function _system ($_cmd, $_out = 0) {
		$_err = tempnam ($this->tmpdir, $this->tmpname);
		$_cmd = $_cmd . ' 2> ' . $_err . '; echo "RET_VAL:$?"';

		$pd = popen ($_cmd, "r");

		while ( ! feof ($pd) ) :
			$_r = rtrim (fgets ($pd, 1024));

			if ( preg_match ("/RET_VAL:([0-9]+)$/", $_r, $_match) ) :
				$this->_retint = $_match[1];

				if ( ! preg_match ("/^RET_VAL/", $_r) ) :
					$_r = preg_replace ('/RET_VAL:.*/', '', $_r);
					$this->_stdout[] = $_r;

					if ( $_out ) :
						echo $_r . "\n";
						flush ();
					endif;
				endif;
				break;
			else :
				$this->_stdout[] = $_r;

				if ( $_out ) :
					echo $_r . "\n";
					flush ();
				endif;
			endif;
		endwhile;
		pclose ($pd);

		if ( filesize ($_err) > 0 ) :
			$this->_stderr = rtrim (file_get_contents ($_err));
		endif;
		unlink ($_err);
	}
	// }}}
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
